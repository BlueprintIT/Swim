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
    return '';
  }
  $result='';
  foreach ($query as $name => $value)
  {
    $result.='&'.urlencode($name).'='.urlencode($value);
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
	$url='http://'.$_SERVER['HTTP_HOST'].$url;
	header('Location: '.$url);
	exit;
}

class Request
{
	var $resource;
	var $method;
	var $query = array();
	var $log;
	var $nested;
	
	function Request()
	{
		$this->log=&LoggerManager::getLogger('swim.request');
	}
	
	function encodePath()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	    return $_PREFS->getPref('url.pagegen').'/'.$this->method.'/'.$this->resource;
	  }
	  else
	  {
	    return $_PREFS->getPref('url.pagegen');
	  }
	}
	
	function makeAllVars()
	{
		$newquery=$this->query;
		$newquery[$_PREFS->getPref('url.methodvar')]=$this->method;
		$newquery[$_PREFS->getPref('url.resourcevar')]=$this->resource;
		if (isset($this->nested))
		{
			$newquery[$_PREFS->getPref('url.nestedvar')]=encodeQuery($this->nested->encodeAsQuery());
		}
		return $newquery;
	}
	
	function makeVars()
	{
		global $_PREFS;
		
		$newquery=$this->query;
		if (isset($this->nested))
		{
			$newquery[$_PREFS->getPref('url.nestedvar')]=encodeQuery($this->nested->encodeAsQuery());
		}
	  if ($_PREFS->getPref('url.pathencoding')!='path')
	  {
			$newquery[$_PREFS->getPref('url.methodvar')]=$this->method;
			$newquery[$_PREFS->getPref('url.resourcevar')]=$this->resource;
	  }
	  return $newquery;
	}
	
	function getFormVars()
	{
		$vars = $this->makeVars();
		$text='';
		foreach ($vars as $key => $value)
		{
			$text.='<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		}
		return $text;
	}
	
	function encode()
	{
		$url=$this->encodePath();
		$vars=$this->makeVars();
		if (count($vars)>0)
		{
			$url.='?'.encodeQuery($vars);
		}
		return $url;
	}
	
	function &decodeCurrentRequest()
	{
		$path='';
		if (isset($_SERVER['PATH_INFO']))
		{
			$path=$_SERVER['PATH_INFO'];
		}
		$query=$_GET;
		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			if (isset($_POST))
			{
				$query=array_merge($query,$_POST);
			}
		}
		return Request::decode($path,$query);
	}
	
	function &decode($path,$query)
	{
		global $_PREFS;

		$request = new Request();
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	  	// Site is setup to use path info to choose page

	    if ((isset($path))&&(strlen($path)>0))
	    {
	    	while ((strlen($path)>0)&&($path[0]=='/'))
	    	{
	    		$path=substr($path,1);
	    	}
	    	
	    	if (strlen($path)>0)
	    	{
		    	$pos=strpos($path,'/');
		    	if ($pos===false)
		    	{
		    		$request->method=$path;
		    	}
		    	else
		    	{
		    		$request->method=substr($path,0,$pos);
		    		if (strlen($path)>$pos+1)
		    		{
			    		$request->resource=substr($path,$pos+1);
			    	}
		    	}
		    }
	    }
	  }
	  
    if (isset($query[$_PREFS->getPref('url.methodvar')]))
    {
      $request->method=$query[$_PREFS->getPref('url.methodvar')];
      unset($query[$_PREFS->getPref('url.methodvar')]);
    }
    
    if (isset($query[$_PREFS->getPref('url.resourcevar')]))
    {
      $request->resource=$query[$_PREFS->getPref('url.resourcevar')];
      unset($query[$_PREFS->getPref('url.resourcevar')]);
    }
    
    if (isset($query[$_PREFS->getPref('url.nestedvar')]))
    {
      $request->nested=&Request::decode('',decodeQuery($query[$_PREFS->getPref('url.nestedvar')]));
      unset($query[$_PREFS->getPref('url.nestedvar')]);
    }
    
    $request->query=$query;
    
    if (!isset($request->method))
    {
    	$request->method=$_PREFS->getPref('method.default');
    }
    
    if ((!isset($request->resource))&&($_PREFS->isPrefSet('method.'.$request->method.'.defaultresource')))
    {
    	$request->resource=$_PREFS->getPref('method.'.$request->method.'.defaultresource');
    }

	  return $request;
	}
}


?>