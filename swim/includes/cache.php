<?

/*
 * Swim
 *
 * Object cache. Holds previously retrieved objects so they don't need to be built again.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class ObjectCache
{
  private static $cache = array();
  private static $log;
  
  public static function getItem($type, $key)
  {
    if (isset(self::$cache[$type]) && isset(self::$cache[$type][$key]))
    {
      return self::$cache[$type][$key];
    }
    return null;
  }
  
  public static function setItem($type, $key, $object)
  {
    if (!isset(self::$log))
      self::$log = LoggerManager::getLogger('swim.cache');
    
    if (isset(self::$cache[$type]) && isset(self::$cache[$type][$key]))
    {
      self::$log->warn('Overwriting cache entry '.$type.' '.$key);
    }
    self::$cache[$type][$key]=$object;
  }
}

?>