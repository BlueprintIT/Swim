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
	
	function Block()
	{
		global $_PREFS;
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
	}
	
	function setVersion($version)
	{
		$this->version=$version;
	}
	
	function setContainer($container)
	{
		$this->container=$container;
		if (is_object($container))
		{
			$this->prefs->setParent($container->prefs);
		}
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
		}
		return $this->resource;
	}
	
	function getDir()
	{
		if (!isset($this->dir))
		{
			if (is_object($container))
			{
				$this->dir=$container->getDir().'/blocks/'.$id;
			}
			else
			{
				$resource=$this->getResource();
				$this->dir=getResourceVersion($resource,$this->version);
			}
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
	
	function displayIntro(&$data,$attrs)
	{
		$class="block";
		if ($data['mode']=='admin')
		{
			$class.=" blockadmin";
		}
		if (isset($attrs['class']))
		{
			$class.=' '.$attrs['class'];
		}
		$attrlist='id="'.$attrs['id'].'" class="'.$class.'"';
		print('<'.$this->type.' '.$attrlist.'>');
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
		$editres->nested=&$request;
?><div class="admincontrol"><a href="<?= $editres->encode() ?>">Edit</a></div><?
	}
	
	function displayContent(&$request,&$page,$attrs,$text)
	{
		print($text);
	}
	
	function displayNormal(&$data,$attrs,$text)
	{
		$this->displayIntro($data,$attrs);
		$this->lockRead();
		$this->displayContent($data['request'],$data['page'],$attrs,$text);
		$this->unlock();
		$this->displayOutro($attrs);
	}
	
	function displayAdmin(&$data,$attrs,$text)
	{
		$this->displayIntro($data,$attrs);
		$this->displayAdminControl($data['request']);
		$this->lockRead();
		$this->displayContent($data['request'],$data['page'],$attrs,$text);
		$this->unlock();
		$this->displayOutro($attrs);
	}
	
	function display(&$parser,$attrs,$text)
	{
		ob_start();
		$request=&$parser->data['request'];
		$page=&$parser->data['page'];
		if ($parser->data['mode']=='admin')
		{
			$this->displayAdmin($parser->data,$attrs,$text);
		}
		else
		{
			$this->displayNormal($parser->data,$attrs,$text);
		}
    $text=ob_get_contents();
    ob_end_clean();
    $parser->parseText($text);
		return true;
	}
	
	function observeTag(&$parser,$tagname,$attrs,$text)
	{
		return false;
	}
}

?>