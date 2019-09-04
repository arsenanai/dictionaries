<?php

echo 'collecting messages from .js sources'.PHP_EOL;

$dir = new DirectoryIterator(dirname(__FILE__)."/resources/js/views");
$messages = array();

function findPositions($html,$needle){
	$lastPos = 0;
	$positions = array();

	while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
	    $positions[] = $lastPos;
	    $lastPos = $lastPos + strlen($needle);
	}

	return $positions;
}

function getMessagesFromLine($line,$prefix,$postfix){
	if (strpos($line, $begin) !== false) {
		$positions = findPositions($line, $prefix);
		foreach ($positions as $value) {
		    echo $value ."<br />";
		}
	}
}

foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $fileinfo->getFilename();
        $fh = fopen(dirname(__FILE__)."/resources/js/views/" . $fileinfo->getFilename(),'r');
		//reading input file
		while ($line = fgets($fh)) {
		  //{{$t('Group')}}
		  //this.$i18n.t('Code')
		  if (strpos($line, '{{$t') !== false) {

		  }
		  if (strpos($line, '$i18n.t(') !== false) {

		  }
		}
		fclose($fh);
    }
}