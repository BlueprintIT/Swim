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
    self::$cache[$type][$key]=$object;
  }
}

?>