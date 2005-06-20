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
	var $prefs;
	var $page;
	var $container;
	var $type = 'div';
	var $id;
	var $lock;
	var $modified;
	
	function Block($id,$dir)
	{
		global $_PREFS;
		$this->id=$id;
		$this->dir=$dir;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
	}
	
	function &getBlockEditor()
	{
		return null;
	}
	
	function setContainer($container)
	{
		$this->container=$container;
	}
	
	function setPage(&$page)
	{
		$this->page=&$page;
		$this->prefs->setParent($page->prefs);
	}
	
	function lockRead()
	{
		$this->lock=lockResourceRead($this->dir);
	}
	
	function lockWrite()
	{
		$this->lock=lockResourceWrite($this->dir);
	}
	
	function unlock()
	{
		unlockResource($this->lock);
	}
	
	function init()
	{
	}
	
	function getModifiedDate()
	{
		if (!isset($this->modified))
		{
			$stat=stat($this->dir.'/block.conf');
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
		$editres->method='editblock';
		$editres->resource=$this->container;
		if ($this->container=='page')
		{
			$editres->resource.='/page/'.$request->resource;
		}
		$editres->resource.='/'.$this->id;
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