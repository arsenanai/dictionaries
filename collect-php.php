<?php

echo 'collecting messages from .php sources'.PHP_EOL;

$dir = new DirectoryIterator(dirname(__FILE__)."/app");
$messages = array();

function myArrayPush(&$array, $item){
	$found = false;
	for($i=0;$i<sizeof($array);$i++)
		if($array[$i]===$item){
			$found = true;
			break;
		}
	if($found===false)
		array_push($array, $item);
}

foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $fileinfo->getFilename();
        $fh = fopen(dirname(__FILE__)."/app/" . $fileinfo->getFilename(),'r');
		while ($line = fgets($fh)) {
		  //{{$t('Group')}}
		  //this.$i18n.t('Code')
			//'#\[(.*?)\]#'
			$pattern = '#\{\{\$t\(\'(.*?)\'\)\}\}#';
			$matches = preg_match_all($pattern, $line, $match);
			for($i=0;$i<$matches;$i++)
				myArrayPush($messages, $match[1][$i]);

			$pattern = '#\$i18n\.t\(\'(.*?)\'\)#';
			$matches = preg_match_all($pattern, $line, $match);
			for($i=0;$i<$matches;$i++)
				myArrayPush($messages, $match[1][$i]);
		}
		fclose($fh);
    }
}
sort($messages);
//echo sizeof($messages).PHP_EOL;
$fp = fopen(__DIR__ . '/messages_js.txt', 'w');
for($i=0;$i<sizeof($messages);$i++){
	if(!empty($messages[$i])){
		$line = $messages[$i];
		fwrite($fp, "'".$line."':'',".PHP_EOL);
	}
}
fclose($fp);