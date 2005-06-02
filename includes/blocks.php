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
	var $type = "div";
	
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
	
	function displayIntro($attrs)
	{
		$attrlist="id=\"".$attrs['id']."\"";
		if (isset($attrs['class']))
		{
			$attrlist.=" class=\"".$attrs['id']."\"";
		}
		print("<".$this->type." ".$attrlist.">");
	}
	
	function displayOutro($attrs)
	{
		print("</".$this->type.">");
	}
	
	function displayAdminControl()
	{
	}
	
	function displayContent($attrs,$text)
	{
		print($text);
	}
	
	function displayNormal($attrs,$text)
	{
		$this->displayIntro($attrs);
		$this->displayContent($attrs,$text);
		$this->displayOutro($attrs);
	}
	
	function displayAdmin($attrs,$text)
	{
		$this->displayIntro($attrs);
		$this->displayAdminControl();
		$this->displayContent($attrs,$text);
		$this->displayOutro($attrs);
	}
	
	function display($attrs,$text)
	{
		if ($this->page->request->mode=="admin")
		{
			$this->displayAdmin($attrs,$text);
		}
		else
		{
			$this->displayNormal($attrs,$text);
		}
	}
}

?>