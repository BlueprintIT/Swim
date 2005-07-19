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

// Load the preferences engine
require_once 'prefs.php';

// Include various utils
require_once $_PREFS->getPref('storage.includes').'/includes.php';
require_once $_PREFS->getPref('storage.blocks.classes').'/blocks.php';

LoggerManager::setLogLevel('',SWIM_LOG_INFO);
//LoggerManager::setLogLevel('swim.user',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.locking',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.method.view',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.cache',SWIM_LOG_WARN);
LoggerManager::setLogLevel('swim.block',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.page',SWIM_LOG_WARN);
//LoggerManager::setLogLevel('swim.resource',SWIM_LOG_ALL);
//LoggerManager::setLogLevel('swim.parser',SWIM_LOG_WARN);

$log=&LoggerManager::getLogger('swim');

$log->debug('Request start');

$request=&Request::decodeCurrentRequest();

callMethod($request);

$log->debug('Request end');

?>