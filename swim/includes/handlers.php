<?

/*
 * Swim
 *
 * File Handlers
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FileHandlers
{
  private static $handlers;
  
  static function loadHandlers()
  {
    global $_PREFS;
    
    $dir = $_PREFS->getPref('storage.handlers');
    $handlers = opendir($dir);
    while (($handler = readdir($handlers)) !== false)
    {
      if ((is_readable($dir.'/'.$handler)) && (substr($handler,-4)=='.php'))
      {
        require_once $dir.'/'.$handler;
      }
    }
  }
  
  static function output($mimetype, $request, $resource)
  {
    $factory = self::$handlers[$mimetype];
    if ($factory !== null)
      $factory->output($request, $resource);
  }
  
  static function canHandle($mimetype)
  {
    return isset(self::$handlers[$mimetype]);
  }
  
  static function addHandler($factory)
  {
    $types = $factory->getMimetypes();
    foreach ($types as $type)
    {
      self::$handlers[$type] = $factory;
    }
  }
}

FileHandlers::loadHandlers();

?>