<?

/*
 * Swim
 *
 * Root code for page creation
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// Load the preferences engine
require "prefs.php";

function displayBlock($attrs)
{
  $attrlist="id=\"".$attrs['id']."\"";
  if (isset($attrs['class']))
  {
    $attrlist="class=\"".$attrs['class']."\" ".$attrlist;
  }
  if (isset($attrs['style']))
  {
    $attrlist="style=\"".$attrs['style']."\" ".$attrlist;
  }
  print("<div ".$attrlist.">");
  print("</div>");
}

function displayTemplate($name)
{
  $template = fopen(getPref("storage.templates")."/".$name.".template","r");
  while (!feof($template))
  {
    $line=fgets($template);
    if (preg_match_all("/<block ([^>]*)\/>/",$line,$matches,PREG_OFFSET_CAPTURE))
    {
      $startpos=0;
      for ($pos=0; $pos<count($matches[0]); $pos++)
      {
        print(substr($line,$startpos,$matches[0][$pos][1]-$startpos));
        $startpos=$matches[0][$pos][1]+strlen($matches[0][$pos][0]);
        if (preg_match_all("/(\S*)=\"([^\"]*)\"/",$matches[1][$pos][0],$defined))
        {
          $attrs=array();
          for ($dpos=0; $dpos<count($defined[0]); $dpos++)
          {
            $attrs[$defined[1][$dpos]]=$defined[2][$dpos];
          }
          
          if (isset($attrs['id']))
          {
            displayBlock($attrs);
          }
        }
      }
      print(substr($line,$startpos));
    }
    else
    {
      print($line);
    }
  }
  fclose($template);
}

// Load the site preferences
loadPreferences();

// Include various utils
require getPref("storage.includes")."/urls.php";

// Figure out what page we are viewing
decodeRequest();

displayTemplate("base");

?>