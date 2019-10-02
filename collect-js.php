<?php

echo 'collecting messages from .vue sources'.PHP_EOL;

$dir = new DirectoryIterator(dirname(__FILE__)."/resources/js/views");
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
        $fh = fopen(dirname(__FILE__)."/resources/js/views/" . $fileinfo->getFilename(),'r');
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

$fh = fopen(__DIR__.'/resources/js/i18n.js','r');
$translations = array();
$started = false;
while ($line = fgets($fh)) {
	if(strpos($line, 'translation ends') !== false){
		$started = false;
	}
	if($started==true){
		$key = explode(":",$line)[0];
		$k = substr($key,1,strlen($key)-2);
		myArrayPush($translations, $k);
	}
	if(strpos($line, 'translation starts')!==false){
		$started = true;
	}
}
fclose($fh);

sort($translations);
//echo sizeof($messages).PHP_EOL;
$fp = fopen(__DIR__ . '/messages_js.txt', 'w');
for($i=0;$i<sizeof($messages);$i++){
	$found = false;
	for($j=0;$j<sizeof($translations);$j++)
		if($translations[$j]===$messages[$i]){
			$found=true;
			break;
		}
	if(!empty($messages[$i]) && !$found ){
		$line = $messages[$i];
		fwrite($fp, "'".$line."':'',".PHP_EOL);
	}
}
fclose($fp);