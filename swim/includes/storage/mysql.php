<?

/*
 * Swim
 *
 * MySQL management
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class MySQLResult extends StorageResult
{
	private $result;
	private $offset = 0;
	
	public function StorageResult($result)
	{
		$this->StorageResult();
		$this->result = $result;
	}
	
	public function fetch()
	{
		$this->offset++;
		return $this->result->fetch_array();
	}
	
	public function fetchObject()
	{
		$this->offset++;
		return $this->result->fetch_object();
	}
	
	public function numFields()
	{
		return $this->result->field_count;
	}
	
	public function fieldName($index)
	{
		return $this->result->fetch_field_direct($index);
	}
	
	public function key()
	{
		return $this->offset;
	}
	
	public function numRows()
	{
		return $this->result->num_rows;
	}
	
	public function seek($pos)
	{
		$result = $this->result->data_seek($pos);
		if ($result)
			$this->offset = $pos;
		return $result;
	}
}

class MySQLStorage extends StorageConnection
{
  private $db;
  
  public function MySQLStorage($host, $user, $pass, $db)
  {
  	$this->StorageConnection();
    $this->db = new mysqli($host, $user, $pass, $db);
    $this->log->debug('Loaded '.$db.' database from '.$host);
  }
  
  public function escape($text)
  {
    return $this->db->escape_string($text);
  }
  
  public function query($query)
  {
    $this->log->debug('query: '.$query);
    $result = $this->db->query($query);
    if ($result && $result !== TRUE)
    	return new StorageResult($result);
    return $result;
  }
  
  public function queryExec($query)
  {
    $this->log->debug('queryExec: '.$query);
    return $this->db->query($query);
  }
  
  public function lastInsertRowid()
  {
    return $this->db->insert_id;
  }
  
  public function changes()
  {
    return $this->db->affected_rows;
  }
  
  public function lastError()
  {
    return $this->db->errno;
  }
}

?>