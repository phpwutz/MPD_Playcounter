<?php
require_once("mpdEventListener.class.php");

$srv = "192.168.1.107";
$port = 6600;
$pwd = null;
$debug = false;
$mpd = new mpd($srv, $port, $pwd, $debug);

echo "started...";

function playListener($songFile){
	//echo $songFile . " was played\n";
	$ret = array();
	exec('python sticker.py get "'.$songFile.'" playcount', $ret);
	//var_dump($ret);
	if(sizeof($ret) < 1){
		//echo "setting to 1";
		exec('python sticker.py set "'.$songFile.'" playcount 1', $ret);
	}else{
		$tmp = explode("=", $ret[0], 2);
		$currentCount = (int) $tmp[1];
		//var_dump($currentCount);
		$newCount = $currentCount + 1;
		exec('python sticker.py set "'.$songFile.'" playcount '.$newCount, $ret);
		var_dump($ret);
	}
	exec('python sticker.py set "'.$songFile.'" lastPlayed '.time(), $ret);
};

$listener = new mpdEventListener($mpd);
$listener->bind(MPDEVENTLISTENER_ONSONGCHANGE, "playListener");
$listener->startListening();