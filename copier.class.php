<?php

class Copier
{
	public $PhotoDir = "";
	public $MountDir = "";
	public $AndroidPhotoDir = "";
	public $LocalFiles = array();
	public $DeviceFiles = array();
	public $NewFiles = array();
	public $Mounter = null;
	public $MaxDirs = 5;
	
	function __construct($photodir, $mountdir, $androiddir, $maxdirs = 5)
	{
		if($photodir == "" || $photodir == null)
		{
			echo "No photo directory specified.\n";
			exit(0);
		}
		
		if($mountdir == "" || $mountdir == null)
		{
			echo "No mount directory specified.\n";
			exit(0);
		}
		
		if($androiddir == "" || $androiddir == null)
		{
			echo "No Android directory specified.\n";
			exit(0);
		}
		
		if(!file_exists($photodir))
		{
			echo "Photo directory not found!\n".$photodir."\n";
			exit(0);
		}
		else { $this->PhotoDir = $photodir; }
		
		if(!file_exists($mountdir))
		{
			echo "Mount directory not found!\n".$mountdir."\n";
			exit(0);
		}
		else { $this->MountDir = $mountdir; }
		
		$mntcontents = scandir($this->MountDir);
		if(count($mntcontents) > 2)
		{
			echo "Mount directory is not empty!\n".$this->MountDir."\n";
			exit(0);
		}
		
		$this->MaxDirs = $maxdirs;
		
		$this->AndroidPhotoDir = $androiddir;
		$this->Mounter = null;
		
	}
	
	public function Run()
	{
		$this->ScanLocalPhotos();
		
		$this->Mounter = new AndroidMounter($this->MountDir);
		
		if(!$this->Mounter->Mount())
		{
			echo "Failed to mount device.\n";
			exit(0);
		}
		
		$this->ScanDeviceFiles();
		$this->GetNewFiles();
		
		echo "Found ".count($this->NewFiles)." files to copy.\n";
		$this->Copy();
		
		$this->Mounter->Unmount();

	}
	
	private function ScanLocalPhotos()
	{
		$files = scandir($this->PhotoDir);
		
		foreach($files as $efile)
		{
			if($efile != "." && $efile != ".." && is_dir($this->PhotoDir."/".$efile))
			{
				if(strlen($efile) >= 8)
				{
					//	Long enough to be xx-xx-xx
					if(substr($efile, 2, 1) == "-"
					&& substr($efile, 5, 1) == "-"
					&& is_numeric(substr($efile, 0, 2))
					&& is_numeric(substr($efile, 3, 2))
					&& is_numeric(substr($efile, 6, 2)))
					{
						//	Seems to match date format
						$tfile = array(
							"year" => substr($efile, 0, 2) + 2000,
							"month" => substr($efile, 3, 2),
							"day" => substr($efile, 6, 2),
							"name" => $efile
						);
						
						$this->LocalFiles[] = $tfile;
					}
				}
			}
		}
	}
	
	private function ScanDeviceFiles()
	{
		if(!$this->Mounter->isMounted) { return; }
		
		$files = scandir($this->MountDir."/".$this->AndroidPhotoDir);
		
		foreach($files as $efile)
		{
			if($efile != "." && $efile != "..")
			{
				if(strlen($efile) > 13)
				{
					//	Long enough for IMG_YYYYMMDD_
					if(substr($efile, 0, 4) == "IMG_"
					&& substr($efile, 12, 1) == "_"
					&& is_numeric(substr($efile, 4, 8)))
					{
						//	Matches image/date format
						$tfile = array(
							"year" => substr($efile, 4, 4),
							"month" => substr($efile, 8, 2),
							"day" => substr($efile, 10, 2),
							"name" => $efile
						);
						
						$this->DeviceFiles[] = $tfile;
					}
				}
			}
		}
	}
	
	private function GetNewFiles()
	{
		foreach($this->DeviceFiles as $edf)
		{
			if(!$this->doesDateExist($edf["year"], $edf["month"], $edf["day"]))
			{
				$this->NewFiles[] = $edf;
			}
		}
	}
	
	private function doesDateExist($year, $month, $day)
	{
		if($year < 2000) { $year += 2000; }
		
		foreach($this->LocalFiles as $elf)
		{
			if($elf["year"] == $year
			&& $elf["month"] == $month
			&& $elf["day"] == $day)
			{
				return true;
			}
		}
		
		return false;
	}
	
	private function Copy()
	{
		//	First create new directories
		//	Then copy the files over
		$loops = 0;
		$floops = 1;
		foreach($this->NewFiles as $enf)
		{
			
			$nd = ($enf["year"] - 2000)."-".$enf["month"]."-".$enf["day"];
			$nd = $this->PhotoDir."/".$nd;
			
			if(!file_exists($nd))
			{
				echo "(".($loops + 1)."/".$this->MaxDirs.") Create dir: ".$nd."\n";
				mkdir($nd);
				$loops++;
			}
			
			$sourcename = $this->MountDir."/".$this->AndroidPhotoDir."/".$enf["name"];
			$destname = $nd."/".$enf["name"];
			echo "\t(".$floops."/".count($this->NewFiles).") Copying ".$enf["name"]."...";
			/*echo "\t".$sourcename."\n";
			echo "\t\t".$destname."\n";*/
			if(!copy($sourcename, $destname))
			{
				echo "failed\n";
			}
			else { echo "ok\n"; }
			
			$floops++;
			if($this->MaxDirs > 0
			&& $loops >= $this->MaxDirs) { return; }
		}
	}
}
