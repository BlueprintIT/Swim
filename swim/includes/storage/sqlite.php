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

class SqliteStorageResult extends StorageResult
{
	private $result;
	
	function SqliteStorageResult($result)
	{
		$this->StorageResult();
		$this->result = $result;
	}

	public function fetch()
	{
		return $this->result->fetch();
	}
	
	public function fetchObject()
	{
		return $this->result->fetchObject();
	}
	
	public function numFields()
	{
    return $this->result->numFields();
	}
	
	public function fieldName($index)
	{
    return $this->result->fieldName($index);
	}
	
	public function current()
	{
		return $this->current();
	}
	
	public function key()
	{
		parent::key();
	}
	
	public function numRows()
	{
    return $this->result->numRows();
	}
	
	public function seek($pos)
	{
    return $this->result->seek($pos);
	}
	
	public function valid()
	{
		return $this->result->valid();
	}
}

class SqliteStorage extends StorageConnection
{
  private $db;
  
  public function SqliteStorage($filename)
  {
  	$this->StorageConnection();
	  if (!is_file($filename))
	  {
	    $this->new=true;
	  }
  	$this->log->debug('Starting SQLite storage engine: '.sqlite_libversion());
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
    $result = $this->db->query($query);
    if ($result && $result !== TRUE)
	    return new SqliteStorageResult($result);
    if ($result===false)
    	$this->log->errorTrace('Query error '.$this->lastErrorText().' for "'.$query.'"');
	  return $result;
  }
  
  public function queryExec($query)
  {
    $this->log->debug('queryExec: '.$query);
    $result = @$this->db->queryExec($query);
    if ($result===false)
    	$this->log->errorTrace('Query error '.$this->lastErrorText().' for "'.$query.'"');
    return $result;
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

  public function lastErrorText()
  {
    return sqlite_error_string($this->db->lastError());
  }
}

?>