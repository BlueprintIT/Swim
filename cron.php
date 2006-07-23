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

$source = __FILE__;
while (is_link($source))
{
  $source=readlink($source);
}
$bootstrap=dirname($source).'/bootstrap';
unset($source);
require_once $bootstrap.'/bootstrap.php';
unset($bootstrap);

LoggerManager::setLogOutput('',new StdOutLogOutput());

SwimEngine::ensureStarted();

setContentType('text/plain');

SearchEngine::buildIndex();

?>