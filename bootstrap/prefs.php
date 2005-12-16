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
	var $delegate;
	
	function Preferences($clone=null)
	{
    if ($clone!==null)
    {
      $this->preferences=$clone->preferences;
      $this->parent=$clone->parent;
      $this->delegate=$clone->delegate;
    }
	}
	
	function setParent($parprefs)
	{
		$this->parent=$parprefs;
	}
  
  function getParent()
  {
    return $this->parent;
  }
	
	function setDelegate($overprefs)
	{
		$this->delegate=$overprefs;
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
	
  function addPreferences($prefs, $overwrite=true)
  {
    foreach ($prefs->preferences as $name => $key)
    {
      if ($overwrite || (!isset($this->preferences[$name])))
      {
        $this->preferences[$name]=$key;
      }
    }
  }
  
  function loadFromDOM($element,$branch = '',$merge = false)
  {
    if (!$merge)
    {
      $this->preferences=array();
    }
    if (strlen($branch)>0)
    {
      $branch.='.';
    }
    $el=$element->firstChild;
    while ($el!==null)
    {
      if (($el->nodeType==XML_ELEMENT_NODE)&&($el->tagName=='preference'))
      {
        $name=$el->getAttribute('name');
        $value=$el->getAttribute('value');
        if (substr($name,0,strlen($branch))==$branch)
        {
          if ((strcasecmp($value,'true')==0)||(strcasecmp($value,'yes')==0))
          {
            $value=true;
          }
          else if ((strcasecmp($value,'false')==0)||(strcasecmp($value,'no')==0))
          {
            $value=false;
          }
          $this->preferences[$name]=$value;
        }
      }
      $el=$el->nextSibling;
    }
  }
  
	function loadPreferences($source,$branch = '',$merge = false)
	{
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
      $matches=array();
      if (preg_match('/^([^=#$[\]\/]+)=(.*?)\s*$/',$line,$matches))
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
	}
	
	function savePreferences($source)
	{
    foreach ($this->preferences as $key => $value)
    {
    	fwrite($source,$key.'='.$value."\n");
    }
	}
	
	function unsetPref($name)
	{
		unset($this->preferences[$name]);
	}
	
	function setPref($name,$value)
	{
		$this->preferences[$name]=$value;
	}
	
	// Clears a set of preferences
	function clearPreferences()
	{
	  $this->preferences=array();
	}
	
	function getDelegatedPref($pref,$default)
	{
		if (isset($this->delegate))
		{
			return $this->delegate->getPref($pref,$default);
		}
		else
		{
			return $this->getPref($pref,$default);
		}
	}
	
	// Evaluates a preference value, resolving references
	function evaluatePref($text)
	{
		$matches=array();
		$count=preg_match_all('/\$\[([^=#$[\]]+?)\]/',$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for($p=$count-1; $p>=0; $p--)
		{
			$pref=$matches[1][$p][0];
			$offset=$matches[0][$p][1];
			$length=strlen($matches[0][$p][0]);
			$replacement=$this->getDelegatedPref($pref,$pref);
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
    else
    {
		  return $default;
    }
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
  
  function isPrefInherited($pref)
  {
    if (isset($this->preferences[$pref]))
    {
      return false;
    }
    if (isset($this->parent))
    {
      return $this->parent->isPrefSet($pref);
    }
    return false;
  }
}

function saveSitePreferences()
{
	global $_PREFS;
	
	$confdir=$host->getPref('storage.config');
	LockManager::lockResourceWrite($confdir);
	$file=fopen($_PREFS->getPref('storage.config').'/site.conf','w');
	$_PREFS->savePreferences($file);
	fclose($file);
	LockManager::unlockResource($confdir);
}

$default = new Preferences();
$file=fopen($bootstrap.'/default.conf','r');
$default->loadPreferences($file);
fclose($file);
$default->setPref('storage.basedir',dirname($bootstrap));

$host = new Preferences();
if (is_readable($bootstrap.'/host.conf'))
{
	$file=fopen($bootstrap.'/host.conf','r');
	$host->loadPreferences($file);
	fclose($file);
}
$host->setParent($default);
$default->setDelegate($host);

$_PREFS=$host;
LoggerManager::loadPreferences();
require_once $bootstrap.'/baseincludes.php';

$confdir=$host->getPref('storage.config');
LockManager::lockResourceRead($confdir);
unset($_PREFS);
$_PREFS = new Preferences();
if (is_readable($host->getPref('storage.config').'/site.conf'))
{
	$file=fopen($host->getPref('storage.config').'/site.conf','r');
	$_PREFS->loadPreferences($file);
	fclose($file);
}
$_PREFS->setParent($host);
LockManager::unlockResource($confdir);

$default->setDelegate($_PREFS);

unset($confdir);
unset($default);
unset($host);
unset($file);
unset($lock);

?>