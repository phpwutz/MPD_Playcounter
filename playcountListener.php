<?php
require_once("mpdEventListener.class.php");

$srv = "192.168.1.107";
$port = 6600;
$pwd = null;
$debug = false;
$mpd = new mpd($srv, $port, $pwd, $debug);

function playListener($songFile){
	echo $songFile . " was played\n";
};

$listener = new mpdEventListener($mpd);
$listener->bind(MPDEVENTLISTENER_ONSONGCHANGE, "playListener");
$listener->startListening();