<?

/*
 * Swim
 *
 * Addons management
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('ADMIN_PRIORITY_CONTENT',0);
define('ADMIN_PRIORITY_GENERAL',10);
define('ADMIN_PRIORITY_SECURITY',20);
define('ADMIN_PRIORITY_ADDON',30);
define('ADMIN_PRIORITY_EXTERNAL',40);

class AdminSection
{
  public function getIcon()
  {
    return "";
  }
  
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
    return Session::getUser()->isLoggedIn();
  }
  
  public function isSelected($request)
  {
  }
}

class AddonAdminSection extends AdminSection
{
  private $url;
  private $icon;
  
  public function AddonAdminSection($name, $url, $icon)
  {
    $this->name = $name;
    $this->url = $url;
    $this->icon = $icon;
  }
  
  public function getIcon()
  {
    return $this->icon;
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
    $request->setMethod('admin');
    $request->setPath('external.tpl');
    $request->setQueryVar('url', $this->url);
    $request->setQueryVar('title', $this->name);

    return $request->encode();
  }
  
  public function isSelected($request)
  {
    if ($request->getMethod()!='admin')
      return false;
    if ($request->getPath()!='external.tpl')
      return false;
    if ($request->getQueryVar('url')!=$this->url)
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
  
  function getRewrites()
  {
  	return array();
  }
}

class AdminManager
{
  public static $log;
  public static $sections = array();
  
  public static function getAvailableSections()
  {
    $sections = array();
    foreach (self::$sections as $section)
    {
      if ($section->isAvailable())
        array_push($sections, $section);
    }
    return $sections;
  }
  
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

class OptionsAdminSection extends AdminSection
{
  public function getIcon()
  {
    global $_PREFS;
    
    return $_PREFS->getPref('url.admin.static').'/icons/web-page-blue.gif';
  }
  
  public function getName()
  {
    return 'General Options';
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_GENERAL;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('options/index.tpl');
    return $request->encode();
  }
  
  public function isAvailable()
  {
    return Session::getUser()->isLoggedIn();
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,8)=='options/'))
      return true;
      
    return false;
  }
}

class AddonManager
{
  private static $addons = array();
  private static $disabled = false;
  
  static function getRewrites()
  {
  	$rewrites = array();
  	foreach (self::$addons as $addon)
  	{
  		array_merge($rewrites, $addon->getRewrites());
  	}
  	return $rewrites;
  }
  
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
      if (is_dir($dir))
      {
        $addons = opendir($dir);
        while (($addon = readdir($addons)) !== false)
        {
          if ((is_dir($dir.'/'.$addon))&&(is_readable($dir.'/'.$addon.'/swimaddon.php')))
          {
            require_once $dir.'/'.$addon.'/swimaddon.php';
          }
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

AdminManager::addSection(new OptionsAdminSection());

?>