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

function redirect($request)
{
	$url=$request->encode();
	$url="http://".$_SERVER['HTTP_HOST'].$url;
	header("Location: ".$url);
	exit;
}

class Request
{
	var $page;
	var $version;
	var $mode = "normal";
	var $query = array();
	var $log;
	var $nested;
	
	function Request()
	{
		$this->log=&LoggerManager::getLogger("swim.request");
	}
	
	function encodePath()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	  	$page = $this->page;
	  	if ($this->mode=="admin")
	  	{
	  		$page="admin/".$page;
	  	}
	    return $_PREFS->getPref("url.base")."/".$page;
	  }
	  else
	  {
	    return $_PREFS->getPref("url.base");
	  }
	}
	
	function makeVars()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	    $newquery=$this->query;
	  }
	  else
	  {
	    if (($_PREFS->getPref("url.pathencoding")=="iterative")&&(count($this->query)>0))
	    {
	    	$newquery=array();
	      $newquery[$_PREFS->getPref("url.queryqueryvar")]=encodeQuery($this->query);
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
	  if (isset($this->nested))
	  {
	  	if (count($this->nested->query)>0)
	  	{
		  	$newquery[$_PREFS->getPref("url.nestedqueryvar")]=encodeQuery($this->nested->query);
	  	}
	  	$newquery[$_PREFS->getPref("url.nestedpathvar")]=$this->nested->page;
	    if ($this->nested->mode=="admin")
	    {
		    $newquery[$_PREFS->getPref("url.nestedmodevar")]=$this->nested->mode;
		  }
	  }
	  return $newquery;
	}
	
	function getFormVars()
	{
		$vars = $this->makeVars();
		$text="";
		foreach ($vars as $key => $value)
		{
			$text.="<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\" />\n";
		}
		return $text;
	}
	
	function encode()
	{
		$url=$this->encodePath();
		$vars=$this->makeVars();
		if (count($vars)>0)
		{
			$url.="?".encodeQuery($vars);
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
		if ($_SERVER['REQUEST_METHOD']=="POST")
		{
			if (isset($_POST))
			{
				$query=array_merge($query,$_POST);
			}
			else
			{
				$this->log->warn("POST global is not set. this should never happen");
			}
		}
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

		if (isset($query[$_PREFS->getPref("url.nestedpathvar")]))
		{
			$this->nested = new Request();
			$this->nested->path = $query[$_PREFS->getPref("url.nestedpathvar")];
			unset($query[$_PREFS->getPref("url.nestedpathvar")]);
			if (isset($query[$_PREFS->getPref("url.nestedqueryvar")]))
			{
				$this->nested->query = decodeQuery($query[$_PREFS->getPref("url.nestedpathvar")]);
				unset($query[$_PREFS->getPref("url.nestedqueryvar")]);
			}
			if (isset($query[$_PREFS->getPref("url.nestedmodevar")]))
			{
				$this->nested->mode = $query[$_PREFS->getPref("url.nestedmodevar")];
				unset($query[$_PREFS->getPref("url.nestedmodevar")]);
			}
		}
	  if ($_PREFS->getPref("url.pathencoding")=="path")
	  {
	  	// Site is setup to use path info to choose page
	  	
	    if ((isset($path))&&(strlen($path)>0))
	    {
		    if (substr($path,0,6)=="/admin")
		    {
		    	$this->mode="admin";
		    	$path=substr($path,6);
		    }
		    while ($path[0]=='/')
		    {
		      $path=substr($path,1);
		    }
		    $this->page=$path;
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
	      $this->page=$query[$_PREFS->getPref("url.querypathvar")];
	      unset($query[$_PREFS->getPref("url.querypathvar")]);
	    }
	    else
	    {
	    	$this->page="";
	    }
	    if (isset($query[$_PREFS->getPref("url.querymodevar")]))
	    {
	    	$this->mode=$query[$_PREFS->getPref("url.querymodevar")];
	      unset($query[$_PREFS->getPref("url.querymodevar")]);
	    }
	    if ($_PREFS->getPref("url.pathencoding")=="iterative")
	    {
	    	// Site is set to hold the rest of the query in another variable
	    	
	      if (isset($query[$_PREFS->getPref("url.queryqueryvar")]))
	      {
	        unset($query[$_PREFS->getPref("url.queryqueryvar")]);
	        $query=decodeQuery($query[$_PREFS->getPref("url.queryqueryvar")]);
	      }
	      else
	      {
	        $query=array();
	      }
	    }
	  }
	  $this->query=$query;
	}
}


?>