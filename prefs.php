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

// Defines a name for each preference type. Never really used except for the count.
$preftypes = array("page","site","defaults");
// Location to load preferences from. NULL means that the preference type is not loaded by default
$preflocations = array(NULL,"site.conf","defaults.conf");
// Which preferences can override which
$prefoverrides = array(0,1,1);

// Loads preferences. With no arguments it loads all preferences from their default
// locations. Otherwise specify the type (numerical) and the file to load from.
function loadPreferences($type=-1,$file=NULL)
{
  global $_PREFERENCES,$preflocations;
  
  if (($type>=0)&&(!is_null($file)))
  {
    if (is_readable($file))
    {
      $_PREFERENCES[$type]=array();
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
          $_PREFERENCES[$type][$matches[1]]=$value;
        }
      }
      fclose($source);
    }
  }
  else
  {
    foreach ($preflocations as $type => $location)
    {
      if (!is_null($location))
      {
        loadPreferences($type,$location);
      }
    }
  }
}

// Evaluates a preference value, resolving references
function evaluatePref($text,$type)
{
  global $prefoverrides;
  
	$count=preg_match_all('/\$\[([^=#$[\]]+)\]/',$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
	for($p=$count-1; $p>=0; $p--)
	{
		$pref=$matches[1][$p][0];
		$offset=$matches[0][$p][1];
		$length=strlen($matches[0][$p][0]);
		$replacement=getPref($pref,$prefoverrides[$type]);
		$text=substr_replace($text,$replacement,$offset,$length);
	}
	return $text;
}

// Retrieves a preference. Returns a blank string for undefined preferences.
function getPref($pref,$type = 0)
{
  global $_PREFERENCES,$preftypes;
  
  for ($i=$type; $i<count($preftypes); $i++)
  {
    if (isset($_PREFERENCES[$i][$pref]))
    {
      return evaluatePref($_PREFERENCES[$i][$pref],$type);
    }
  }
  return "";
}

// Checks if a preference is defined
function isPrefSet($pref)
{
  global $_PREFERENCES,$preftypes;
  
  for ($i=0; $i<count($preftypes); $i++)
  {
    if (isset($_PREFERENCES[$i][$pref]))
    {
      return true;
    }
  }
  return false;
}

?>