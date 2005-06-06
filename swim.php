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
require_once "base.php";

LoggerManager::setLogLevel("",SWIM_LOG_WARN);
LoggerManager::setLogLevel("php",SWIM_LOG_ALL);
LoggerManager::setLogLevel("swim.request",SWIM_LOG_ALL);
LoggerManager::setLogLevel("swim.user",SWIM_LOG_ALL);
LoggerManager::setLogLevel("swim.cache",SWIM_LOG_ALL);
LoggerManager::setLogLevel("page",SWIM_LOG_ALL);

// Load the page to display
$request=Request::decodeCurrentRequest();
$page = &$request->getPage();

if ($_USER->canAccess($request,$page))
{
	$page->display($request);
}
else
{
	$newrequest = new Request();
	$newrequest->page="login";
	$newrequest->nested=&$request;
	$page = new Page($newrequest);
	$page->display($newrequest);
}

?>