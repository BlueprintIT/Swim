<?

/*
 * Swim
 *
 * Base includes that are common to the api and the page generator
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

?>