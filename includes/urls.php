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
	
	function decodeTemplateResource($args,&$result,$version=false)
	{
		global $_PREFS;
		
		$template=&loadTemplate($args[0],$version);
		if ($template!==false)
		{
			$result->type='template';
			$result->template=$args[1];
			$result->_template=&$template;
			$result->resource=$_PREFS->getPref('storage.templates').'/'.$args[1];
			if (count($args)>=3)
			{
				$result->path=implode('/',array_slice($args,2));
			}
			return true;
		}
		return false;
	}
	
	function decodeBlockResource($container,$args,&$result,$version=false)
	{
		global $_PREFS;
		
		if (($_PREFS->isPrefSet('storage.blocks.'.$container))&&(count($args)>0))
		{
			$result->resource=$_PREFS->getPref('storage.blocks.'.$container).'/'.$args[0];
			$block=&loadBlock($container,$args[0],$version);
			if ($block!==false)
			{
				$result->type='block';
				$result->block=$args[0];
				$result->_block=&$block;
				if (count($args)>1)
				{
					$result->path=implode('/',array_slice($args,1));
				}
				return true;
			}
		}
		return false;
	}
	
	function decodePageResource($container,$args,&$result,$version=false)
	{
		$log=LoggerManager::getLogger('swim.resource');
		$log->debug('Testing container '.$container);
		$log->debug('Argument has '.count($args).' items');
		if (count($args)>0)
		{
			$page=&loadPage($container,$args[0],$version);
			if ($page!==false)
			{
				$log->debug('Found valid page');
				$result->_page=&$page;
				$result->version=$page->version;
				$result->type='page';
				$result->page=$args[0];
				if (count($args)>=2)
				{
					$log->debug('Checking for block '.$args[1]);
					$block=&$result->_page->getBlock($args[1]);
					if ($block!==null)
					{
						$result->type='block';
						$result->block=$args[1];
						$result->_block=&$block;
						if (count($args)>=3)
						{
							$result->path=implode('/',array_slice($args,2));
						}
						return true;
					}
					$result->path=implode('/',array_slice($args,1));
				}
				return true;
			}
		}
		return false;
	}
	
	function &decodeResource($resource)
	{
		global $_PREFS;
		
		$log=LoggerManager::getLogger('swim.resource');
		
		$log->debug('Decoding '.$resource);
		
		$parts=explode('/',$resource);
		if (count($parts)==0)
		{
			$log->info('No resource to decode');
			return false;
		}
		$result = new Resource();
		if (($parts[0]=='files')&&(count($parts)>2))
		{
			$result->type='file';
			$result->container=$parts[1];
			$result->path=implode('/',array_slice($parts,2));
			return $result;
		}
		if (($parts[0]=='template')&&(count($parts)>=2))
		{
			$log->debug('Template resource');
			if (Resource::decodeTemplateResource($parts,$result))
				return $result;
		}
		if (($parts[0]=='block')&&(count($parts)>=2))
		{
			$log->debug('Block resource');
			if (Resource::decodeBlockResource($parts[1],array_slice($parts,2),$result))
				return $result;
				
			if (Resource::decodeBlockResource('global',array_slice($parts,1),$result))
				return $result;
		}
		$log->debug('Assuming page resource');
		if (Resource::decodePageResource($parts[0],array_slice($parts,1),$result))
			return $result;
			
		if (Resource::decodePageResource('global',$parts,$result))
			return $result;
			
		return false;
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