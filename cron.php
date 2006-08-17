<?

/*
 * Swim
 *
 * Automated tasks designed for running from a cron job
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$source = $_SERVER["SCRIPT_FILENAME"];
$sitebase = dirname($source);
if (is_dir($sitebase.'/swim/bootstrap'))
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

LoggerManager::setLogOutput('',new StdOutLogOutput());

SwimEngine::ensureStarted();

setContentType('text/plain');

SearchEngine::buildIndex();

?>