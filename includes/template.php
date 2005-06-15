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
	var $prefs;
	var $parsing = false;
	var $curPage;
	
	function Template($name)
	{
		global $_PREFS;
		
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->dir=getCurrentResource($this->prefs->getPref('storage.templates').'/'.$name);
		
		// If the template doesnt exist then there is a problem
		if ($this->dir===false)
		{
			trigger_error('This website has not been properly configured.');
			exit;
		}
		
		// If the template has prefs then load them
		if (is_readable($this->dir.'/template.conf'))
		{
			$this->prefs->loadPreferences($this->dir.'/template.conf','template');
		}
	}
	
	function generateURL(&$parser,$tag,$attrs,$text)
	{
	}
	
	function displayBlock(&$parser,$tag,$attrs,$text)
	{
		$page=&$parser->data['page'];
		$block=$page->getBlock($attrs['id']);
		return $block->display($parser,$attrs,$text);
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
		else if ($tag=='url')
		{
			return $this->generateURL($parser,$tag,$attrs,$text);
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
		
		// Parse the template and display
		$parser = new TemplateParser();
		$parser->data=array('page'=>&$page,'request'=>&$request,'mode'=>$mode);
		$parser->addObserver('block',$this);
		$parser->addObserver('var',$this);
		$parser->addObserver('url',$this);
		
		ob_start();
		$parser->parseFile($this->dir.'/'.$file);
		ob_end_flush();
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