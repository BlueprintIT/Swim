<?

/*
 * Swim
 *
 * Defines a block that just displays html source.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class HtmlBlock extends Block
{
	function HtmlBlock($dir,$pref)
	{
		$this->Block($dir,$pref);
	}
	
	function display($attrs,$text)
	{
		$name=$this->getPref("filename","block.html");
		readfile($this->_dir."/".$name);
	}
}

?>