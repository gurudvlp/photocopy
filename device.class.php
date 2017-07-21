<?php

class Device
{
	public $ID = 0;
	public $vID = "";
	public $pID = "";
	public $Product = "";
	public $Vendor = "";
	
	function __construct($deviceline = "")
	{
		if($deviceline == null || $deviceline == "") { return; }
		
		$this->Parse($deviceline);
	}
	
	public function Parse($deviceline)
	{
		if(strlen($deviceline) < 8) { return; }
		if(substr($deviceline, 0, 7) != "Device ") { return; }
		
		if(strpos($deviceline, " is a ") === false) { return; }
		
		$presuf = explode(" is a ", $deviceline);
		
		$prep = explode(" ", $presuf[0]);
		$this->ID = $prep[1];
		
		$parts = explode("=", $prep[2]);
		$this->vID = $parts[1];
		
		$parts = explode("=", $prep[4]);
		$this->pID = $parts[1];
	}
}
