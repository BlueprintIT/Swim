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
	function PhpBlock()
	{
		$this->Block();
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		global $_USER;
		
		$request=$parser->data['request'];
		$prefs=&$this->prefs;
		$log = &LoggerManager::getLogger('page');
		$name=$this->prefs->getPref('block.phpblock.filename','block.php');
		include($this->getDir().'/'.$name);
		return true;
	}
}

?>