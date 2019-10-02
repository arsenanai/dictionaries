<?php

echo 'collecting messages from .vue sources'.PHP_EOL;
$baseDir = __DIR__.DIRECTORY_SEPARATOR."resources".DIRECTORY_SEPARATOR."js";

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
function getDirContents($dir, &$results = array()){
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path) && in_array(pathinfo($path, PATHINFO_EXTENSION), array('vue','js'))) {
            $results[] = $path;
        } else if($value != "." && $value != ".." && is_dir($path)) {
            getDirContents($path, $results);
        }
    }
    return $results;
}

$dir = getDirContents($baseDir);

foreach ($dir as $fileinfo) {
    if ($fileinfo!='.') {
        //echo $fileinfo.PHP_EOL;
        $fh = fopen($fileinfo,'r');
		while ($line = fgets($fh)) {
			$pattern = '#\{\{\$t\(\'(.*?)\'\)\}\}#';
			$matches = preg_match_all($pattern, $line, $match);
			for($i=0;$i<$matches;$i++)
				myArrayPush($messages, $match[1][$i]);

			$pattern = '#\{\{\$t\(\"(.*?)\"\)\}\}#';
			$matches = preg_match_all($pattern, $line, $match);
			for($i=0;$i<$matches;$i++)
				myArrayPush($messages, $match[1][$i]);

			$pattern = '#\$i18n\.t\(\'(.*?)\'\)#';
			$matches = preg_match_all($pattern, $line, $match);
			for($i=0;$i<$matches;$i++)
				myArrayPush($messages, $match[1][$i]);

			$pattern = '#\$i18n\.t\(\"(.*?)\"\)#';
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
	for($j=0;$j<sizeof($translations);$j++){
		$messages[$i] = str_replace("\'","'",$messages[$i]);
		if(strcmp($translations[$j],$messages[$i])===0){
			$found=true;
			break;
		}
	}
	if(!empty($messages[$i]) && !$found ){
		$line = $messages[$i];
		fwrite($fp, "'".$line."':'',".PHP_EOL);
	}
}
fclose($fp);