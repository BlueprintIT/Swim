<?

/*
 * Swim
 *
 * Database storage abstraction
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class StorageResult
{
  protected $log;

	function __construct()
	{
    $this->log = LoggerManager::getLogger('swim.storage.result');
	}
	
	public function fetch()
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function fetchObject()
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function numFields()
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function fieldName($index)
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function key()
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function numRows()
	{
    $this->log->warntrace('Unimplemented storage method');
	}
	
	public function seek($pos)
	{
    $this->log->warntrace('Unimplemented storage method');
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
  protected $querycount = 0;
  protected $execcount = 0;

	function __construct()
	{
    $this->log = LoggerManager::getLogger('swim.storage.connection');
	}
	
  public function buildQuery($fields, $tables, $where = array(), $order = array())
  {
    $query = 'SELECT ';
    if (!is_array($fields))
      $fields = array($fields);
    $first = true;
    foreach ($fields as $field)
    {
      if (!$first)
        $query.=',';
      $query.=$field;
      $first = false;
    }
    $query.=' FROM ';
    
    if (!is_array($tables))
      $tables = array($tables);
    $first = true;
    foreach ($tables as $table)
    {
      if (!$first)
        $query.=',';
      $query.=$table;
      $first = false;
    }
    
    if (!is_array($where))
      $tables = array($where);
    $first = true;
    foreach ($where as $q)
    {
      if ($first)
        $query.=' WHERE ';
      else
        $query.=' AND ';
      $query.=$q;
      $first = false;
    }
    
    if (!is_array($order))
      $order = array($order);
    $first = true;
    foreach ($order as $o)
    {
      if ($first)
        $query.=' ORDER BY ';
      else
        $query.=',';
      $query.=$o;
      $first = false;
    }
    
    return $query.';';
  }
  
  public function getQueryCount()
  {
    return $this->querycount+$this->execcount;
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
    $this->log->warntrace('Unimplemented storage method');
    return addslashes($text);
  }
  
  public function prepare($query)
  {
    $this->log->errortrace('Unimplemented storage method');
  }
  
  public function query($query)
  {
    $this->log->errortrace('Unimplemented storage method');
  }
  
  public function queryExec($query)
  {
    $this->log->errortrace('Unimplemented storage method');
  }

  public function lastInsertRowid()
  {
    $this->log->errortrace('Unimplemented storage method');
  }
  
  public function changes()
  {
    $this->log->errortrace('Unimplemented storage method');
  }
  
  public function lastError()
  {
    $this->log->errortrace('Unimplemented storage method');
  }
  
  public function lastErrorText()
  {
    $this->log->errortrace('Unimplemented storage method');
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
	          $this->log->error('Error running query '.$query.' '.$this->lastError());
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

require "mysql.php";
require "sqlite.php";

function storage_init()
{
  global $_PREFS,$_STORAGE;
  
  // TODO Add in some locking using a transaction and test for tables here
  $log = LoggerManager::getLogger('swim.storage');
  $type = $_PREFS->getPref('storage.dbtype');
  if ($type == 'sqlite')
  {
    $_STORAGE = new SqliteStorage($_PREFS->getPref('storage.config').'/storage.db');
  }
  else if ($type == 'mysql')
  {
    $host = $_PREFS->getPref('storage.mysql.host');
    $user = $_PREFS->getPref('storage.mysql.user');
    $pass = $_PREFS->getPref('storage.mysql.pass');
    $database = $_PREFS->getPref('storage.mysql.database');
    $_STORAGE = new MysqlStorage($host, $user, $pass, $database);
  }
    
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