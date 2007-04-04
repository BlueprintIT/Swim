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

if (isset($_SERVER['SCRIPT_FILENAME']))
  $rootdir = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
elseif (isset($argv[0]))
  $rootdir = dirname(dirname(dirname($argv[0])));
else
{
  print('Unable to locate root directory error.');
  exit;
}
$swimdir = dirname(dirname(__FILE__));

unset($source);
require_once $swimdir.'/bootstrap/bootstrap.php';
unset($swimdir);
unset($rootdir);

?>
