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
	function PhpBlock($id,$dir)
	{
		$this->Block($id,$dir);
	}
	
	function displayContent(&$request,&$page,$attrs,$text)
	{
		global $_USER;
		
		$log = &LoggerManager::getLogger('page');
		$name=$this->prefs->getPref('block.phpblock.filename','block.php');
		include($this->dir.'/'.$name);
		return true;
	}
}

?>