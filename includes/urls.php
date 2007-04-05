<?

/*
 * Swim
 *
 * Url encoding and decoding functions
 *
 * Copyright Blueprint IT Ltd. 2007
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

function cleanTag($text,$tag)
{
  $text = preg_replace("/<".$tag.".*?".$tag.">/si", "", "$text");
  $text = preg_replace("/<".$tag.".*?>/si", "", "$text");
  return $text;
}

function cleanVariable($text)
{
	$text = cleanTag($text, 'script');
	$text = cleanTag($text, 'style');
	$text = cleanTag($text, 'link');
  return $text;
}

function buildArray(&$current, $parts, $pos, $value)
{
  $part = $parts[$pos];
  if ($pos == count($parts)-1)
  {
    if (strlen($part) == 0)
      $current[] = $value; //cleanVariable($value);
    else
      $current[$part] = $value; //cleanVariable($value);
  }
  else
  {
    if (strlen($part) == 0)
    {
      $next = array();
      $current[] = &$next;
    }
    else if (isset($current[$part]))
    {
      $next = &$current[$part];
    }
    else
    {
      $next = array();
      $current[$part] = &$next;
    }
    buildArray($next, $parts, $pos+1, $value);
  }
}

function extractVariable(&$array,$name,$value)
{
  $name = str_replace('].', '.', $name);
  $name = str_replace('][', '.', $name);
  $name = str_replace(']', '', $name);
  $name = str_replace('[', '.', $name);
  
  $parts = explode('.', $name);
  
  buildArray($array, $parts, 0, $value);
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
  private $modified;
	
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
      $this->modified=$clone->modified;
    }
    else
    {
      $this->modified = false;
      $this->protocol='http';
      if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on')&&($_PREFS->getPref('security.sslenabled')))
        $this->protocol='https';
    }
  }
  
  public function isModified()
  {
    return $this->modified;
  }
  
  public function setModified($value)
  {
    $this->modified = $value;
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
  	if (!isset($this->query[$var]))
  	{
  		$this->log->warntrace('Attempt to access unset query var '.$var);
  		return null;
  	}
  
    return $this->query[$var];
  }
  
  public function setQueryVar($var, $value)
  {
    $this->query[$var] = $value;
  }
  
  public function clearQueryVar($var)
  {
    unset($this->query[$var]);
  }
  
  public function setQueryVars($values)
  {
    foreach ($values as $key => $value)
      $this->query[$key]=$value;
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
    $this->log->warntrace('Attempt to get unknown property - '.$name);
  }
  
  public function __set($name, $value)
  {
    $this->log->warnTrace('Attempt to set unknown property - '.$name);
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

    if ($thisprotocol!=$protocol)
    {
      $host=$thisprotocol.'://'.$_SERVER['HTTP_HOST'];
    }
    
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	  	$url='/'.$this->method;
      if (strlen($this->path)>0)
	  	{
	  		if (substr($this->path,0,1)!='/')
		  		$url.='/'.$this->path;
		  	else
		  		$url.=$this->path;
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
 		$newquery[$_PREFS->getPref('url.resourcevar')]=$this->path;
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
  		$newquery[$_PREFS->getPref('url.resourcevar')]=$this->path;
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
	
	static function getCurrentPath()
	{
		global $_PREFS;
		
		$log=LoggerManager::getLogger('swim.request');

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
        return $path;
			}
			else
			{
				return '';
			}
		}
		return '';
	}
	
	static function decodeCurrentRequest()
	{
		global $_PREFS;
		
		$log=LoggerManager::getLogger('swim.request');

	  if ($_PREFS->getPref('url.encoding')=='path')
	  	$path = self::getCurrentPath();
	  else
	  	$path = '';

		$query=decodeQuery($_SERVER['QUERY_STRING']);
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
	
  static function decodeEncodedPath($path)
  {
    $pos = strpos($path, '?');
    if ($pos)
    {
      $query = decodeQuery(substr($path, $pos+1));
      $path=substr($path, 0, $pos);
    }
    else
      $query = array();
    
    return self::decode($path, $query);
  }
  
	static function decode($path,$query,$protocol = 'http')
	{
		global $_PREFS;

    $log=LoggerManager::getLogger('swim.request');

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
    
    if (($request->getMethod() == '') && ($request->getPath() == '') && ($_PREFS->isPrefSet('url.defaultpath')))
    {
    	$request->setMethod($_PREFS->getPref('url.defaultmethod'));
      $request->setPath($_PREFS->getPref('url.defaultpath'));
    }
    
    $log->debug('Decoded method '.$request->getMethod().' path '.$request->getPath());
    
	  return $request;
	}
}


?>