<?

/*
 * Swim
 *
 * Startup code that loads the SWIM bootstrap
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$rootdir = dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"])));
$swimdir = dirname(dirname(__FILE__));

unset($source);
require_once $swimdir.'/bootstrap/bootstrap.php';
unset($swimdir);
unset($rootdir);

?>