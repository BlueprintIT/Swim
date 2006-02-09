<?

/*
 * Swim
 *
 * Addons management
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Addon
{
  function getID()
  {
    return null;
  }
  
  function getDescription()
  {
    return "Unnamed addon";
  }
  
  function startup()
  {
  }
  
  function shutdown()
  {
  }
}

class AddonManager
{
  private static $addons = array();
  private static $disabled = false;
  
  static function disable()
  {
    if (count(self::$addons)==0)
      self::$disabled=true;
  }
  
  static function registerAddon($addon)
  {
    $id=$addons->getID();
    if ($id!==null)
    {
      self::$addons[$id]=$addon;
    }
  }
  
  static function loadAddons()
  {
    global $_PREFS;
    
    if (!self::$disabled)
    {
      $dir = $_PREFS->getPref('storage.addons');
      $addons = opendir($dir);
      while (($addon = readdir($addons)) !== false)
      {
        if ((is_dir($dir.'/'.$addon))&&(is_readable($dir.'/'.$addon.'/swimaddon.php')))
        {
          require_once $dir.'/'.$addon.'/swimaddon.php';
        }
      }
      self::startup();
    }
  }
  
  static function startup()
  {
    foreach (self::$addons as $addon)
    {
      $addon->startup();
    }
  }
  
  static function shutdown()
  {
    foreach (self::$addons as $addon)
    {
      $addon->startup();
    }
    self::$addons = array();
  }
}

?>