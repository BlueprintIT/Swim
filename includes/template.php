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

class Template extends Resource
{
	var $parsing = false;
	var $curPage;
	var $log;
	
	function Template(&$container,$id,$version)
	{
		$this->Resource($container,$id,$version);
		
		$this->log=&LoggerManager::getLogger('swim.template');
	}
	
	function getPath()
	{
		return $this->container->getPath().'/template/'.$this->id;
	}
	
	function getFileModifiedDate($file)
	{
		$f=$this->getDir().'/'.$this->prefs->getPref($file);
		if (is_readable($f))
		{
			$stats=stat($f);
			return $stats['mtime'];
		}
		return false;
	}
	
	function getModifiedDate()
	{
		if (isset($this->modified))
		{
			return $this->modified;
		}
		
		$modified=parent::getModifiedDate();
		
		$stat=$this->getFileModifiedDate('template.file.html');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$stat=$this->getFileModifiedDate('template.file.xml');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$stat=$this->getFileModifiedDate('template.admin.html');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$stat=$this->getFileModifiedDate('template.admin.xml');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$this->modified=$modified;
		return $modified;
	}
	
	function &generateRelativeURL(&$data,$url,$method)
	{
		$request = new Request();
		$request->method=$method;
		if (strlen($url)==0)
		{
			$url=$data['request']->resource;
			$request->query=$data['request']->query;
		}
		else if (substr($url,0,6)=='block/')
		{
			$url=$data['block']->getPath().substr($url,5);
			$request->query['version']=$data['block']->version;
		}
		else if (substr($url,0,5)=='page/')
		{
			$url=$data['page']->getPath().substr($url,4);
			//$request->query['version']=$data['page']->version;
		}
		else if (substr($url,0,9)=='template/')
		{
			$url=$this->getPath().substr($url,8);
			$request->query['version']=$this->version;
		}
		else if (isset($data['block']))
		{
			$url=$data['block']->getPath().'/file/'.$url;
			$request->query['version']=$data['block']->version;
		}
		else
		{
			$url=$this->getPath().'/file/'.$url;
			$request->query['version']=$this->version;
		}
		$request->resource=$url;
		return $request;
	}
	
	function &generateRequest(&$data,$url,$method)
	{
		if (strlen($url)==0)
		{
			$request = new Request();
			$request->method='view';
			$request->resource=$data['request']->resource;
		}
		else if ($url[0]=='/')
		{
			$request = new Request();
			$request->method=$method;
			$request->resource=substr($url,1);
		}
		else
		{
		  $request=&$this->generateRelativeURL($data,$url,$method);
		}
		return $request;
	}
	
	function generateURL(&$data,$url,$method='view')
	{
	  $request=&$this->generateRequest($data,$url,$method);
	  return $request->encode();
	}
	
	function displayDate(&$parser,$tag,$attrs,$text)
	{
		if (isset($attrs['source']))
		{
			if ($attrs['source']=='block')
			{
				$time=$data['block']->getModifiedDate();
			}
			else if ($attrs['source']=='template')
			{
				$time=$this->getModifiedDate();
			}
			else if ($attrs['source']=='page')
			{
				$time=$data['page']->getModifiedDate();
			}
			else if ($attrs['source']=='resource')
			{
				$time=$data['page']->getTotalModifiedDate();
			}
		}
		else
		{
			$time=time();
		}
		if (isset($attrs['format']))
		{
			$format=$attrs['format'];
		}
		else if ($tag=='date')
		{
			$format='l jS F Y';
		}
		else if ($tag=='time')
		{
			$format='g:ia (T)';
		}
		print(date($format,$time));
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
		print('<comment><object type="application/x-java-applet;version=1.4" height="'.$height.'" width="'.$width.'">'."\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'code','value'=>$class),'',false); print("\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'codebase','value'=>$codebase),'',false); print("\n\t\t");
		$this->displayElement($parser,'param',array('name'=>'archive','value'=>$attrs['classpath']),'',false); print("\n\t");
		print($text);
		print('</object></comment>'."\n");
		print('</object>');
	}
	
	function displayEditLink(&$parser,$tag,$attrs,$text)
	{
		$request = new Request();
		$request->nested=&$parser->data['request'];
		if (isset($attrs['method']))
		{
			$request->method=$attrs['method'];
			unset($attrs['method']);
		}
		else
		{
			$request->method='edit';
		}
		if (isset($attrs['block']))
		{
			$block=&$parser->data['page']->getReferencedBlock($attrs['block']);
			$request->resource=$block->getPath();
			unset($attrs['block']);
			$request->query['version']=$block->version;
		}
		else
		{
			$page=&$parser->data['page'];
			$request->resource=$page->getPath();
			$request->query['version']=$page->version;
		}
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
		$block=$page->getReferencedBlock($attrs['id']);
		if ($block!=null)
		{
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
			$method=$parser->data['request']->method;
		}
		$request=&$this->generateRequest($parser->data,$attrs['href'],$method);
		if (isset($attrs['nest']))
		{
			$request->nested=&$parser->data['request'];
			unset($attrs['nest']);
		}
		if (isset($attrs['template']))
		{
			$request->query['template']=$attrs['template'];
			unset($attrs['template']);
		}
		$attrs['href']=$request->encode();
		$this->displayElement($parser,'a',$attrs,$text);
	}
	
	function displayIf(&$parser,$tag,$attrs,$text)
	{
		$result=false;
		if (isset($attrs['hasVar']))
		{
			$name=$attrs['hasVar'];
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
				$value=$page->prefs->getPref($name);
				$result=strlen($value)>0;
			}
		}
		else if (isset($attrs['hasBlock']))
		{
			$block=&$parser->data['page']->getBlock($attrs['hasBlock']);
			if ($block!==false)
			{
				$result=true;
			}
		}
		if ($result)
		{
			print($text);
		}
		return true;
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
		else if ($tag=='img')
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
		else if ($tag=='if')
		{
			return $this->displayIf($parser,$tag,$attrs,$text);
		}
		else if (($tag=='date')||($tag=='time'))
		{
			return $this->displayDate($parser,$tag,$attrs,$text);
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
						
		// Parse the template and display
		$parser = new TemplateParser();
		$parser->addEmptyTag("img");
		$parser->data=array('page'=>&$page,'request'=>&$request,'mode'=>$mode);
		$parser->addObserver('block',$this);
		$parser->addObserver('var',$this);
		$parser->addObserver('stylesheet',$this);
		$parser->addObserver('script',$this);
		$parser->addObserver('applet',$this);
		$parser->addObserver('image',$this);
		$parser->addObserver('img',$this);
		$parser->addObserver('anchor',$this);
		$parser->addObserver('editlink',$this);
		$parser->addObserver('if',$this);
		$parser->addObserver('date',$this);
		$parser->addObserver('time',$this);
		
		$this->lockRead();
		ob_start();
		$parser->parseFile($this->dir.'/'.$file);
		ob_end_flush();
		$this->unlock();
	}
	
	function displayAdmin(&$request,&$page)
	{
		$this->internalDisplay($request,$page,'admin','template.admin.xml','template.admin.html');
	}
	
	function display(&$request,&$page)
	{
		$this->internalDisplay($request,$page,'normal','template.file.xml','template.file.html');
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
		$newtemplates=&$container->getResources('template');
		$templates=array_merge($templates,$newtemplates);
	}
	return $templates;
}

?>