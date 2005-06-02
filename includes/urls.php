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
	var $version;
	var $mode = "normal";
	var $query = array();
	
	function encode()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	  	$page = $this->page;
	  	if ($this->mode=="admin")
	  	{
	  		$page="admin/".$page;
	  	}
	    $url=$_PREFS->getPref("url.base")."/".$page;
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
	    $newquery[$_PREFS->getPref("url.querypathvar")]=$this->page;
	    if ($this->mode=="admin")
	    {
		    $newquery[$_PREFS->getPref("url.querymodevar")]=$this->mode;
		  }
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
		$this->choosePage();
	}
	
	function choosePage()
	{
		global $_PREFS;
		
		// If there is no page then use the default page
		if ($this->page=="")
		{
			if ($this->mode=="admin")
			{
				$this->page=$_PREFS->getPref("pages.admin");
			}
			else
			{
				$this->page=$_PREFS->getPref("pages.default");
			}
		}
		
		// These are the fallback pages we want to display in order of preference
		$selection = array($_PREFS->getPref("pages.error"), $_PREFS->getPref("pages.default"));
		
		while (!($this->isValidPage()))
		{
			if (count($selection)==0)
			{
				trigger_error("This website has not been properly configured.");
				exit;
			}
			
			// Bad page so get the next fallback and clear the query.
			$this->page=array_shift($selection);
			$this->mode="normal";
			$this->query=array();
		}
	}
	
	function isValidPage()
	{
		global $_PREFS;
		return is_readable(getCurrentResource($_PREFS->getPref("storage.pages")."/".$this->page)."/page.conf");
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
	    if (substr($this->page,0,6)=="admin/")
	    {
	    	$this->mode="admin";
	    	$this->page=substr($this->page,6);
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
	    if (isset($query[$_PREFS->getPref("url.querymodevar")]))
	    {
	    	$this->mode=$this->query[$_PREFS->getPref("url.querymodevar")];
	      unset($this->query[$_PREFS->getPref("url.querymodevar")]);
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