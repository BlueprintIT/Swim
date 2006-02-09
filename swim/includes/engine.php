<?

/*
 * Swim
 *
 * SWIM engine code
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class SwimEngine
{
  static function startup()
  {
    global $_PREFS, $_STATE;
    
    if ($_STATE==STATE_BOOTSTRAP)
    {
      $log=LoggerManager::getLogger('swim.engine');
      $log->debug('Engine startup.');

      $_STATE=STATE_STARTUP;
      // Include various utils
      require_once $_PREFS->getPref('storage.bootstrap').'/includes.php';
    }
  }
  
  static function loadAddons()
  {
    global $_STATE;
    $log=LoggerManager::getLogger('swim.engine');
    
    if ($_STATE<STATE_STARTUP)
      self::startup();
    
    if ($_STATE==STATE_STARTUP)
    {
      $log->debug('Loading addons.');
  
      $_STATE=STATE_ADDONS;
      AddonManager::loadAddons();
      $_STATE=STATE_STARTED;
    }
  }
  
  static function ensureStarted()
  {
    global $_STATE;
    
    if ($_STATE<STATE_STARTED)
      self::loadAddons();
  }
  
  static function processRequest($request)
  {
    global $_STATE, $_PREFS;
    
    if ($_STATE<STATE_STARTED)
      self::ensureStarted();
    
    $log=LoggerManager::getLogger('swim.engine');
    
    LoggerManager::pushState();
    $log->debug('processing');
    
    if ($_STATE>STATE_PROCESSING)
    {
      $log->warntrace('Process attempted in invalid state.');
    }

    $prevstate=$_STATE;
    $_STATE=STATE_PROCESSING;
    
    $methodfile=$request->method.".php";
    $methodfunc='method_'.$request->method;
    if (is_readable($_PREFS->getPref('storage.methods').'/'.$methodfile))
    {
      require_once($_PREFS->getPref('storage.methods').'/'.$methodfile);
      if (function_exists($methodfunc))
      {
        $methodfunc($request);
      }
      else
      {
        displayServerError($request);
      }
    }
    else
    {
      displayNotFound($request);
    }
    
    $_STATE=$prevstate;
    $log->debug('processing complete');
    LoggerManager::popState();
  }
  
  static function processCurrentRequest()
  {
    global $_STATE;
    
    if ($_STATE<STATE_STARTED)
      self::ensureStarted();
    
    self::processRequest(Request::decodeCurrentRequest());
  }
  
  static function shutdown()
  {
    global $_STATE;
    $log=LoggerManager::getLogger('swim.engine');
    if ($_STATE<STATE_SHUTDOWN)
    {
      $log->debug('Engine shutdown');
      $_STATE=STATE_SHUTDOWN;
      AddonManager::shutdown();
      LockManager::shutdown();
      LoggerManager::shutdown();
      $_STATE=STATE_COMPLETE;
      exit;
    }
    else if ($_STATE==STATE_SHUTDOWN)
    {
      $log->warntrace('Shutdown called during shutdown phase.');
    }
    else
    {
      $log->debug('Shutdown called after shutdown complete (shutdown handler fallback).');
    }
  }
}

function shutdown_hook()
{
  SwimEngine::shutdown();
}

register_shutdown_function('shutdown_hook');

?>