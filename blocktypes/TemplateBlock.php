<?

/*
 * Swim
 *
 * Defines a block that displays templated source.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class TemplateBlock extends Block
{
	function TemplateBlock(&$container,$id,$version)
	{
		$this->Block($container,$id,$version);
	}
	
	function &getBlockEditor(&$request)
	{
		$request->data['file']=$request->query['file'];
		$container=&getContainer('internal');
		$page=&$container->getPage('htmledit');
		return $page;
	}
	
	function canEdit(&$request,&$data,$attrs)
	{
		return false;
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		$name=$this->prefs->getPref('block.templateblock.'.$this->getFormat().'.template',$this->prefs->getPref('block.templateblock.template'));
		$resource = &Resource::decodeResource($name);
		if ($resource->isFile())
		{
			$resource->outputFile();
		}
		return true;
	}
	
	function registerObservers(&$parser)
	{
		$parser->addObserver('a',$this);
		$parser->addObserver('section',$this);
	}
	
	function unregisterObservers(&$parser)
	{
		$parser->removeObserver('a',$this);
		$parser->removeObserver('section',$this);
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
			Template::displayElement($parser,$tagname,$attrs,$text);
			return true;
		}
		else if ($tagname=='section')
		{
			$data=&$parser->data;
			$parser->startBuffer();
			if ($data['request']->method=='admin')
			{
?>
<div class="adminpanel fileadmin">
	<editlink block="<?= $data['blockid'] ?>" file="<?= $attrs['src'] ?>"><image class="icon" src="/global/template/base/file/layout/edit.gif"/>Edit</editlink>
</div>
<?
			}
?><file src="<?= $attrs['src'] ?>"/><?
			$parser->endBuffer();
			return true;
		}
		else
		{
			return Block::observeTag($parser,$tagname,$attrs,$text);
		}
	}
}

?>