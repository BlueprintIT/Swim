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
	var $version;
	
	function Template($container,$id,$version=false)
	{
		global $_PREFS;
		
		$this->container=$container;
		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->resource=$this->prefs->getPref('storage.templates').'/'.$id;
		if ($version==false)
		{
			$version=getCurrentVersion($this->resource);
		}
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
		if (isset($data['block']))
		{
			$url=$data['request']->resource.'/'.$data['block']->id.'/'.$url;
		}
		else
		{
			$url='template/'.$this->id.'/'.$url;
		}
		$request = new Request();
		$request->method='view';
		$request->resource=$url;
		return $request->encode();
	}
	
	function generateURL(&$data,$url)
	{
		if ($url[0]=='/')
		{
			return $url;
		}
		else
		{
			return $this->generateRelativeURL($data,$url);
		}
	}
	
	function displayElement(&$parser,$tag,$attrs,$text='',$shortallowed=true)
	{
		if (!($parser->data['request']->isXML()))
		{
			$shortallowed=false;
		}
		print('<'.$tag);
		foreach ($attrs as $name => $value)
		{
			print(' '.$name.'="'.$value.'"');
		}
		if (($shortallowed)&&(strlen($text)==0))
		{
			print(' />');
		}
		else
		{
			print('>'.$text.'</'.$tag.'>');
		}
	}
	
	function displayStylesheet(&$parser,$tag,$attrs,$text)
	{
		$this->displayElement($parser,'link',array('rel'=>'stylesheet','href'=>$this->generateURL($parser->data,$attrs['src'])));
	}
	
	function displayBlock(&$parser,$tag,$attrs,$text)
	{
		$page=&$parser->data['page'];
		$block=$page->getBlock($attrs['id']);
		$parser->data['modified']=max($parser->data['modified'],$block->getModifiedDate());
		$parser->data['block']=&$block;
		$result=$block->display($parser,$attrs,$text);
		unset($parser->data['block']);
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

?>