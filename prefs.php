<?

/*
 * Swim
 *
 * The preferences engine
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */
	
class Preferences
{
	var $preferences = array();
	var $parent;
	var $overrides;
	
	function Preferences()
	{
		$this->overrides=&$this;
	}
	
	function setParent(&$parprefs)
	{
		$this->parent=&$parprefs;
	}
	
	function setOverride(&$overprefs)
	{
		$this->overrides=&$overprefs;
	}
	
	function getPrefBranch($branch)
	{
		if (!($branch[strlen($branch)-1]=='.'))
		{
			$branch.='.';
		}
		if (isset($this->parent))
		{
			$result=$this->parent->getPrefBranch($branch);
		}
		else
		{
			$result=array();
		}
		foreach ($this->preferences as $name=>$value)
		{
			if (substr($name,0,strlen($branch))==$branch)
			{
				$result[substr($name,strlen($branch))]=$this->evaluatePref($value);
			}
		}
		return $result;
	}
	
	// Loads preferences. With no arguments it loads all preferences from their default
	// locations. Otherwise specify the type (numerical) and the file to load from.
	function loadPreferences($file,$branch = '',$merge = false)
	{
    if (is_readable($file))
    {
      $source=fopen($file,'r');
    	if (!$merge)
    	{
	      $this->preferences=array();
	    }
	    if (strlen($branch)>0)
	    {
	    	$branch.='.';
	    }
      while (!feof($source))
      {
        $line=fgets($source);
        if (preg_match('/^([^=#$[\]]+)=(.*?)\s*$/',$line,$matches))
        {
        	if (substr($matches[1],0,strlen($branch))==$branch)
        	{
	          $value=$matches[2];
	          if ((strcasecmp($value,'true')==0)||(strcasecmp($value,'yes')==0))
	          {
	            $value=true;
	          }
	          else if ((strcasecmp($value,'false')==0)||(strcasecmp($value,'no')==0))
	          {
	            $value=false;
	          }
	          $this->preferences[$matches[1]]=$value;
	        }
        }
	    }
      fclose($source);
    }
    else
    {
    	trigger_error('Unable to read file '.$file);
    }
	}
	
	function savePreferences($file)
	{
	}
	
	// Clears a set of preferences
	function clearPreferences()
	{
	  $this->preferences=array();
	}
	
	// Evaluates a preference value, resolving references
	function evaluatePref($text)
	{
		$count=preg_match_all('/\$\[([^=#$[\]]+?)\]/',$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for($p=$count-1; $p>=0; $p--)
		{
			$pref=$matches[1][$p][0];
			$offset=$matches[0][$p][1];
			$length=strlen($matches[0][$p][0]);
			$replacement=$this->overrides->getPref($pref,$pref);
			$text=substr_replace($text,$replacement,$offset,$length);
		}
		return $text;
	}
	
	// Retrieves a preference. Returns a blank string for undefined preferences.
	function getPref($pref,$default = '')
	{
    if (isset($this->preferences[$pref]))
    {
      return $this->evaluatePref($this->preferences[$pref]);
    }
    else if (isset($this->parent))
    {
    	return $this->parent->getPref($pref,$default);
    }
	  return $default;
	}
	
	// Checks if a preference is defined
	function isPrefSet($pref)
	{
	  if (isset($this->preferences[$pref]))
	  {
	    return true;
	  }
	  else if (isset($this->parent))
	  {
	  	return $this->parent->isPrefSet($pref);
	  }
	  return false;
	}
}

function init()
{
	global $_PREFS;
	
	$default = new Preferences();
	$default->loadPreferences('default.conf');
	
	$siteprefs = new Preferences();
	$siteprefs->loadPreferences('site.conf');
	$siteprefs->setParent($default);
	
	$default->setOverride($siteprefs);

	$_PREFS = new Preferences();
	$_PREFS->setParent($siteprefs);
}

init();

?>