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

define('ADMIN_PRIORITY_CONTENT',0);
define('ADMIN_PRIORITY_SECURITY',10);
define('ADMIN_PRIORITY_ADDON',20);
define('ADMIN_PRIORITY_EXTERNAL',30);

class AdminSection
{
  public function getName()
  {
  }
  
  public function getPriority()
  {
  }
  
  public function getURL()
  {
  }
  
  public function isAvailable()
  {
    return true;
  }
  
  public function isSelected($request)
  {
  }
}

class AddonAdminSection extends AdminSection
{
  private $url;
  
  public function AddonAdminSection($name, $url)
  {
    $this->name = $name;
    $this->url = $url;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_ADDON;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->method='view';
    $request->resource='internal/page/external';
    $request->query['url']=$this->url;

    return $request->encode();
  }
  
  public function isSelected($request)
  {
    if ($request->method!='view')
      return false;
    if ($request->resource!='internal/page/external')
      return false;
    if ($request->query['url']!=$this->url)
      return false;
      
    return true;
  }
}

class ExternalAdminSection extends AdminSection
{
  private $url;
  
  public function ExternalAdminSection($name, $url)
  {
    $this->name = $name;
    $this->url = $url;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_EXTERNAL;
  }
  
  public function getURL()
  {
    return $this->url;
  }
  
  public function isSelected($request)
  {
    return false;
  }
}

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

class AdminManager
{
  public static $log;
  public static $sections = array();
  
  public static function addSection($section)
  {
    if (!isset(self::$log))
      self::$log = LoggerManager::getLogger('swim.adminmanager');
      
    self::$log->debug('Adding admin section '.$section->getName());
    
    $pos = 0;
    while ($pos<count(self::$sections))
    {
      if (self::$sections[$pos]->getPriority()>$section->getPriority())
      {
        array_splice(self::$sections, $pos, 0, array($section));
        return;
      }
      $pos++;
    }
    array_push(self::$sections, $section);
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