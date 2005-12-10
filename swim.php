<?

/*
 * Swim
 *
 * Root code for page creation
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
$source = __FILE__;
while (is_link($source))
{
	$source=readlink($source);
}
$bootstrap=dirname($source).'/bootstrap';
unset($source);

// Load the preferences engine
require_once $bootstrap.'/logging.php';

// Load the preferences engine
require_once $bootstrap.'/prefs.php';

LoggerManager::setLogLevel('',SWIM_LOG_INFO);
LoggerManager::setLogLevel('swim.utils.shutdown',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.user',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.locking',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.method.view',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.cache',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.block',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.page',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.resource',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.preferences',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.storage',SWIM_LOG_ALL);

$_STATE=STATE_STARTUP;
// Include various utils
require_once $bootstrap.'/includes.php';
require_once $_PREFS->getPref('storage.blocks.classes').'/blocks.php';
unset($bootstrap);

$log=&LoggerManager::getLogger('swim');

$log->debug('Request start');
$_STATE=STATE_PROCESSING;

$request=&Request::decodeCurrentRequest();

callMethod($request);

$log->debug('Request end');
//shutdown();

?>