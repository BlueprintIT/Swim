<?

/*
 * Swim
 *
 * Root code for page creation
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$source = __FILE__;
$sitebase = dirname($source);
if (is_dir($sitebase.'/swim'))
{
  $swimbase = $sitebase.'/swim';
}
else
{
  while (is_link($source))
  {
  	$source=readlink($source);
  }
  $swimbase = dirname($source);
}
unset($source);
require_once $swimbase.'/bootstrap/bootstrap.php';
unset($swimbase);
unset($sitebase);

SwimEngine::processCurrentRequest();

?>