<?

/*
 * Swim
 *
 * Database storage abstraction
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class StorageResult
{
  protected $log;

	function StorageResult()
	{
    $this->log = LoggerManager::getLogger('swim.storage.result');
	}
	
	public function fetch()
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function fetchObject()
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function numFields()
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function fieldName($index)
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function key()
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function numRows()
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function seek($pos)
	{
    $this->log->warn('Unimplemented storage method');
	}
	
	public function fetchSingle()
	{
		$row = $this->fetch();
		return $row[0];
	}
	
	public function fetchAll()
	{
		$total = array();
		while ($this->valid())
		{
			array_push($total, $this->fetch());
		}
		return $total;
	}
	
	public function column($index)
	{
		$row = $this->current();
		if ($row)
			return $row[$index];
	}
	
	public function current()
	{
		$row = $this->fetch();
		$this->prev();
		return $row;
	}
	
	public function next()
	{
		return $this->seek($this->key()+1);
	}
	
	public function valid()
	{
		return $this->key()<$this->numRows();
	}
	
	public function rewind()
	{
		$this->seek(0);
	}
	
	public function prev()
	{
		if ($this->hasPrev())
			return $this->seek($this->key()-1);
		else
			return false;
	}
	
	public function hasPrev()
	{
		return $this->key()>0;
	}
}

class StorageConnection
{
  protected $log;
  protected $new = false;
  protected $transaction = 0;

	function StorageConnection()
	{
    $this->log = LoggerManager::getLogger('swim.storage.connection');
	}
	
	function beginTransaction()
	{
		if ($this->transaction==0)
			$this->queryExec('BEGIN;');
		$this->transaction++;
	}
	
	function commitTransaction()
	{
		$this->transaction--;
		if ($this->transaction<=0)
			$this->queryExec('COMMIT;');
		if ($this->transaction<0)
		{
			$this->log->warntrace('Ending one transaction too many.');
			$this->transaction=0;
		}
	}
	
	function rollbackTransaction()
	{
		$this->transaction--;
		if ($this->transaction<=0)
			$this->queryExec('ROLLBACK;');
		else
			$this->log->errortrace('Could not rollback nested transaction.');
		if ($this->transaction<0)
		{
			$this->log->warntrace('Ending one transaction too many.');
			$this->transaction=0;
		}
	}
	
  public function escape($text)
  {
    $this->log->warn('Unimplemented storage method');
    return addslashes($text);
  }
  
  public function query($query)
  {
    $this->log->error('Unimplemented storage method');
  }
  
  public function queryExec($query)
  {
    $this->log->error('Unimplemented storage method');
  }

  public function lastInsertRowid()
  {
    $this->log->error('Unimplemented storage method');
  }
  
  public function changes()
  {
    $this->log->error('Unimplemented storage method');
  }
  
  public function lastError()
  {
    $this->log->error('Unimplemented storage method');
  }
  
  public function lastErrorText()
  {
    $this->log->error('Unimplemented storage method');
  }
  
  public function arrayQuery($query)
  {
    $this->log->debug('arrayQuery: '.$query);
    $result = $this->query($query);
    if ($result)
    	return $result->fetchAll();
    return $result;
  }
  
  public function singleQuery($query)
  {
    $this->log->debug('singleQuery: '.$query);
    $result = $this->query($query);
    if ($result)
    {
    	if ($result->numRows()==0)
    	{
    		return null;
    	}
    	if ($result->numRows()>1)
    	{
    		$total = array();
    		while ($row = $result->fetch())
    		{
    			array_push($total, $row[0]);
    		}
    		return $total;
    	}
    	return $result->fetchSingle();
    }
    return $result;
  }
  
  function isNew()
  {
  	return $this->new;
  }
  
	function executeFile($filename)
	{
		if (is_readable($filename))
		{
	    $file = fopen($filename,'r');
	    $query='';
	    while (!feof($file))
	    {
	      $line=rtrim(fgets($file));
	      $query.=$line;
	      if (substr($query,-1)==';')
	      {
	        if (!$this->queryExec($query))
	        {
	          $this->log->error('Error running query '.$query.' '.$_STORAGE->lastError());
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
		else
		{
			$this->log->error('Could not access sql file '.$filename);
		}
	}
}

require "sqlite.php";

function storage_init()
{
  global $_PREFS,$_STORAGE;
  
  // TODO Add in some locking using a transaction and test for tables here
  $log = LoggerManager::getLogger('swim.storage');
  $_STORAGE = new SqliteStorage($_PREFS->getPref('storage.config').'/storage.db');
  if ($_STORAGE->isNew())
  {
    $log->debug('Initialising database');
    if (is_readable($_PREFS->getPref('storage.bootstrap').'/storage.sql'))
	    $_STORAGE->executeFile($_PREFS->getPref('storage.bootstrap').'/storage.sql');
	  if (is_readable($_PREFS->getPref('storage.config').'/storage.sql'))
	    $_STORAGE->executeFile($_PREFS->getPref('storage.config').'/storage.sql');
  }
}

storage_init();

?>