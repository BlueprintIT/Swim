<?

/*
 * Swim
 *
 * SQLite management
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class SqliteStorage
{
  private $db;
  private $log;
  
  public function SqliteStorage($filename)
  {
    $this->log = LoggerManager::getLogger('swim.storage');
    $this->db = sqlite_factory($filename);
    $this->log->debug('Loaded database from '.$filename);
  }
  
  public function escape($text)
  {
    return sqlite_escape_string($text);
  }
  
  public function query($query)
  {
    $this->log->debug('query: '.$query);
    return $this->db->query($query);
  }
  
  public function queryExec($query)
  {
    $this->log->debug('queryExec: '.$query);
    return $db->queryExec($query);
  }
  
  public function arrayQuery($query)
  {
    $this->log->debug('arrayQuery: '.$query);
    return $this->db->arrayQuery($query);
  }
  
  public function singleQuery($query)
  {
    $this->log->debug('singleQuery: '.$query);
    return $this->db->singleQuery($query);
  }
  
  public function lastInsertRowid()
  {
    return $this->db->lastInsertRowid();
  }
  
  public function changes()
  {
    return $this->db->changes();
  }
  
  public function lastError()
  {
    return $this->db->lastError();
  }
}

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
  $_STORAGE = new SqliteStorage($_PREFS->getPref('storage.config').'/storage.db');
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

storage_init();

?>