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
	// Defines a name for each preference type. Never really used except for the count.
	var $preftypes = array("block","page","template","site","defaults");
	// Location to load preferences from. NULL means that the preference type is not loaded by default
	var $preflocations = array(NULL,NULL,NULL,"site.conf","default.conf");
	// Which preferences can override which
	var $prefoverrides = array(0,1,2,3,3);
	var $preferences = array();
	
	function Preferences()
	{
    foreach ($this->preflocations as $type => $location)
    {
      if (!is_null($location))
      {
        $this->loadPreferences($type,$location);
      }
    }
	}
	
	// Returns the numeric form of a type.
	function getPrefTypeId($type)
	{
		return array_search($type,$this->preftypes);
	}
	
	// Loads preferences. With no arguments it loads all preferences from their default
	// locations. Otherwise specify the type (numerical) and the file to load from.
	function loadPreferences($type,$file)
	{
    if (is_readable($file))
    {
      $this->preferences[$type]=array();
      $source=fopen($file,"r");
      while (!feof($source))
      {
        $line=fgets($source);
        if (preg_match("/^([^=#$[\]]+)=(.*?)\s*$/",$line,$matches))
        {
          $value=$matches[2];
          if ((strcasecmp($value,"true")==0)||(strcasecmp($value,"yes")==0))
          {
            $value=true;
          }
          else if ((strcasecmp($value,"false")==0)||(strcasecmp($value,"no")==0))
          {
            $value=false;
          }
          $this->preferences[$type][$matches[1]]=$value;
        }
      }
      fclose($source);
    }
    else
    {
    	trigger_error("Unable to read file ".$file);
    }
	}
	
	// Evaluates a preference value, resolving references
	function evaluatePref($text,$type)
	{
		$count=preg_match_all('/\$\[([^=#$[\]]+)\]/',$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for($p=$count-1; $p>=0; $p--)
		{
			$pref=$matches[1][$p][0];
			$offset=$matches[0][$p][1];
			$length=strlen($matches[0][$p][0]);
			$replacement=$this->getPref($pref,$this->prefoverrides[$type]);
			$text=substr_replace($text,$replacement,$offset,$length);
		}
		return $text;
	}
	
	// Retrieves a preference. Returns a blank string for undefined preferences.
	function getPref($pref,$type = 0)
	{
	  for ($i=$type; $i<count($this->preftypes); $i++)
	  {
	    if (isset($this->preferences[$i][$pref]))
	    {
	      return $this->evaluatePref($this->preferences[$i][$pref],$type);
	    }
	  }
	  return "";
	}
	
	// Checks if a preference is defined
	function isPrefSet($pref)
	{
	  for ($i=0; $i<count($this->preftypes); $i++)
	  {
	    if (isset($this->preferences[$i][$pref]))
	    {
	      return true;
	    }
	  }
	  return false;
	}
	
	// Returns a branch of the preferences in associative array form
	function getPrefBranch($branch)
	{
		$result=array();
		if (strlen($branch)>0)
		{
			$branch.=".";
		}
		for ($i=0; $i<count($this->preftypes); $i++)
		{
			foreach ($this->preferences[$i] as $name => $value)
			{
				if (substr($name,0,strlen($branch))==$branch)
				{
					$remains=substr($name,strlen($branch));
					if (strlen($remains)>0)
					{
						$parts=explode(".",$remains);
						$pos=0;
						$current=&$result;
						while ($pos<count($parts)-1)
						{
							if (!isset($current[$parts[$pos]]))
							{
								$current[$parts[$pos]]=array();
							}
							$current=&$current[$parts[$pos]];
							$pos++;
						}
						if (!isset($current[$parts[$pos]]))
						{
							$current[$parts[$pos]]=$this->evaluatePref($value,$i);
						}
					}
				}
			}
		}
		return $result;
	}
}

$_PREFERENCES = new Preferences();

function loadPreferences($type,$file)
{
	global $_PREFERENCES;
	if (!is_numeric($type))
	{
		$type=$_PREFERENCES->getPrefTypeId($type);
	}
	$_PREFERENCES->loadPreferences($type,$file);
}

// Retrieves a preference. Returns a blank string for undefined preferences.
function getPref($pref)
{
	global $_PREFERENCES;
	return $_PREFERENCES->getPref($pref);
}

// Checks if a preference is defined
function isPrefSet($pref)
{
	global $_PREFERENCES;
	return $_PREFERENCES->isPrefSet($pref);
}

function getPrefBranch($branch="")
{
	global $_PREFERENCES;
	return $_PREFERENCES->getPrefBranch($branch);
}

?>