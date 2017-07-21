<?php
////////////////////////////////////////////////////////////////////////////////
//
//	photocopy
//
//	Copyright 2017 Brian Murphy
//
//	This program attempts to copy any new photos on an android device to the
//	appropriate location.
//
////////////////////////////////////////////////////////////////////////////////

$LocalPhotoDir = "/home/guru/Pictures";
$MountDir = "/home/guru/mnt/android";
$AndroidPhotoDir = "Internal storage/DCIM/Camera";

$Options = ParseOptions($argv);
if(!array_key_exists("maxdirs", $Options)
|| (array_key_exists("maxdirs", $Options) && !is_numeric($Options["maxdirs"])))
{
	$Options["maxdirs"] = 5;
}

require("device.class.php");
require("androidmounter.class.php");
require("copier.class.php");

$Photos = new Copier($LocalPhotoDir, $MountDir, $AndroidPhotoDir, $Options["maxdirs"]);
$Photos->Run();


function ParseOptions($args)
{
	if(count($args) < 2) { return array(); }
	$toret = array();
	
	foreach($args as $earg)
	{
		if(strpos($earg, "=") !== false)
		{
			$kv = explode("=", $earg);
			$toret[strtolower($kv[0])] = $kv[1];
		}
	}
	
	return $toret;
}
?>
