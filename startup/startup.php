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

$sitebase = dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"])));
$swimbase = dirname(dirname(__FILE__));

unset($source);
require_once $swimbase.'/bootstrap/bootstrap.php';
unset($swimbase);
unset($sitebase);

?>