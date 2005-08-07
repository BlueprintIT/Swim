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

class TemplateBlock extends HtmlBlock
{
	function TemplateBlock(&$container,$id,$version)
	{
		$this->HtmlBlock($container,$id,$version);
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
		if ($tagname=='section')
		{
			$data=&$parser->data;
			$parser->startBuffer();
			if ($data['request']->method=='admin')
			{
?>
<div class="adminpanel fileadmin">
	<editlink block="<?= $data['blockid'] ?>" file="<?= $attrs['src'] ?>"><image class="icon" src="/global/file/images/edit.gif"/>Edit</editlink>
</div>
<?
			}
?><file src="<?= $attrs['src'] ?>"/><?
			$parser->endBuffer();
			return true;
		}
		else
		{
			return parent::observeTag($parser,$tagname,$attrs,$text);
		}
	}
}

?>