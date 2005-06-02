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

// Start up the session
session_name("SwimSession");
session_start();

// Load the preferences engine
require_once "prefs.php";

// Include various utils
require_once $_PREFS->getPref("storage.includes")."/includes.php";
require_once $_PREFS->getPref("storage.blocks.classes")."/blocks.php";

// Load the page to display
$request = new Request();
$request->decodeCurrentRequest();
$page = new Page($request);

$page->display();

?>