<?

/*
 * Swim
 *
 * The template class and related functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Template
{
	var $dir;
	var $resource;
	var $container;
	var $id;
	var $prefs;
	var $parsing = false;
	var $curPage;
	var $lock;
	var $log;
	var $version;
	
	function Template($container,$id,$version)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.template');
		
		$this->container=&$container;
		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->resource=$container->getTemplateResource($id);
		$this->version=$version;
		$this->dir=getResourceVersion($this->resource,$version);
		
		// If the template doesnt exist then there is a problem
		if ($this->dir===false)
		{
			trigger_error('This website has not been properly configured.');
			exit;
		}
		
		$this->lockRead();
		
		// If the template has prefs then load them
		if (is_readable($this->dir.'/template.conf'))
		{
			$this->prefs->loadPreferences($this->dir.'/template.conf','template');
		}
		
		$this->unlock();
	}
	
	function getDir()
	{
		return $this->dir;
	}
	
	function getResource()
	{
		return $this->resource;
	}
	
	function lockRead()
	{
		$this->lock=lockResourceRead($this->dir);
	}
	
	function lockWrite()
	{
		$this->lock=lockResourceWrite($this->dir);
	}
	
	function unlock()
	{
		unlockResource($this->lock);
	}
	
	function generateRelativeURL(&$data,$url)
	{
		if (substr($url,0,6)=='block/')
		{
			$url=$data['page']->container->id.'/page/'.$data['page']->id.'/'.$data['block']->id.substr($url,5);
		}
		else if (substr($url,0,5)=='page/')
		{
			$url=$data['page']->container->id.'/page/'.$data['page']->id.substr($url,4);
		}
		else if (substr($url,0,9)=='template/')
		{
			$url=$this->container->id.'/template/'.$this->id.substr($url,8);
		}
		return $url;
	}
	
	function &generateRequest(&$data,$url,$method)
	{
		$request = new Request();
		$request->method=$method;
		if ($url[0]=='/')
		{
			$request->resource=substr($url,1);
		}
		else
		{
		  $request->resource=$this->generateRelativeURL($data,$url);
		}
		return $request;
	}
	
	function generateURL(&$data,$url,$method='view')
	{
	  $request=&$this->generateRequest($data,$url,$method);
	  return $request->encode();
	}
	
	function displayElement(&$parser,$tag,$attrs,$text='',$closetag=true)
	{
		print('<'.$tag);
		foreach ($attrs as $name => $value)
		{
			print(' '.$name.'="'.$value.'"');
		}
		if ((strlen($text)==0)&&($parser->data['request']->isXML()))
		{
			print(' />');
		}
		else
		{
			print('>'.$text);
			if ($closetag)
				print('</'.$tag.'>');
		}
	}
	
	function displayApplet(&$parser,$tag,$attrs,$text)
	{
		$this->log->debug('Displaying applet');
		$width=$attrs['width'];
		$height=$attrs['height'];
		$class=$attrs['class'];
		$classpath=$attrs['classpath'];
		$codebase=$this->generateURL($parser,$attrs['codebase']);
		print('<object classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93" height="'.$height.'" width="'.$width.'"'."\n\t");
		print('codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0">'."\n\t");
		$this->displayElement($parser,'param',array('name'=>'type','value'=>'application/x-java-applet;version=1.4'),'',false); print("\n\t");
		$this->displayElement($parser,'param',array('name'=>'code','value'=>$class.'.class'),'',false); print("\n\t");
		$this->displayElement($parser,'param',array('name'=>'codebase','value'=>$codebase),'',false); print("\n\t");
		$this->displayElement($parser,'param',array('name'=>'archive','value'=>$attrs['classpath']),'',false); print("\n\t");
		print($text);
		print('<object type="application/x-java-applet;version=1.4" height="'.$height.'" width="'.$width.'">'."\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'code','value'=>$class),'',false); print("\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'codebase','value'=>$codebase),'',false); print("\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'archive','value'=>$attrs['classpath']),'',false); print("\n\t");
		print($text);
		print('</object>'."\n");
		print('</object>');
	}
	
	function displayEditLink(&$parser,$tag,$attrs,$text)
	{
		if (isset($attrs['method']))
		{
			$method=$attrs['method'];
			unset($attrs['method']);
		}
		else
		{
			$method='edit';
		}
		$block=&$parser->data['page']->getBlock($attrs['block']);
		if (is_a($block->container,'Page'))
		{
			$resource=$block->container->container->id.'/page/'.$block->container->id.'/'.$block->id;
		}
		else
		{
			$resource=$block->container->id.'/block/'.$block->id;
		}
		unset($attrs['block']);
		$request=&$this->generateRequest($parser->data,$resource,$method);
		$request->nested=$parser->data['request'];
		$attrs['href']=$request->encode();
		$this->displayElement($parser,'a',$attrs,$text);
	}
	
	function displayStylesheet(&$parser,$tag,$attrs,$text)
	{
		$this->displayElement($parser,'link',array('type'=>'text/css','rel'=>'stylesheet','href'=>$this->generateURL($parser->data,$attrs['src'])),'',false);
	}
	
	function displayScript(&$parser,$tag,$attrs,$text)
	{
		if (isset($attrs['src']))
		{
			$this->displayElement($parser,'script',array('type'=>'text/javascript','src'=>$this->generateURL($parser->data,$attrs['src'])));
		}
		else
		{
			$this->displayElement($parser, $tag, $attrs, $text);
		}
	}
	
	function displayImage(&$parser,$tag,$attrs,$text)
	{
		$attrs['src']=$this->generateURL($parser->data,$attrs['src']);
		$this->displayElement($parser,'img',$attrs,$text,false);
	}
	
	function displayBlock(&$parser,$tag,$attrs,$text)
	{
		$page=&$parser->data['page'];
		$block=$page->getBlock($attrs['id']);
		if ($block!=null)
		{
			$parser->data['modified']=max($parser->data['modified'],$block->getModifiedDate());
			$parser->data['block']=&$block;
			$result=$block->display($parser,$attrs,$text);
			unset($parser->data['block']);
		}
	}
	
	function displayAnchor(&$parser,$tag,$attrs,$text)
	{
		if (isset($attrs['method']))
		{
			$method=$attrs['method'];
			unset($attrs['method']);
		}
		else
		{
			$method='view';
		}
		$attrs['href']=$this->generateURL($parser->data,$attrs['href'],$method);
		$this->displayElement($parser,'a',$attrs,$text);
	}
	
	function displayVar(&$parser,$tag,$attrs,$text)
	{
		$name=$attrs['name'];
		if (isset($attrs['namespace']))
		{
			$name=$attrs['namespace'].'.'.$name;
		}
		else
		{
			if (strpos($name,'.')===false)
			{
				$name='page.variables.'.$name;
			}
		}
		$page=&$parser->data['page'];
		if ($page->prefs->isPrefSet($name))
		{
			print($page->prefs->getPref($name));
		}
		return true;
	}
	
	function observeTag(&$parser,$tag,$attrs,$text)
	{
		$this->log->debug('Observed '.$tag);
		if ($tag=='var')
		{
			return $this->displayVar($parser,$tag,$attrs,$text);
		}
		else if ($tag=='block')
		{
			return $this->displayBlock($parser,$tag,$attrs,$text);
		}
		else if ($tag=='stylesheet')
		{
			return $this->displayStylesheet($parser,$tag,$attrs,$text);
		}
		else if ($tag=='script')
		{
			return $this->displayScript($parser,$tag,$attrs,$text);
		}
		else if ($tag=='applet')
		{
			return $this->displayApplet($parser,$tag,$attrs,$text);
		}
		else if ($tag=='image')
		{
			return $this->displayImage($parser,$tag,$attrs,$text);
		}
		else if ($tag=='anchor')
		{
			return $this->displayAnchor($parser,$tag,$attrs,$text);
		}
		else if ($tag=='editlink')
		{
			return $this->displayEditLink($parser,$tag,$attrs,$text);
		}
		else
		{
			return false;
		}
	}
	
	function internalDisplay(&$request,&$page,$mode,$xmlpref,$htmlpref)
	{
		if ($request->isXML())
		{
			$file=$this->prefs->getPref($xmlpref);
			if (!is_readable($this->dir.'/'.$file))
			{
				$request->setXML(false);
				$file=$this->prefs->getPref($htmlpref);
			}
		}
		else
		{
			$file=$this->prefs->getPref($htmlpref);
		}
		
		if ($request->isXML())
		{
			setContentType('application/xhtml+xml');
		}
		else
		{
			setContentType('text/html');
		}
		
		$stats=stat($this->dir.'/'.$file);
		$modified=$stats['mtime'];
		if (is_readable($this->dir.'/template.conf'))
		{
			$stats=stat($this->dir.'/template.conf');
			$modified=max($modified,$stats['mtime']);
		}
		$modified=max($modified,$page->getModifiedDate());
		
		// Parse the template and display
		$parser = new TemplateParser();
		$parser->data=array('page'=>&$page,'request'=>&$request,'mode'=>$mode,'modified'=>$modified);
		$parser->addObserver('block',$this);
		$parser->addObserver('var',$this);
		$parser->addObserver('stylesheet',$this);
		$parser->addObserver('script',$this);
		$parser->addObserver('applet',$this);
		$parser->addObserver('image',$this);
		$parser->addObserver('anchor',$this);
		$parser->addObserver('editlink',$this);
		
		$this->lockRead();
		ob_start();
		$parser->parseFile($this->dir.'/'.$file);
		setModifiedDate($parser->data['modified']);
		ob_end_flush();
		$this->unlock();
	}
	
	function displayAdmin(&$request,&$page)
	{
		$this->internalDisplay($request,$page,'admin','template.adminxml','template.adminhtml');
	}
	
	function display(&$request,&$page)
	{
		$this->internalDisplay($request,$page,'normal','template.xmlfile','template.htmlfile');
	}
}

function &getAllTemplates()
{
	global $_PREFS;
	
	$templates=array();
	$containers=&getAllContainers();
	foreach(array_keys($containers) as $id)
	{
		$container=&$containers[$id];
		$newtemplates=&$container->getTemplates();
		$templates=array_merge($templates,$newtemplates);
	}
	return $templates;
}

?>