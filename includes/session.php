<?

/*
 * Swim
 *
 * Data shared across sessions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Session
{
  private static $sessionname = 'SwimSession';
  private static $initialised = false;
  private static $user = null;
  private static $log = null;
  
  public function init()
  {
    self::$log = LoggerManager::getLogger('swim.session');
  }
  
  private static function inSession()
  {
    return (self::$initialised || isset($_COOKIE[self::$sessionname]));
  }
  
  private static function ensureSession()
  {
    if (self::$initialised)
      return;
      
    session_name(self::$sessionname);
    session_cache_limiter('none');
    session_start();

    if (!isset($_SESSION['data']))
      $_SESSION['data'] = array();

    self::$initialised = true;
  }
  
  public static function getUser()
  {
    if (self::$user !== null)
      return self::$user;
    
    if (self::inSession())
    {
      self::ensureSession();
      if (isset($_SESSION['data']['user']))
      {
        self::$user = UserManager::getUser($_SESSION['data']['user']);
        self::$user->logged = true;
      }
      else
        self::$user = new User();
    }
    else
      self::$user = new User();
      
    return self::$user;
  }
  
  public static function setUser($user)
  {
    self::ensureSession();
    if ($user === null)
    {
      unset($_SESSION['data']['user']);
      self::$user = new User();
    }
    else
    {
      $_SESSION['data']['user'] = $user->getUsername();
      self::$user = $user;
    }
  }
  
  public static function getCurrentVariant()
  {
    if (self::inSession())
    {
      self::ensureSession();
      if (isset($_SESSION['data']['variant']))
        return $_SESSION['data']['variant'];
      else
        return 'default';
    }
    else
      return 'default';
  }
  
  public static function setCurrentVariant($variant)
  {
    self::ensureSession();
    $_SESSION['data']['variant'] = $variant;
  }
}

Session::init();

?>