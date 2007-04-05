<?

/*
 * Swim
 *
 * Object cache. Holds previously retrieved objects so they don't need to be built again.
 *
 * Copyright Blueprint IT Ltd. 2007
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
  
  public static function removeItem($type, $key)
  {
    if (!isset(self::$log))
      self::$log = LoggerManager::getLogger('swim.cache');
    
    unset(self::$cache[$type][$key]);
  }
}

class RequestCache
{
	private static $defined = false;
	
	public static function isCacheDefined()
	{
		return self::$defined;
	}
	
	public static function setNoCache()
	{
		if (self::$defined)
			return;
		self::$defined = true;
		
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Pragma: no-cache');
		header('Expires: '.httpdate(time()-3600));
	}
	
	public static function setCacheTime($minutes)
	{
		if (self::$defined)
			return;
		self::$defined = true;
		
		header('Cache-Control: max-age='.($minutes*60).', public');
		header('Pragma: cache');
		header('Expires: '.httpdate(time()+($minutes*60)));
	}
	
	public static function setCacheInfo($date,$etag=false)
	{
		if (self::$defined)
			return;
		self::$defined = true;
		
		$log=LoggerManager::getLogger('swim.requestcache');
    
    $log->debug('Checking cache info for '.$_SERVER['REQUEST_URI']);
    
    header('Cache-Control: max-age=0, pre-check=0, post-check=0');
    
		if ($date!=false)
			header('Last-Modified: '.httpdate($date));
		if ($etag!==false)
			header('ETag: '.$etag);
	
		if (((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
			||((isset($_SERVER['HTTP_IF_NONE_MATCH'])))&&($etag!==false))
		{
			$log->debug('Found a cache check header');
			if ((isset($_SERVER['HTTP_IF_NONE_MATCH']))&&($etag!==false))
			{
				$log->debug('Checking etag');
				if ($etag!=$_SERVER['HTTP_IF_NONE_MATCH'])
				{
					$log->debug('ETag differs');
					return;
				}
			}
			if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))&&($date!==false))
			{
				$log->debug('Checking modification date');
				$checkdate=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
				if ($checkdate!=$date)
				{
					$log->debug('Date differs');
					return;
				}
			}
			$log->debug('Resource is cached');
			header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
			SwimEngine::shutdown();
		}
    else
    {
      $log->debug('No cache information in request');
    }
	}
}

?>