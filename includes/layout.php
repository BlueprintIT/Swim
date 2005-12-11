<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Layout
{
	var $id;
	var $name;
	var $prefs;
	
	function Layout($id,$name)
	{
		global $_PREFS;
		
		$this->id=$id;
		$this->name=$name;
		$this->prefs=$_PREFS;
	}
	
	function getDir()
	{
		return $this->prefs->getPref('storage.layouts').'/'.$this->id;
	}
}

$_LAYOUTS = array();

function getLayout($id)
{
	global $_LAYOUTS,$_PREFS;
	
	if (!isset($_LAYOUT[$id]))
	{
		getAllLayouts();
	}
	return $_LAYOUTS[$id];
}

function getAllLayouts()
{
	global $_LAYOUTS,$_PREFS;
	$log=LoggerManager::getLogger('swim.layout');
	
	$file=$_PREFS->getPref('layouts.conf');
	if (is_readable($file))
	{
		$layouts=array();
		$prefs = new Preferences();
		$conf=fopen($file,'r');
		$prefs->loadPreferences($conf);
		fclose($conf);
		foreach ($prefs->preferences as $id => $name)
		{
			if (!isset($_LAYOUTS[$id]))
			{
				$_LAYOUTS[$id]=new Layout($id,$name);
			}
			$layouts[$id]=$_LAYOUTS[$id];
		}
		return $layouts;
	}
	else
	{
		$log->warn('Layout file was unreadable: '.$file);
		return array();
	}
}

?>
