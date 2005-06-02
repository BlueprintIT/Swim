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
	function HtmlBlock($dir)
	{
		$this->Block($dir);
	}
	
	function displayContent($attrs,$text)
	{
		$name=$this->prefs->getPref("block.filename","block.html");
		readfile($this->dir."/".$name);
	}
}

?>