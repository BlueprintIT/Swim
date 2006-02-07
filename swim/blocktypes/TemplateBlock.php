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
	function TemplateBlock($container,$id,$version)
	{
		$this->HtmlBlock($container,$id,$version);
	}
	
	function getBlockEditor($request)
	{
		$request->data['file']=$request->query['file'];
		$container=getContainer('internal');
		$page=$container->getPage('htmledit');
		return $page;
	}
	
	function canEdit($request,$data,$attrs)
	{
		return false;
	}
	
	function displayContent($parser,$attrs,$text)
	{
		$name=$this->prefs->getPref('block.templateblock.'.$this->getFormat().'.template',$this->prefs->getPref('block.templateblock.template'));
		$resource = Resource::decodeResource($name);
		if (($resource!==false)&&($resource->isFile()))
		{
			$resource->outputFile();
		}
		return true;
	}
	
	function registerObservers($parser)
	{
		$parser->addObserver('img',$parser->data['template']);
		$parser->addObserver('a',$this);
		$parser->addObserver('section',$this);
	}
	
	function unregisterObservers($parser)
	{
		$parser->removeObserver('section',$this);
		$parser->removeObserver('a',$this);
		$parser->removeObserver('img',$parser->data['template']);
	}
	
	function observeTag($parser,$tagname,$attrs,$text)
	{
		if ($tagname=='section')
		{
			$data=$parser->data;
			$parser->startBuffer();
			if ($data['request']->method=='admin')
			{
				$id=$data['blockid'];
				if (isset($attrs['reference']))
				{
					$id=$attrs['reference'];
				}
?>
<div class="adminpanel fileadmin">
	<anchor query:reference="<?= $id ?>" nest="true" query:file="<?= $attrs['src'] ?>" method="edit" href="/<?= $this->getPath() ?>"><image class="icon" src="/global/file/images/edit.gif"/>Edit</anchor>
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