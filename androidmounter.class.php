<?php

class AndroidMounter
{
	public $MountPoint = "";
	public $isMounted = false;
	public $DeviceList = array();
	
	function __construct($mountpoint = "")
	{
		$this->MountPoint = $mountpoint;
		$this->isMounted = false;
		$this->DeviceList = array();
	}
	
	public function Mount()
	{
		if($this->MountPoint == null || $this->MountPoint == "")
		{
			echo "Mount point was not defined.\n";
			$this->isMounted = false;
			return false;
		}
		
		if(!$this->isDevicePresent())
		{
			echo "No android devices detected.\n";
			return false;
		}
		
		$cmd = $this->MountCommand();
		`$cmd`;
		
		$this->isMounted = true;
		
		return true;
	}
	
	public function Unmount($attempt = 0)
	{
		$cmd = "fusermount -u ".$this->MountPoint." 2>&1";
		$res = `$cmd`;
		
		if(strpos($res, "failed to unmount") === false) 
		{ 
			echo "Device unmounted.\n";
			return; 
		}
		
		if($attempt >= 3)
		{
			echo "Could not unmount device.\n";
			return;
		}
		
		echo "Failed to unmount, retrying...";
		sleep(1);
		echo ".";
		sleep(1);
		echo ".";
		sleep(1);
		echo ".\n";
		
		$this->Unmount($attempt + 1);
	}
	
	private function ScanDevices()
	{
		$this->Devices = array();
		
		$cmd = "jmtpfs -l 2>&1";
		$res = `$cmd`;
		
		$lines = explode("\n", $res);
		
		foreach($lines as $eline)
		{
			
			if(strlen($eline) > 7
			&& substr($eline, 0, 7) == "Device ")
			{
				$this->DeviceList[] = new Device($eline);
			}
		}
	}
	
	private function isDevicePresent()
	{
		if(count($this->DeviceList) == 0) { $this->ScanDevices(); }
		
		if(count($this->DeviceList) == 0) { return false; } else { return true; }
	}
	
	private function MountCommand()
	{
		return "jmtpfs ".$this->MountPoint;
	}
}
