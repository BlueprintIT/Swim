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
      self::setDefaultVariant('default');
    }
  }
  
  public static function getDefaultVariant($variant)
  {
    return $_SESSION['data']['variant'];
  }
  
  public static function setDefaultVariant($variant)
  {
    $_SESSION['data']['variant'] = $variant;
  }
}

Session::init();

?>