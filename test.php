<?

/*
 * Swim
 *
 * SWIM unit tests
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function logTest($id,$desc,$comparison)
{
  global $log;
  
  if ($comparison)
  {
    $log->debug('Passed test '.$id.': '.$desc);
  }
  else
  {
    $log->error('Failed test '.$id.': '.$desc);
  }
}

function run_test($file)
{
  global $_PREFS,$log;
  
  include($_PREFS->getPref('storage.test').'/'.$file);
}

function run_all_tests()
{
  global $_PREFS,$log;
  
  $testdir=opendir($_PREFS->getPref('storage.test'));
  while (($testfile = readdir($testdir)) !== false)
  {
    if (substr($testfile,-4)=='.php')
    {
      $type=substr($testfile,0,-4);
      $log = LoggerManager::getLogger('test.'.$type);
      $log->info('Running tests from '.$testfile);  
      run_test($testfile);
    }
  }
}

$source = __FILE__;
while (is_link($source))
{
  $source=readlink($source);
}
$bootstrap=dirname($source).'/bootstrap';
unset($source);
require_once $bootstrap.'/bootstrap.php';
unset($bootstrap);
SwimEngine::startup();
AddonManager::disable();
SwimEngine::ensureStarted();

LoggerManager::setLogOutput("",new PageLogOutput());
LoggerManager::setLogLevel('test',LOG_LEVEL_INFO);

if (isset($_GET['level']))
{
  $lev = strtolower($_GET['level']);
  if ($lev=='debug')
    LoggerManager::setLogLevel('test',LOG_LEVEL_DEBUG);
  else if ($lev=='info')
    LoggerManager::setLogLevel('test',LOG_LEVEL_INFO);
  else if ($lev=='warn')
    LoggerManager::setLogLevel('test',LOG_LEVEL_WARN);
  else if ($lev=='error')
    LoggerManager::setLogLevel('test',LOG_LEVEL_ERROR);
  else if ($lev=='fatal')
    LoggerManager::setLogLevel('test',LOG_LEVEL_FATAL);
}

$log=LoggerManager::getLogger('test');

$log->info('Test start');
$_STATE=STATE_PROCESSING;

run_all_tests();

$log=LoggerManager::getLogger('test');
$log->info('Test end');
//SwimEngine::shutdown();;

?>