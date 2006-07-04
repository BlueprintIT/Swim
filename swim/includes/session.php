<?

/*
 * Swim
 *
 * Data shared across sessions
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Session
{
  public function init()
  {
    if (!isset($_SESSION['data']))
    {
      $_SESSION['data'] = array();
      self::setCurrentVariant('default');
    }
  }
  
  public static function getCurrentVariant()
  {
    return $_SESSION['data']['variant'];
  }
  
  public static function setCurrentVariant($variant)
  {
    $_SESSION['data']['variant'] = $variant;
  }
}

Session::init();

?>