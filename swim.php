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

// Load the base includes
require_once 'base.php';

LoggerManager::setLogLevel('',SWIM_LOG_WARN);
LoggerManager::setLogLevel('php',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.request',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.user',SWIM_LOG_ALL);
LoggerManager::setLogLevel('swim.cache',SWIM_LOG_ALL);
LoggerManager::setLogLevel('page',SWIM_LOG_ALL);

$log=&LoggerManager::getLogger('swim');

$request=&Request::decodeCurrentRequest();

if ($_USER->canAccess($request))
{
	callMethod($request);
}
else
{
}


?>