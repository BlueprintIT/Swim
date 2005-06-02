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
	
	function display($attrs,$text)
	{
		$attrlist="id=\"".$attrs['id']."\"";
		if (isset($attrs['class']))
		{
			$attrlist.=" class=\"".$attrs['id']."\"";
		}
		print("<div ".$attrlist.">");
		$name=$this->prefs->getPref("block.filename","block.html");
		readfile($this->dir."/".$name);
		print("</div>");
	}
}

?>