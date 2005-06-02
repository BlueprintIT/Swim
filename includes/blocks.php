<?

/*
 * Swim
 *
 * The abstract block class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Block
{
	var $dir;
	var $prefs;
	var $page;
	
	function Block($dir)
	{
		global $_PREFS;
		$this->dir=$dir;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
	}
	
	function setPage(&$page)
	{
		$this->page=&$page;
		$this->prefs->setParent($page->prefs);
	}
	
	function init()
	{
	}
	
	function display($attrs,$text)
	{
	}
}

?>