<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Page
{
	var $template;
	var $prefs;
	var $blocks;
	var $request;
	
	function Page($request)
	{
		global $_PREFS;
		
		$this->request = $request;
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->load();
	}
	
	function display()
	{
		$this->template->display($this);
	}
	
	function getVersion()
	{
		if (isset($this->request->version))
		{
			return $this->request->version;
		}
		else
		{
			return getVersion($this->prefs->getPref("storage.pages")."/".$this->request->page);
		}
	}
	
	function getDir()
	{
		if (isset($this->request->version))
		{
			return getResourceVersion($this->prefs->getPref("storage.pages")."/".$this->request->page,$this->request->version);
		}
		else
		{
			return getCurrentResource($this->prefs->getPref("storage.pages")."/".$this->request->page);
		}
	}
	
	function getBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref="page.blocks.".$id;
			if ($this->prefs->isPrefSet($blockpref.".id"))
			{
				$container=$this->prefs->getPref($blockpref.".container");
				$block=$this->prefs->getPref($blockpref.".id");
				if ($container=="page")
				{
					$blockdir=$this->getDir()."/blocks/".$block;
				}
				else if ($this->prefs->isPrefSet("storage.blocks.".$container))
				{
					if ($this->prefs->isPrefSet($blockpref.".version"))
					{
						$version=$this->prefs->getPref($blockpref.".version");
						$blockdir=getResourceVersion($this->prefs->getPref("storage.blocks.".$container)."/".$block,$version);
					}
					else
					{
						$blockdir=getCurrentResource($this->prefs->getPref("storage.blocks.".$container)."/".$block);
					}
				}
				else
				{
					trigger_error("Block container not set");
				}
	
				$blockprefs = new Preferences();
				$blockprefs->setParent($this->prefs);
				if (is_readable($blockdir."/block.conf"))
				{
					$blockprefs->loadPreferences($blockdir."/block.conf","block");
				}
				$class=$blockprefs->getPref("block.class");
				if ($blockprefs->isPrefSet("block.classfile"))
				{
					require_once $blockprefs->getPref("storage.blocks.classes")."/".$blockprefs->getPref("block.classfile");
				}
				else if (is_readable($blockdir."/block.class"))
				{
					require_once $blockdir."/block.class";
				}
				if (class_exists($class))
				{
					$object = new $class($blockdir);
		
					$object->setPage($this);
					$this->blocks[$id] = &$object;
				}
				else
				{
					trigger_error("Invalid block found");
				}
			}
			else
			{
				return new Block("");
			}
		}
		return $this->blocks[$id];
	}
	
	function load()
	{
		// Load the page's preferences
		if (!isset($this->request->version))
		{
			$this->request->version=getCurrentVersion($this->prefs->getPref("storage.pages")."/".$this->request->page);
		}
		
		$this->prefs->loadPreferences($this->getDir()."/page.conf","page");
		
		// Find the page's template or use the default
		if ($this->prefs->isPrefSet("page.template"))
		{
			$templ=$this->prefs->getPref("page.template");
		}
		else
		{
			$templ=$this->prefs->getPref("templates.default");
		}
		$this->template=loadTemplate($templ);
		$this->prefs->setParent($this->template->prefs);
	}
}

?>