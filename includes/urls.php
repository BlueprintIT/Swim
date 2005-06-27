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

class Resource
{
	var $type;
	var $path;
	var $template;
	var $page;
	var $block;
	var $lock;
	var $filedir;
	
	var $_block;
	var $_page;
	var $_template;
	
	function isFile()
	{
		return isset($this->path);
	}
	
	function isPage()
	{
		return (($this->type=='page')&&(!isset($this->path)));
	}
	
	function isBlock()
	{
		return (($this->type=='block')&&(!isset($this->path)));
	}
	
	function isTemplate()
	{
		return (($this->type=='template')&&(!isset($this->path)));
	}
	
	function lockRead()
	{
		if ($this->isBlock())
		{
			$res=$this->getBlock();
			$res->lockRead();
		}
		else if ($this->isPage())
		{
			$res=$this->getPage();
			$res->lockRead();
		}
		else if ($this->isTemplate())
		{
			$res=$this->getTemplate();
			$res->lockRead();
		}
		else
		{
			$path=dirname($this->path);
			if ($path=='.')
			{
				$path=$this->getDir();
			}
			else
			{
				$path=$this->getDir().'/'.$path;
			}
			$this->lock=lockResourceRead($path);
		}
	}
	
	function lockWrite()
	{
		if ($this->isBlock())
		{
			$res=$this->getBlock();
			$res->lockWrite();
		}
		else if ($this->isPage())
		{
			$res=$this->getPage();
			$res->lockWrite();
		}
		else if ($this->isTemplate())
		{
			$res=$this->getTemplate();
			$res->lockWrite();
		}
		else
		{
			$path=dirname($this->path);
			if ($path=='.')
			{
				$path=$this->getDir();
			}
			else
			{
				$path=$this->getDir().'/'.$path;
			}
			$this->lock=lockResourceWrite($path);
		}
	}
	
	function unlock()
	{
		if ($this->isBlock())
		{
			$res=$this->getBlock();
			$res->unlock();
		}
		else if ($this->isPage())
		{
			$res=$this->getPage();
			$res->unlock();
		}
		else if ($this->isTemplate())
		{
			$res=$this->getTemplate();
			$res->unlock();
		}
		else
		{
			unlockResource($this->lock);
		}
	}
	
	function getResource()
	{
		if (isset($this->_block))
		{
			return $this->_block->getResource();
		}
		if (isset($this->_page))
		{
			return $this->_page->getResource();
		}
		if (isset($this->_template))
		{
			return $this->_template->getResource();
		}
		if (isset($this->filedir))
		{
			return $this->filedir;
		}
	}
	
	function getDir()
	{
		if (isset($this->_block))
		{
			return $this->_block->getDir();
		}
		if (isset($this->_page))
		{
			return $this->_page->getDir();
		}
		if (isset($this->_template))
		{
			return $this->_template->getDir();
		}
		if (isset($this->filedir))
		{
			return $this->filedir;
		}
	}
	
	function &getBlock()
	{
		return $this->_block;
	}
	
	function &getPage()
	{
		return $this->_page;
	}
	
	function &getTemplate()
	{
		return $this->_template;
	}
	
	function &decodeResource($request)
	{
		global $_PREFS;
		
		$log=&LoggerManager::getLogger('swim.resource');
		
		if (is_object($request))
		{
			$resource=$request->resource;
			if (isset($request->query['version']))
			{
				$version=$request->query['version'];
			}
			else
			{
				$version=false;
			}
		}
		else
		{
			$resource=$request;
			$version=false;
		}
		
		$log->debug('Decoding '.$resource);
		
		if (strlen($resource)==0)
		{
			$log->info('No resource to decode');
			return false;
		}

		$result = new Resource();
		$parts = explode('/',$resource);
		if (count($parts)<3)
			return false;
			
		list($container,$result->type)=$parts;
		
		$container=&getContainer($container);

		if ($result->type=='file')
		{
			$log->debug('Found file');
			$result->filedir=&$container->getFileDir();
			$result->path=implode('/',array_slice($parts,2));
		}
		else
		{
			$id=$parts[2];
			if (count($parts)>3)
				$result->path=implode('/',array_slice($parts,3));

			if ($result->type=='page')
			{
				$log->debug('Found page: '.$id);
				$result->_page=&$container->getPage($id,$version);
				if ($result->_page==null)
				{
					$log->warn('Invalid page');
					return false;
				}
				$result->version=$result->_page->version;
				if (count($parts)>3)
				{
					$log->debug('Testing for block '.$parts[3]);
					if ($result->_page->isBlock($parts[3]))
					{
						$log->debug('Found page block '.$parts[3]);
						$result->_block=&$result->_page->getBlock($parts[3]);
						$result->type='block';
						if (count($parts)>4)
							$result->path=implode('/',array_slice($parts,4));
						else
							unset($this->path);
					}
				}
			}
			else if ($result->type=='template')
			{
				$log->debug('Found template: '.$id);
				$result->_template=&$container->getTemplate($id,$version);
				if ($result->_template==null)
				{
					$log->warn('Invalid template');
					return false;
				}
				$result->version=$result->_template->version;
			}
			else if ($result->type=='block')
			{
				$log->debug('Found block: '.$id);
				$result->_block=&$container->getBlock($id,$version);
				if ($result->_block==null)
				{
					$log->warn('Invalid block');
					return false;
				}
				$result->version=$result->_block->version;
			}
			else
			{
				return false;
			}
		}
		
		return $result;
	}
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

function decodeQuery($query)
{
	$result=array();
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
	var $xml;
	
	function Request()
	{
		$this->log=&LoggerManager::getLogger('swim.request');
	}
	
	function isXML()
	{
		if (!isset($this->xml))
		{
			if (strpos($_SERVER['HTTP_ACCEPT'],'application/xhtml+xml')!==false)
			{
				$this->xml=true;
			}
			else
			{
				$this->xml=false;
			}
		}
		return $this->xml;
	}
	
	function setXML($value)
	{
		$this->xml=$value;
	}
	
	function encodePath()
	{
		global $_PREFS;
		
	  if ($_PREFS->getPref('url.encoding')=='path')
	  {
	  	$url=$_PREFS->getPref('url.pagegen').'/'.$this->method;
	  	if (isset($this->resource))
	  	{
	  		$url.='/'.$this->resource;
	  	}
	    return $url;
	  }
	  else
	  {
	    return $_PREFS->getPref('url.pagegen');
	  }
	}
	
	function makeAllVars()
	{
		global $_PREFS;
		
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
			$newquery[$_PREFS->getPref('url.nestedvar')]=encodeQuery($this->nested->makeAllVars());
		}
	  if ($_PREFS->getPref('url.encoding')!='path')
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
			$text.='<input type="hidden" name="'.$key.'" value="'.htmlentities($value).'" />'."\n";
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