<?

/*
 * Swim
 *
 * Tests for resource caches
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$dir=$_PREFS->getPref('storage.test');

LockManager::lockResourceRead($dir);
LockManager::lockResourceWrite($dir);
LockManager::lockResourceRead($dir);
LockManager::unlockResource($dir);
LockManager::unlockResource($dir);
LockManager::unlockResource($dir);
LockManager::lockResourceWrite($dir);
LockManager::lockResourceRead($dir);
LockManager::unlockResource($dir);
LockManager::unlockResource($dir);

$log->info('The following should show a single warning about an attempt to unlock an unlocked resource.');
LockManager::unlockResource($dir);

$log->info('Upon test completion a warning should display about a remaining lock on a resource.');
LockManager::lockResourceRead($dir);

$mkdir = new MkdirLocker();
$lock=&$mkdir->getReadLock($log,$dir);
$lock=&$mkdir->getReadLock($log,$dir);
$mkdir->unlock($log,$dir,$lock,LOCK_READ);
$log->info('This should pause until the stale lock file is removed.');
$lock=&$mkdir->getWriteLock($log,$dir);
$mkdir->unlock($log,$dir,$lock,LOCK_WRITE);
$lock=&$mkdir->getReadLock($log,$dir);
$mkdir->unlock($log,$dir,$lock,LOCK_READ);

$file = fopen($dir.'/locktest','w');
logTest('5','Flock shared',flock($file,LOCK_SH));
logTest('6','Flock exclusive',flock($file,LOCK_EX));
logTest('7','Flock unlock',flock($file,LOCK_UN));

$file2 = fopen($dir.'/locktest','r');

flock($file,LOCK_SH);
logTest('8','Flock multiple shared',flock($file2,LOCK_SH+LOCK_NB));
flock($file2,LOCK_UN);
flock($file,LOCK_UN);
flock($file,LOCK_EX);
logTest('9','Flock attempt to get shared access to exclusive lock',!flock($file2,LOCK_SH+LOCK_NB));
flock($file2,LOCK_UN);
logTest('10','Flock attempt to get exclusive access to exclusive lock',!flock($file2,LOCK_EX+LOCK_NB));
flock($file2,LOCK_UN);
flock($file,LOCK_UN);

fclose($file2);
fclose($file);

?>
