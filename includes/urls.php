<?

/*
 * Swim
 *
 * Url encoding and decoding functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function encodeQuery($query)
{
  if (count($query)==0)
  {
    return "";
  }
  $result="";
  foreach ($query as $name => $value)
  {
    $result.="&".urlencode($name)."=".urlencode($value);
  }
  return substr($result,1);
}

function decodeQuery($query)
{
  parse_str($query,$result);
  return $result;
}

class Request
{
	var $page;
	var $query = array();
	
	function encode()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	    $url=$_PREFS->getPref("url.base")."/".$this->page;
	    $newquery=$this->query;
	  }
	  else
	  {
	    $url=$_PREFS->getPref("url.base");
	    if (($_PREFS->getPref("url.pathencoding")=="iterative")&&(count($this->query)>0))
	    {
	      $newquery['query']=encodeQuery($this->query);
	    }
	    else
	    {
	    	$newquery=$this->query;
	    }
	    $newquery[getPref("url.querypathvar")]=$this->page;
	  }
	  if (count($newquery)>0)
	  {
	    $url.="?".encodeQuery($newquery);
	  }
	  return $url;
	}
	
	function decodeCurrentRequest()
	{
		$path="";
		if (isset($_SERVER['PATH_INFO']))
		{
			$path=$_SERVER['PATH_INFO'];
		}
		$query=$_GET;
		$this->decode($path,$query);
	}
	
	function decode($path,$query)
	{
		global $_PREFS;
  	$this->query=$query;

	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	  	// Site is setup to use path info to choose page
	  	
	    if ((isset($path))&&(strlen($path)>1))
	    {
	      $this->page=substr($path,1);
	    }
	    else
	    {
	    	$this->page="";
	    }
	  }
	  else
	  {
	  	// Site uses a query variable to choose page
	    if (isset($query[$_PREFS->getPref("url.querypathvar")]))
	    {
	      $this->page=$this->query[$_PREFS->getPref("url.querypathvar")];
	      unset($this->query[$_PREFS->getPref("url.querypathvar")]);
	    }
	    else
	    {
	    	$this->page="";
	    }
	    if ($_PREFS->getPref("url.pathencoding")=="iterative")
	    {
	    	// Site is set to hold the rest of the query in another variable
	    	
	      if (isset($this->query['query']))
	      {
	        unset($this->query['query']);
	        $this->query=decodeQuery($this->query['query']);
	      }
	      else
	      {
	        $this->query=array();
	      }
	    }
	  }
	}
}


?>