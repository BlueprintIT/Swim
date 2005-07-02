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
	function HtmlBlock(&$container,$id,$version)
	{
		$this->Block($container,$id,$version);
	}
	
	function &getBlockEditor()
	{
		$container=&getContainer('internal');
		$page=&$container->getPage('htmledit');
		return $page;
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		$name=$this->prefs->getPref('block.htmlblock.filename','block.html');
		readfile($this->getDir().'/'.$name);
		return true;
	}
}

?>