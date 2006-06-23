<?

/*
 * Swim
 *
 * Url encoding and decoding functions
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function urlPathEncode($path)
{
  return preg_replace("/[^a-zA-Z0-9\\/\\$-_.+!*'(),;:@&=]/e", "'%'.dechex(ord('\\0'))", $path);
}

function urlPathDecode($path)
{
  return rawurldecode($path);
}

function encodeQuery($query)
{
  if (count($query)==0)
  {
    return '';
  }
  $result='';
  foreach ($query as $name => $value)
  {
  	if (is_array($value))
  	{
  		foreach ($value as $key => $var)
  		{
  			$result.='&'.urlencode($name).'['.$key.']='.urlencode($var);
  		}
  	}
  	else
  	{
	    $result.='&'.urlencode($name).'='.urlencode($value);
	  }
  }
  return substr($result,1);
}

function cleanVariable($text)
{
  $text = preg_replace("/<script.*?script>/si", "", "$text");
  $text = preg_replace("/<script.*>/si", "", "$text");
  $text = preg_replace("/<\/script>/si", "", "$text");
  return $text;
}

function extractVariable(&$array,$name,$value)
{
	$spos=strpos($name,'[');
	$epos=strpos($name,']',$spos);
	if (($spos>=0)&&($epos>0))
	{
		$sub=substr($name,0,$spos);
		$remains=substr($name,$epos+1);
		$name=substr($name,$spos+1,$epos).$remains;
		if (strlen($sub)==0)
		{
			$next=array();
			$array[]=&$next;
		}
		else if (isset($array[$sub]))
		{
			$next=&$array[$sub];
		}
		else
		{
			$next=array();
			$array[$sub]=&$next;
		}
		extractVariable($next,$name,$value);
	}
	else
	{
		$array[$name]=cleanVariable($value);
	}
}

function decodeQuery($query)
{
  $log=LoggerManager::getLogger('swim.urls.decode');
	$result=array();
	$vars=explode('&',$query);
	foreach ($vars as $var)
	{
		$pos=strpos($var,'=');
		if ($pos>0)
		{
			$name=urldecode(substr($var,0,$pos));
			$value=urldecode(substr($var,$pos+1));
      if (get_magic_quotes_gpc())
      {
        $value=stripslashes($value);
      }
			extractVariable($result,$name,$value);
		}
	}
  return $result;
}

function readURLEncodedPost($in)
{
	$line='';
  while (!feof($in))
  {
    $data=fread($in,1024);
	  $line.=$data;
	}
 	return decodeQuery($line);
}

function readMultipartPost($in)
{
	$log=LoggerManager::getLogger('swim.urls');
	$query=array();
	while (!feof($in))
	{
		$line=fgets($in);
		$matches=array();
		if (preg_match('/Content-disposition:\s*form-data;.*name="([^"]*)"/',$line,$matches))
		{
			$orig=$matches[1];
			$log->warn('Read post param '.$orig);
			$name=$orig;
			$name=str_replace('.','_',$name);
			if (isset($_POST[$matches]))
			{
				$query[$orig]=$_POST[$name];
			}
		}
	}
	return $query;
}

function decodePostQuery()
{
	$log=LoggerManager::getLogger('swim.urls');
	$query=$_POST;
	if (isset($_SERVER['CONTENT_TYPE']))
	{
		$ct=$_SERVER['CONTENT_TYPE'];
		$pos=strpos($ct,';');
		if ($pos>0)
		{
			$ct=substr($ct,0,$pos);
		}
	  $in=@fopen('php://input','rb');
	  if ($in!==false)
	  {
	  	if ($ct=='application/x-www-form-urlencoded')
	  	{
	  		$query=readURLEncodedPost($in);
	  	}
	  	else if ($ct=='multipart/form-data')
	  	{
        $query=array();
        foreach ($_POST as $name => $value)
        {
          if (get_magic_quotes_gpc())
          {
            $value=stripslashes($value);
          }
          extractVariable($query,$name,$value);
        }
	  	}
			fclose($in);
	  }
	}
	else
	{
		$log->warn('No Content Type. Logging $_SERVER');
		$log->warn($_SERVER);
	}
	return $query;
}

function redirect($request)
{
  $protocol='http';
  if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
    $protocol='https';

  if ($request instanceof Request)
  {
  	$url=$request->encode();
  }
  else
  {      
    $url=$request;
  }
  if (strpos($url,'://')===false)
  {
    $url=$protocol.'://'.$_SERVER['HTTP_HOST'].$url;
  }
	header('Location: '.$url);
	SwimEngine::shutdown();
}

function checkSecurity($request, $required, $allowed)
{
  global $_PREFS;
  
  $log=LoggerManager::getLogger('swim.security');
  
  $allowed=($allowed && $_PREFS->getPref('security.sslenabled'));
  
  if ($log->isDebugEnabled())
  {
    $text = 'Check security - allowed: ';
    if ($allowed)
      $text.='true';
    else
      $text.='false';
    $text.=', required: ';
    if ($required)
      $text.='true';
    else
      $text.='false';
    $log->debug($text);
  }
  
  if (($request->getProtocol()=='https')&&(!$allowed))
  {
    $log->debug('SSL requested but not allowed');
    $request->setProtocol('http');
    redirect($request);
  }
  if (($request->getProtocol()=='http')&&($required)&&($allowed))
  {
    $log->debug('SSL required but not requested');
    $request->setProtocol('https');
    redirect($request);
  }
}

class Request
{
  private $log;

  private $protocol;
  private $xml = false;

  var $resObject = null;

  private $method = '';
  private $path = '';
  private $query = array();
  private $nested = null;
  private $data = array();
	
  function Request($clone=null)
  {
    global $_PREFS;
    
    $this->log=LoggerManager::getLogger('swim.request');
    if ($clone!==null)
    {
      $this->protocol=$clone->protocol;
      $this->path=$clone->path;
      $this->resObject=$clone->resObject;
      $this->method=$clone->method;
      $this->query=$clone->query;
      $this->nested=$clone->nested;
    }
    else
    {
      $this->protocol='http';
      if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on')&&($_PREFS->getPref('security.sslenabled')))
        $this->protocol='https';
    }
  }
  
  public function getProtocol()
  {
    return $this->protocol;
  }
  
  public function setProtocol($value)
  {
    $this->protocol = $value;
  }
  
  public function getNested()
  {
    return $this->nested;
  }
  
  public function setNested($value)
  {
    $this->nested = $value;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function setPath($value)
  {
    $this->path = $value;
  }
  
  public function getMethod()
  {
    return $this->method;
  }
  
  public function setMethod($value)
  {
    $this->method = $value;
  }
  
  public function clearQuery()
  {
    $this->query = array();
  }
  
  public function hasQueryVar($var)
  {
    return isset($this->query[$var]);
  }
  
  public function getQueryVar($var)
  {
    return $this->query[$var];
  }
  
  public function setQueryVar($var, $value)
  {
    $this->query[$var] = $value;
  }
  
  public function getQuery()
  {
    return $this->query;
  }
  
  public function setQuery($value)
  {
    $this->query = $value;
  }
  
  function isXML()
  {
    return $this->xml;
  }
  
  function setXML($value)
  {
    $this->xml=$value;
  }
  
  public function __get($name)
  {
    if ($name == 'resource')
    {
      if ($this->resObject !== FALSE)
        return $this->resObject;
      return null;
    }
    else if ($name == 'resourcePath')
    {
      return $this->path;
    }
    else
    {
      $this->log->warntrace('Attempt to get unknown property - '.$name);
    }
  }
  
  public function __set($name, $value)
  {
    if ($name == 'resource')
    {
      if ($value instanceof Resource)
      {
        $this->resObject = $value;
        $this->path = $value->getPath();
      }
      else if ($value === null)
      {
        $this->resObject = null;
        $this->path = '';
      }
      else
      {
        $this->resObject = FALSE;
        $this->path = $value;
        Resource::decodeResource($this);
      }
    }
    else if ($name == 'resourcePath')
    {
      $this->path = $value;
      if ($value == '')
        $this->resObject = null;
      else
        $this->resObject = FALSE;
    }
    else
    {
      $this->log->warnTrace('Attempt to set unknown property - '.$name);
    }
  }
  
  function isXHTML()
  {
    if ((isset($_SERVER['HTTP_ACCEPT']))&&(strpos($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')!==false))
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
	function encodePath($humanreadable = true)
	{
		global $_PREFS;
		
    if ($this->resObject === FALSE)
      Resource::decodeResource($this);
      
    $host='';
    $thisprotocol=$this->protocol;
    if (!$_PREFS->getPref('security.sslenabled'))
      $thisprotocol='http';
      
    $protocol='http';
    if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
      $protocol='https';

    if ($this->resource !== null)
    {
      if (!$this->resource->prefs->getPref('security.sslallowed'))
        $thisprotocol = 'http';
      else if ($this->resource->prefs->getPref('security.sslrequired'))
        $thisprotocol = 'https';
    }
    
    if ($thisprotocol!=$protocol)
    {
      $host=$thisprotocol.'://'.$_SERVER['HTTP_HOST'];
    }
    
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
      $url=null;
      if (($humanreadable) && ($this->method=='view') && ($this->resource !== null))
      {
        $url = $this->resource->getViewPath($this);
        $this->log->warn('Path for '.$this->resource->getPath().' is '.$url);
      }
      if ($url === null)
      {
  	  	$url='/'.$this->method;
        $res = $this->resourcePath;
        if (strlen($res)>0)
  	  	{
  	  	    
  	  		if (substr($res,0,1)!='/')
  		  		$url.='/'.$res;
  		  	else
  		  		$url.=$res;
  	  	}
  	  }
	    return $host.$_PREFS->getPref('url.pagegen').urlPathEncode($url);
	  }
	  else
	  {
	    return $host.$_PREFS->getPref('url.pagegen');
	  }
	}
	
	function makeAllVars()
	{
		global $_PREFS;
		
		$newquery=$this->query;
		$newquery[$_PREFS->getPref('url.methodvar')]=$this->method;
 		$newquery[$_PREFS->getPref('url.resourcevar')]=$this->resourcePath;
		if (isset($this->nested))
		{
			$newquery[$_PREFS->getPref('url.nestedvar')]=encodeQuery($this->nested->makeAllVars());
		}
		return $newquery;
	}
	
	function makeVars()
	{
		global $_PREFS;
		
		$newquery=$this->query;
		if (isset($this->nested))
		{
			$newquery[$_PREFS->getPref('url.nestedvar')]=encodeQuery($this->nested->makeAllVars());
		}
	  if ($_PREFS->getPref('url.encoding')!='path')
	  {
			$newquery[$_PREFS->getPref('url.methodvar')]=$this->method;
  		$newquery[$_PREFS->getPref('url.resourcevar')]=$this->resourcePath;
	  }
	  return $newquery;
	}
	
	function getFormVars()
	{
		$vars = $this->makeVars();
		$text='';
		foreach ($vars as $key => $value)
		{
			$text.='<input type="hidden" name="'.$key.'" value="'.htmlentities($value).'" />'."\n";
		}
		return $text;
	}
	
	function encode($humanreadable = true)
	{
		$url=$this->encodePath($humanreadable);
		$vars=$this->makeVars();
		if (count($vars)>0)
		{
			$url.='?'.encodeQuery($vars);
		}
		return $url;
	}
	
	static function decodeCurrentRequest()
	{
		global $_PREFS;
		
		$log=LoggerManager::getLogger('swim.request');
    $path='';
		$query=decodeQuery($_SERVER['QUERY_STRING']);
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	  	$pathvar=$_PREFS->getPref('url.pathenvvar');
			if (isset($_SERVER[$pathvar]))
			{
				$path=$_SERVER[$pathvar];
        $log->debug('Decoding path '.$path);
        if (strpos($path,'?'))
        {
          $path=substr($path,0,strpos($path,'?'));
          $log->debug('Removed query '.$path);
        }
				$pathstart=$_PREFS->getPref('url.pathstart','');
				if (substr($path,0,strlen($pathstart))==$pathstart)
				{
					$path=substr($path,strlen($pathstart));
          if ($path=='/index.php')
          {
            $path='';
          }
          $log->debug('Reduced to path '.$path);
				}
				else
				{
					$path='';
				}
			}
	  }
		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			$postvars = decodePostQuery();
			$query=array_merge($query,$postvars);
		}
    $protocol='http';
    if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
      $protocol='https';
		return Request::decode($path,$query,$protocol);
	}
	
	static function decode($path,$query,$protocol = 'http')
	{
		global $_PREFS;

		$request = new Request();
    $request->setProtocol($protocol);
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	  	// Site is setup to use path info to choose page

      $path = urlPathDecode($path);
      
	    if ((isset($path))&&(strlen($path)>0))
	    {
	    	while ((strlen($path)>0)&&($path[0]=='/'))
	    		$path=substr($path,1);
	    	
	    	if (strlen($path)>0)
	    	{
		    	$pos=strpos($path,'/');
		    	if ($pos===false)
		    		$request->setMethod($path);
		    	else
		    	{
		    		$request->setMethod(substr($path,0,$pos));
		    		if (strlen($path)>$pos+1)
		    		{
			    		$request->setPath(substr($path,$pos+1));
			    	}
		    	}
		    }
	    }
	  }
	  
    if (isset($query[$_PREFS->getPref('url.methodvar')]))
    {
      $request->setMethod($query[$_PREFS->getPref('url.methodvar')]);
      unset($query[$_PREFS->getPref('url.methodvar')]);
    }
    
    if (isset($query[$_PREFS->getPref('url.resourcevar')]))
    {
      $request->setPath($query[$_PREFS->getPref('url.resourcevar')]);
      unset($query[$_PREFS->getPref('url.resourcevar')]);
    }
    
    if (isset($query[$_PREFS->getPref('url.nestedvar')]))
    {
      $request->setNested(Request::decode('',decodeQuery($query[$_PREFS->getPref('url.nestedvar')])));
      unset($query[$_PREFS->getPref('url.nestedvar')]);
    }
    
    $request->setQuery($query);
    
    if ($request->getMethod() == '')
    	$request->setMethod($_PREFS->getPref('method.default'));
    
    if ((strlen($request->getPath())==0)&&($_PREFS->isPrefSet('method.'.$request->method.'.defaultresource')))
    	$request->setPath($_PREFS->getPref('method.'.$request->method.'.defaultresource'));

	  return $request;
	}
}


?>