<?

/*
 * Swim
 *
 * Defines a block that is generated from php source.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class PhpBlock extends Block
{
	function PhpBlock($container,$id,$version)
	{
		$this->Block($container,$id,$version);
	}
	
	function registerObservers($parser)
	{
		if ($this->prefs->getPref("block.observe.a"))
			$parser->addObserver('img',$parser->data['template']);
		if ($this->prefs->getPref("block.observe.img"))
			$parser->addObserver('a',$this);
	}
	
	function unregisterObservers($parser)
	{
		if ($this->prefs->getPref("block.observe.a"))
			$parser->removeObserver('a',$this);
		if ($this->prefs->getPref("block.observe.img"))
			$parser->removeObserver('img',$parser->data['template']);
	}
	
	function getModifiedDate()
	{
		return time();
	}

	function displayContent($parser,$attrs,$text)
	{
		global $_USER,$_PREFS;
		
		$request=$parser->data['request'];
		$prefs=$this->prefs;
		$log = LoggerManager::getLogger('page');
		$name=$this->prefs->getPref('block.phpblock.filename','block.php');
		include($this->getDir().'/'.$name);
		return true;
	}

	function observeTag($parser,$tagname,$attrs,$text)
	{
		if ($tagname=='a')
		{
			$this->log->debug('Observing a link');
			$link=$attrs['href'];
			if (substr($link,0,12)=='attachments/')
			{
				$this->log->debug('Attachment link');
				$request = new Request();
				$request->method=$parser->data['request']->method;
				$request->resource=$this->getPath().'/file/'.$link;
				$attrs['href']=$request->encode();
			}
			else if (substr($link,0,1)=='/')
			{
				$this->log->debug('Internal link');
				$request = new Request();
				$request->method=$parser->data['request']->method;
				$request->resource=substr($link,1);
				$attrs['href']=$request->encode();
			}
			else
			{
				$this->log->debug('External link');
				$attrs['target']="_blank";
			}
			print(Template::buildElement($parser,$tagname,$attrs,$text));
			return true;
		}
		else
		{
			return Block::observeTag($parser,$tagname,$attrs,$text);
		}
	}
}

?>