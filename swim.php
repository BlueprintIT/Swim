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

function displayLogin(&$request)
{
	$newrequest = new Request();
	$newrequest->method='displayLogin';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

function displayError($request)
{
	$newrequest = new Request();
	$newrequest->method='error';
	$newrequest->nested=&$request;
	callMethod($newrequest);
}

$request=&Request::decodeCurrentRequest();

if ($_USER->canAccess($request))
{
	callMethod($request);
}
else
{
	displayLogin($request);
}


?>