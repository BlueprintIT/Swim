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

class Block
{
	var $dir;
	var $resource;
	var $prefs;
	var $version;
	var $container;
	var $type = 'div';
	var $id;
	var $lock;
	var $modified;
	var $log;
	
	function Block()
	{
		global $_PREFS;
		$this->log=&LoggerManager::getLogger('swim.block');
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
	}
	
	function &getBlockEditor()
	{
		return null;
	}
	
	function setID($id)
	{
		$this->id=$id;
		unset($this->dir);
		unset($this->resource);
	}
	
	function setVersion($version)
	{
		$this->version=$version;
		unset($this->dir);
	}
	
	function setContainer($container)
	{
		$this->container=$container;
		if (is_object($container))
		{
			$this->prefs->setParent($container->prefs);
		}
		unset($this->dir);
		unset($this->resource);
	}
	
	function getResource()
	{
		if (!isset($this->resource))
		{
			if (is_object($this->container))
			{
				$this->resource=$this->container->getResource();
			}
			else
			{
				$this->resource=$this->prefs->getPref('storage.blocks.'.$this->container).'/'.$this->id;
			}
			$this->log->debug('Resource determined to be '.$this->resource);
		}
		return $this->resource;
	}
	
	function getDir()
	{
		if (!isset($this->dir))
		{
			if (is_object($this->container))
			{
				$this->dir=$this->container->getDir().'/blocks/'.$this->id;
			}
			else
			{
				$this->dir=getResourceVersion($this->getResource(),$this->version);
			}
			$this->log->debug('Dir determined to be '.$this->dir);
		}
		return $this->dir;
	}
	
	function init()
	{
	}
	
	function blockInit()
	{
	}
	
	function lockRead()
	{
		$this->lock=lockResourceRead($this->getDir());
	}
	
	function lockWrite()
	{
		$this->lock=lockResourceWrite($this->getDir());
	}
	
	function unlock()
	{
		unlockResource($this->lock);
	}
	
	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			$stat=stat($this->getDir().'/block.conf');
			$this->modified=$stat['mtime'];
		}
		return $this->modified;
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
	    $this->log->info('Re-parsing content');
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
	    $this->log->info('Re-parsing content');
	    $parser->parseText($text);
			return true;
		}
		return false;
	}
}

?>