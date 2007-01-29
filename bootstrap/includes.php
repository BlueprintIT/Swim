<?

/*
 * Swim
 *
 * Main includes
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// Include all the support routines

$includesdir = $_PREFS->getPref('storage.includes');
require $includesdir.'/cache.php';
require $includesdir.'/xml.php';
require $includesdir.'/addons.php';
require $includesdir.'/security.php';
require $includesdir.'/utils.php';
require $includesdir.'/urls.php';
require $includesdir.'/mimetypes.php';
require $includesdir.'/smarty.php';
require $includesdir.'/session.php';
require $includesdir.'/items/items.php';
require $includesdir.'/search.php';
unset($includesdir);

?>