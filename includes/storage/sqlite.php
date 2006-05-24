<?

/*
 * Swim
 *
 * SQLite management
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function storage_init()
{
  global $_PREFS,$_STORAGE;
  
  // TODO Add in some locking using a transaction and test for tables here
  $log = LoggerManager::getLogger('swim.storage');
  $log->debug('Starting SQLite storage engine: '.sqlite_libversion());
  $create=false;
  if (!is_file($_PREFS->getPref('storage.config').'/storage.db'))
  {
    $create=true;
  }
  $_STORAGE = sqlite_factory($_PREFS->getPref('storage.config').'/storage.db');
  if ($create)
  {
    $log->debug('Initialising database');
    $file = fopen($_PREFS->getPref('storage.bootstrap').'/storage.sql','r');
    $query='';
    while (!feof($file))
    {
      $line=rtrim(fgets($file));
      $query.=$line;
      if (substr($query,-1)==';')
      {
        if (!$_STORAGE->queryExec($query))
        {
          $log->error('Error running query '.$query.' '.$_STORAGE->lastError());
        }
        $query='';
      }
      else
      {
        $query.=' ';
      }
    }
    fclose($file);
  }
}

function storage_escape($text)
{
  return sqlite_escape_string($text);
}

storage_init();

?>