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
	
	function Block(&$container,$id,$version)
	{
		$this->Resource($container,$id,$version);
		$this->log=&LoggerManager::getLogger('swim.block');
	}
	
	function getPath()
	{
		if (is_a($this->container,'Page'))
		{
			return $this->container->getPath().'/'.$this->id;
		}
		else
		{
			return Resource::getPath().'/block/'.$this->id;
		}
	}
	
	function &getBlockEditor()
	{
		return null;
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
	
	function displayAdminControl(&$request)
	{
		$editres = new Request();
		$editres->method='edit';
		$editres->resource=$request->resource.'/'.$this->id;
		$editres->query['version']=$this->version;
		$editres->nested=&$request;
?><div class="admincontrol"><a href="<?= $editres->encode() ?>">Edit</a></div><?
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		print($text);
	}
	
	function display(&$parser,$attrs,$text)
	{
		$request=&$parser->data['request'];
		$page=&$parser->data['page'];
		$this->displayIntro($attrs);
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
			$this->displayContent($parser,$attrs,$text);
	    $text=ob_get_contents();
	    ob_end_clean();
	    $this->log->debug('Re-parsing content');
	    $parser->parseText($text);
		}
		$this->displayOutro($attrs);
	}
	
	function observeTag(&$parser,$tagname,$attrs,$text)
	{
		if ($tagname=='content')
		{
			ob_start();
			$this->displayContent($parser,$attrs,$text);
	    $text=ob_get_contents();
	    ob_end_clean();
	    $this->log->debug('Re-parsing content');
	    $parser->parseText($text);
			return true;
		}
		return false;
	}
}

function &loadBlock(&$container,$id,$version=false)
{
	global $_PREFS;
	
	$log=&LoggerManager::getLogger('swim.block.loader');
	
	$blockdir=$container->getBlockDir($id,$version);
	
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
		trigger_error('Invalid block found');
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
		$newblocks=&$container->getBlocks();
		$blocks=array_marge($blocks,$newblocks);
	}
	return $blocks;
}

?>