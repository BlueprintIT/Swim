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
require_once "prefs.php";

// Include various utils
require_once $_PREFS->getPref("storage.includes")."/includes.php";
require_once $_PREFS->getPref("storage.blocks.classes")."/blocks.php";

LoggerManager::setLogLevel("",SWIM_LOG_WARN);
LoggerManager::setLogLevel("php",SWIM_LOG_ALL);
LoggerManager::setLogLevel("swim.request",SWIM_LOG_ALL);
LoggerManager::setLogLevel("swim.user",SWIM_LOG_ALL);
LoggerManager::setLogLevel("page",SWIM_LOG_ALL);

// Load the page to display
$request = new Request();
$request->decodeCurrentRequest();
$page = new Page($request);

if ($_USER->canAccess($page))
{
	$page->display();
}
else
{
	$newrequest = new Request();
	$newrequest->page="login";
	$newrequest->nested=&$request;
	$page = new Page($newrequest);
	$page->display();
}

?>