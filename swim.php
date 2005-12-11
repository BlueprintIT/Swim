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

$source = __FILE__;
while (is_link($source))
{
	$source=readlink($source);
}
$bootstrap=dirname($source).'/bootstrap';
unset($source);

require_once $bootstrap.'/bootstrap.php';

LoggerManager::setLogLevel('php',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.user',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.locking',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.method.view',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.cache',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.block',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.page',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.resource',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.preferences',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.storage',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.categories',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.method.upload',SWIM_LOG_ALL);

$log=LoggerManager::getLogger('swim');

$log->debug('Request start');
$_STATE=STATE_PROCESSING;

$request=Request::decodeCurrentRequest();

callMethod($request);

$log->debug('Request end');
//shutdown();

?>