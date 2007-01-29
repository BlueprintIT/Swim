<?

/*
 * Swim
 *
 * Bootstrap all includes
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('STATE_BOOTSTRAP', 0);
define('STATE_STARTUP', STATE_BOOTSTRAP+1);
define('STATE_ADDONS', STATE_STARTUP+1);
define('STATE_STARTED', STATE_ADDONS+1);
define('STATE_PROCESSING', STATE_STARTED+1);
define('STATE_SHUTDOWN', STATE_PROCESSING+1);
define('STATE_COMPLETE', STATE_SHUTDOWN+1);

function loadBasePreferences()
{
  global $swimdir, $rootdir, $_PREFS, $_PREFSCOPES;
  
  $_PREFSCOPES = array();
  $_PREFSCOPES['default'] = new Preferences();
  $file=fopen($swimdir.'/bootstrap/default.conf','r');
  $_PREFSCOPES['default']->loadPreferences($file);
  fclose($file);
  $_PREFSCOPES['default']->setPref('storage.swimdir', $swimdir);
  $_PREFSCOPES['default']->setPref('storage.rootdir', $rootdir);
  
  $_PREFSCOPES['host'] = new Preferences();
  if (is_readable($swimdir.'/bootstrap/host.conf'))
  {
    $file=fopen($swimdir.'/bootstrap/host.conf','r');
    $_PREFSCOPES['host']->loadPreferences($file);
    fclose($file);
  }
  $_PREFSCOPES['host']->setParent($_PREFSCOPES['default']);
  $_PREFSCOPES['default']->setDelegate($_PREFSCOPES['host']);
  
  $_PREFS=$_PREFSCOPES['host'];
}

function loadSitePreferences()
{
  global $bootstrap, $_PREFS, $_PREFSCOPES;
  
  $confdir = $_PREFSCOPES['host']->getPref('storage.config');

  $_PREFSCOPES['site'] = new Preferences();
  if (is_readable($confdir.'/site.conf'))
  {
    $file=fopen($confdir.'/site.conf','r');
    $_PREFSCOPES['site']->loadPreferences($file);
    fclose($file);
  }

  if (is_readable($confdir.'/settings.conf'))
  {
    $file=fopen($confdir.'/settings.conf','r');
    $_PREFSCOPES['host']->loadPreferences($file, '', true);
    fclose($file);
  }
  $_PREFSCOPES['site']->setParent($_PREFSCOPES['host']);
  
  $_PREFSCOPES['default']->setDelegate($_PREFSCOPES['site']);
  
  $_PREFS=$_PREFSCOPES['site'];
}

$_STATE=STATE_BOOTSTRAP;

// Load the logging engine
require_once $swimdir.'/bootstrap/logging.php';
error_reporting(E_ALL);

LoggerManager::setLogLevel('',LOG_LEVEL_WARN);
LoggerManager::setLogLevel('php',LOG_LEVEL_INFO);
LoggerManager::setLogLevel('swim.storage',LOG_LEVEL_WARN);
LoggerManager::setLogLevel('swim.utils.shutdown',LOG_LEVEL_WARN);

// Load the preferences engine
require_once $swimdir.'/bootstrap/prefs.php';

loadBasePreferences();
loadSitePreferences();

LoggerManager::setLogOutput('',new FileLogOutput($_PREFS->getPref('logging.logfile')));
LoggerManager::setBaseDir($_PREFS->getPref('storage.swimdir'));

require_once $swimdir.'/bootstrap/baseincludes.php';

?>
