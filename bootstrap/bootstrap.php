<?

/*
 * Swim
 *
 * Bootstrap all includes
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define('STATE_BOOTSTRAP',0);
define('STATE_STARTUP',1);
define('STATE_ADDONS',2);
define('STATE_STARTED',3);
define('STATE_PROCESSING',4);
define('STATE_SHUTDOWN',5);
define('STATE_COMPLETE',6);

$_STATE=STATE_BOOTSTRAP;

// Load the logging engine
require_once $bootstrap.'/logging.php';
error_reporting(E_ALL);

LoggerManager::setLogLevel('',LOG_LEVEL_INFO);
LoggerManager::setLogLevel('php',LOG_LEVEL_INFO);
LoggerManager::setLogLevel('swim.utils.shutdown',LOG_LEVEL_WARN);

// Load the preferences engine
require_once $bootstrap.'/prefs.php';

?>
