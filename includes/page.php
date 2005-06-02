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
	
	function Page()
	{
		global $_PREFS;
		
		$this->blocks = array();
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->choosePage();
		$this->load();
	}
	
	function getBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref="page.blocks.".$id;
			$container=$this->prefs->getPref($blockpref.".container");
			$block=$this->prefs->getPref($blockpref.".id");
			if ($container=="page")
			{
				$blockdir=getCurrentVersion($this->prefs->getPref("storage.pages")."/".$this->request->page)."/blocks/".$block;
			}
			else if ($this->prefs->isPrefSet("storage.blocks.".$container))
			{
				$version=$this->prefs->getPref($blockpref.".version");
				$blockdir=getVersion($this->prefs->getPref("storage.blocks.".$container)."/".$block,$version);
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
			eval("\$object = new ".$class."(\"".$blockdir."\");");

			$object->setPage($this);
			$this->blocks[$id] = &$object;
		}
		return $this->blocks[$id];
	}
	
	function load()
	{
		// Load the page's preferences
		$this->prefs->loadPreferences(getCurrentVersion($this->prefs->getPref("storage.pages")."/".$this->request->page)."/page.conf","page");
		// Find the page's template or use the default
		if ($this->prefs->isPrefSet("page.template"))
		{
			$templ=$this->prefs->getPref("page.template");
		}
		else
		{
			$templ=$this->prefs->getPref("templates.default");
		}
		$this->template = new Template($templ);
		$this->prefs->setParent($this->template->prefs);
	}
	
	function choosePage()
	{
		// Figure out what page we are attempting to view
		$this->request = new Request();
		$this->request->decodeCurrentRequest();
		
		// If there is no page then use the default page
		if ($this->request->page=="")
		{
			$this->request->page=$this->prefs->getPref("pages.default");
		}
		
		// These are the fallback pages we want to display in order of preference
		$selection = array($this->prefs->getPref("pages.error"), $this->prefs->getPref("pages.default"));
		
		while (!($this->isValidPage()))
		{
			if (count($selection)==0)
			{
				trigger_error("This website has not been properly configured.");
				exit;
			}
			
			// Bad page so get the next fallback and clear the query.
			$this->request->page=array_shift($selection);
			$this->query=array();
		}
	}
	
	function decodeRequest()
	{
	}
	
	function isValidPage()
	{
		return is_readable(getCurrentVersion($this->prefs->getPref("storage.pages")."/".$this->request->page)."/page.conf");
	}
}

?>