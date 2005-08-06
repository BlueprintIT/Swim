<?

/*
 * Swim
 *
 * The abstract block class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Block extends Resource
{
	var $type = 'div';
	var $log;
	var $format;
	
	function Block(&$container,$id,$version)
	{
		$this->Resource($container,$id,$version);
		$this->log=&LoggerManager::getLogger('swim.block');
	}
	
	function &getBlockEditor(&$request)
	{
		return null;
	}

	function setFormat($form)
	{
		$this->prefs->setPref('block.format',$form);
	}
	
	function getFormat()
	{
		return $this->prefs->getPref('block.format');
	}
	
	function getType()
	{
		return $this->type;
	}
	
	function displayIntro($attrs)
	{
		$class='block';
		if (isset($attrs['class']))
		{
			$class.=' '.$attrs['class'];
		}
		$attrlist='id="'.$attrs['id'].'" class="'.$class.'"';
		print('<'.$this->getType().' '.$attrlist.'>');
	}
	
	function displayOutro($attrs)
	{
		print('</'.$this->type.'>');
	}
	
	function canEdit(&$request,&$data,$attrs)
	{
		return true;
	}
	
	function displayAdminPanel(&$request,&$data,$attrs)
	{
?>
	<div id="<?= $data['blockid'] ?>admin" class="adminpanel">
<?
		if ($this->canEdit($request,$data,$attrs))
		{
?>
		<editlink block="<?= $data['blockid'] ?>"><image class="icon" src="/global/template/base/file/layout/edit.gif"/>Edit</editlink>
<?
		}
?>
	</div>
<?
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		print($text);
	}
	
	function registerObservers(&$parser)
	{
	}
	
	function unregisterObservers(&$parser)
	{
	}
	
	function display(&$parser,$attrs,$text)
	{
		$request=&$parser->data['request'];
		$page=&$parser->data['page'];
		$parser->data['blockattrs']=&$attrs;
		if (strlen(trim($text))>0)
		{
			$parser->addObserver('content',$this);
			//$parser->pushStack('temp');
			$parser->parseText($text);
			//$result=$parser->popStack();
			$parser->removeObserver('content',$this);
		}
		else
		{
			ob_start();
			if (($request->method=='admin')&&((!isset($attrs['panel']))||($attrs['panel']!='false')))
			{
				$this->displayAdminPanel($request,$parser->data,$attrs);
			}
			$this->displayIntro($attrs);
			$this->displayContent($parser,$attrs,$text);
			$this->displayOutro($attrs);
	    $text=ob_get_contents();
	    ob_end_clean();
	    $this->log->debug('Re-parsing content');
	    $this->registerObservers($parser);
	    $parser->parseText($text);
	    $this->unregisterObservers($parser);
		}
		unset($parser->data['blockattrs']);
	}
	
	function observeTag(&$parser,$tagname,$attrs,$text)
	{
		if ($tagname=='content')
		{
			ob_start();
			$request=&$parser->data['request'];
			if (($request->method=='admin')&&((!isset($attrs['panel']))||($attrs['panel']!='false')))
			{
				$this->displayAdminPanel($request,$parser->data,$parser->data['blockattrs']);
			}
			$this->displayIntro($parser->data['blockattrs']);
			$this->displayContent($parser,$attrs,$text);
			$this->displayOutro($attrs);
	    $text=ob_get_contents();
	    ob_end_clean();
	    $this->log->debug('Re-parsing content');
	    $this->registerObservers($parser);
	    $parser->parseText($text);
	    $this->unregisterObservers($parser);
			return true;
		}
		return false;
	}
}

function &loadBlock($blockdir,&$container,$id,$version=false)
{
	global $_PREFS;
	
	$log=&LoggerManager::getLogger('swim.block.loader');
	
	if (is_dir($blockdir))
	{
		if ($container->isWritable())
			$lock=lockResourceRead($blockdir);
	
		$blockprefs = new Preferences();
		$blockprefs->setParent($_PREFS);
		if (is_readable($blockdir.'/resource.conf'))
		{
			$file=fopen($blockdir.'/resource.conf','r');
			$blockprefs->loadPreferences($file,'block');
			fclose($file);
		}
		$class=$blockprefs->getPref('block.class');
		if ($blockprefs->isPrefSet('block.classfile'))
		{
			require_once $blockprefs->getPref('storage.blocks.classes').'/'.$blockprefs->getPref('block.classfile');
		}
		else if (is_readable($blockdir.'/block.class'))
		{
			require_once $blockdir.'/block.class';
		}
	
		if ($container->isWritable())
			unlockResource($lock);
	
		if (class_exists($class))
		{
			$log->debug('Block loaded');
			$object = new $class($container,$id,$version);
			return $object;
		}
		else
		{
			trigger_error('Invalid block found at '.$blockdir);
			return false;
		}
	}
	else
	{
		$log->warn('Passed invalid block dir '.$blockdir);
		return false;
	}
}

function &getAllBlocks()
{
	global $_PREFS;
	
	$blocks=array();
	$containers=&getAllContainers();
	foreach(array_keys($containers) as $id)
	{
		$container=&$containers[$id];
		$newblocks=&$container->getResources('block');
		$blocks=array_marge($blocks,$newblocks);
	}
	return $blocks;
}

?>