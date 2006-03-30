<?

/*
 * Swim
 *
 * The abstract block class
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Block extends Resource
{
	var $log;
	var $format;
	
	function Block($container,$id,$version)
	{
		$this->Resource($container,$id,$version);
    if ((isset($this->parent))&&($this->parent instanceof Page))
    {
      $layout = $this->parent->getLayout();
      if ($layout != null)
      {
	      $blk = $layout->getBlockLayout($this->id);
	      if ($blk!=null)
	      {
	        $layprefs = new Preferences($blk->prefs);
	        $layprefs->setParent($this->prefs->getParent());
	        $this->prefs->setParent($layprefs);
	      }
	    }
    }
		$this->log=LoggerManager::getLogger('swim.block');
	}
	
	function getBlockEditor($request)
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
		return $this->prefs->getPref('block.type','div');
	}
	
	function displayIntro($attrs)
	{
		if ($this->prefs->isPrefSet('block.stylesheets'))
		{
			$styles=explode(',',$this->prefs->getPref('block.stylesheets'));
			foreach ($styles as $style)
			{
				print('<stylesheet src="/'.$style.'"/>');
			}
		}
    if (!isset($attrs['notag']))
    {
  		$class='block';
  		if (isset($attrs['class']))
  		{
  			$class.=' '.$attrs['class'];
        unset($attrs['class']);
  		}
      $attrlist='id="'.$attrs['id'].'" class="'.$class.'"';
      unset($attrs['id']);
      foreach ($attrs as $attr => $value)
      {
        $attrlist.=' '.$attr.'="'.$value.'"';
      }
  		print('<'.$this->getType().' '.$attrlist.'>');
    }
	}
	
	function displayOutro($attrs)
	{
    if (!isset($attrs['notag']))
  		print('</'.$this->getType().'>');
	}
	
	function canEdit($request,$data,$attrs)
	{
		return true;
	}
		
	function displayContent($parser,$attrs,$text)
	{
		print($text);
	}
	
	function registerObservers($parser)
	{
	}
	
	function unregisterObservers($parser)
	{
	}
	
	function display($parser,$attrs,$text)
	{
		$request=$parser->data['request'];
		$page=$parser->data['page'];
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
	
	function observeTag($parser,$tagname,$attrs,$text)
	{
		if ($tagname=='content')
		{
			ob_start();
			$request=$parser->data['request'];
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

function loadBlock($blockdir,$container,$id,$version=false)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger('swim.block.loader');
	
	if (is_dir($blockdir))
	{
		if ($container->isWritable())
			LockManager::lockResourceRead($blockdir);
	
    $blockprefs = new Preferences();
    $blockprefs->setParent($_PREFS);

    if ($container instanceof Page)
    {
      $layout=$container->getLayout();
      if ($layout != null)
      {
	      $blk=$layout->getBlockLayout($id);
	      if ($blk!=null)
	      {
	        $blockprefs->addPreferences($blk->prefs,false);
	      }
	    }
    }
    
		if (is_readable($blockdir.'/resource.conf'))
		{
			$file=fopen($blockdir.'/resource.conf','r');
			$blockprefs->loadPreferences($file,'block',true);
			fclose($file);
		}
		$class=$blockprefs->getPref('block.class');
		if (($blockprefs->isPrefSet('block.classfile'))&&(is_readable($blockprefs->getPref('storage.blocks.classes').'/'.$blockprefs->getPref('block.classfile'))))
		{
			require_once $blockprefs->getPref('storage.blocks.classes').'/'.$blockprefs->getPref('block.classfile');
		}
		else if (is_readable($blockdir.'/block.class.php'))
		{
			require_once $blockdir.'/block.class.php';
		}
	
		if ($container->isWritable())
			LockManager::unlockResource($blockdir);
	
		if (class_exists($class))
		{
			$log->debug('Block loaded');
			$object = new $class($container,$id,$version);
			return $object;
		}
		else
		{
			$log->warn('Invalid block found at '.$blockdir);
			return null;
		}
	}
	else
	{
		$log->warn('Passed invalid block dir '.$blockdir);
		return null;
	}
}

function getAllBlocks()
{
	return getAllResources('block');
}

?>