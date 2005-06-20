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
	function HtmlBlock()
	{
		$this->Block();
	}
	
	function &getBlockEditor()
	{
		return new HtmlEditBlock();
	}
	
	function displayContent(&$request,&$page,$attrs,$text)
	{
		$name=$this->prefs->getPref('block.htmlblock.filename','block.html');
		readfile($this->getDir().'/'.$name);
		return true;
	}
}

class HtmlEditBlock extends Block
{
	function HtmlEditBlock()
	{
		$this->Block();
	}

	function displayContent(&$request,&$page,$attrs,$text)
	{
		print 'Block editor';
		return true;
	}
}

?>