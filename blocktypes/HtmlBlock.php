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
	
	function &getBlockEditor(&$request)
	{
		$request->data['file']=$this->prefs->getPref('block.htmlblock.filename','block.html');
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
	
	function registerObservers(&$parser)
	{
		$parser->addObserver('a',$this);
	}
	
	function unregisterObservers(&$parser)
	{
		$parser->removeObserver('a',$this);
	}
	
	function observeTag(&$parser,$tagname,$attrs,$text)
	{
		if ($tagname=='a')
		{
			$this->log->debug('Observing a link');
			$link=$attrs['href'];
			if (substr($link,0,12)=='attachments/')
			{
				$this->log->debug('Attachment link');
				$data=&$parser->data;
				$attrs['href']=$this->getPath().'/'.$link;
			}
			else if (strpos($link,'://')!==false)
			{
				$this->log->debug('External link');
			}
			else
			{
				$this->log->debug('Internal link');
				$request = new Request();
				$request->method='view';
				$request->resource=$link;
				$attrs['href']=$request->encode();
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