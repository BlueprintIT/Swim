<?

/*
 * Swim
 *
 * Includes to be loaded at the earliest opportunity.
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
require $includesdir.'/storage/storage.php';
require $includesdir.'/locking/locking.php';
require $includesdir.'/engine.php';
unset($includesdir);

?>