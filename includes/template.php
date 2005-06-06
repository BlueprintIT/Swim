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
	var $file;
	var $admin;
	var $prefs;
	var $parsing = false;
	var $curPage;
	
	function Template($name)
	{
		global $_PREFS;
		
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		
		$this->dir=getCurrentResource($this->prefs->getPref("storage.templates")."/".$name);
		
		// If the template doesnt exist then there is a problem
		if ($this->dir===false)
		{
			trigger_error("This website has not been properly configured.");
			exit;
		}
		
		// If the template has prefs then load them
		if (is_readable($this->dir."/template.conf"))
		{
			$this->prefs->loadPreferences($this->dir."/template.conf","template");
		}
		
		// Find the template file name or use the default
		if ($this->prefs->isPrefSet("template.file"))
		{
			$this->file=$this->prefs->getPref("template.file");
		}
		else
		{
			$this->file=$this->prefs->getPref("templates.defaultname");
		}
		
		// Find the template admin name or use the default
		if ($this->prefs->isPrefSet("template.admin"))
		{
			$this->admin=$this->prefs->getPref("template.admin");
		}
		else
		{
			$this->admin=$this->prefs->getPref("templates.adminname");
		}
		
		// If the file doesnt exist then we have a problem with the template.
		if (!is_readable($this->dir."/".$this->file))
		{
			trigger_error($name." template is invalid.");
			exit;
		}
	}
	
	function displayBlock(&$request,&$page,$tag,$attrs,$text)
	{
		$block=$page->getBlock($attrs['id']);
		return $block->display($request,$page,$attrs,$text);
	}
	
	function displayVar(&$request,&$page,$tag,$attrs,$text)
	{
		$name=$attrs['name'];
		if (isset($attrs['namespace']))
		{
			$name=$attrs['namespace'].".".$name;
		}
		else
		{
			if (strpos($name,".")===false)
			{
				$name="page.variables.".$name;
			}
		}
		if ($page->prefs->isPrefSet($name))
		{
			print($page->prefs->getPref($name));
		}
	}
	
	function observeTag(&$parser,$tag,$attrs,$text)
	{
		$page=&$parser->data['page'];
		$request=&$parser->data['request'];
		if ($tag=="var")
		{
			return $this->displayVar($request,$page,$tag,$attrs,$text);
		}
		else if ($tag=="block")
		{
			return $this->displayBlock($request,$page,$tag,$attrs,$text);
		}
	}
	
	function display(&$request,&$page)
	{
		// Parse the template and display
		$parser = new TemplateParser();
		$parser->data=array('page'=>&$page,'request'=>&$request);
		$parser->addObserver("block",$this);
		$parser->addObserver("var",$this);
		
		ob_start();
		$parser->parseFile($this->dir."/".$this->file);
		ob_end_flush();
	}
}

?>