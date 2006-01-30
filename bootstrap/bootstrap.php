<?

/*
 * Swim
 *
 * Bootstrap all includes
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('STATE_BOOTSTRAP',0);
define('STATE_STARTUP',1);
define('STATE_PROCESSING',2);
define('STATE_SHUTDOWN',3);
define('STATE_COMPLETE',4);

$_STATE=STATE_BOOTSTRAP;
error_reporting(E_ALL);

// Load the preferences engine
require_once $bootstrap.'/logging.php';

LoggerManager::setLogLevel('',SWIM_LOG_INFO);
LoggerManager::setLogLevel('swim.utils.shutdown',SWIM_LOG_WARN);

// Load the preferences engine
require_once $bootstrap.'/prefs.php';

$_STATE=STATE_STARTUP;
// Include various utils
require_once $bootstrap.'/includes.php';
require_once $_PREFS->getPref('storage.blocks.classes').'/blocks.php';
unset($bootstrap);

?>
