<?

/*
 * Swim
 *
 * Defines a block that is generated from php source.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class PhpBlock extends Block
{
	function PhpBlock($dir)
	{
		$this->Block($dir);
	}
	
	function displayContent($attrs,$text)
	{
		global $page,$_USER;
		
		$log = &LoggerManager::getLogger("page");
		$name=$this->prefs->getPref("block.phpblock.filename","block.php");
		include($this->dir."/".$name);
		return true;
	}
}

?>