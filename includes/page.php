<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Page extends Resource
{
	var $blocks = array();
	
	function getTotalModifiedDate()
	{
		$modified=$this->getModifiedDate();
		$template=&$this->getReferencedTemplate();
		$modified=max($modified,$template->getModifiedDate());

		$blocks=$this->prefs->getPrefBranch('page.blocks');
		foreach ($blocks as $key=>$id)
		{
			if (substr($key,-3,3)=='.id')
			{
				$blk=substr($key,0,-3);
				$cont=$this->prefs->getPref('page.blocks.'.$blk.'.container');
				if ($cont!='page')
				{
					$block=&$this->getReferencedBlock($blk);
					if ($block!==false)
					{
						$modified=max($modified,$block->getModifiedDate());
					}
				}
			}
		}

		return $modified;
	}
	
	function display(&$request)
	{
		$template=&$this->getReferencedTemplate();
		$template->display($request,$this);
	}
	
	function displayAdmin(&$request)
	{
		$template=&$this->getReferencedTemplate();
		$template->displayAdmin($request,$this);
	}
	
	function savePreferences()
	{
		$file=$this->openFileWrite('resource.conf');
		$this->prefs->savePreferences($file);
		$this->closeFile($file);
	}
	
	function isReferencedBlock($id=false)
	{
		if ($id===false)
		{
			return false;
		}
		else
		{
			$block=&$this->getReferencedBlock($id);
			return $block!==false;
		}
	}
	
	function setReferencedBlock($id,&$block)
	{
		$this->blocks[$id]=&$block;
	}
	
	function &getReferencedBlockUsage(&$block)
	{
		if (isset($block->parent))
		{
			return array();
		}
		$container=$block->container->id;
		$result=array();
		$blocks=$this->prefs->getPrefBranch('page.blocks');
		foreach ($blocks as $key=>$id)
		{
			if ((substr($key,-3,3)=='.id')&&($id==$block->id))
			{
				$blk=substr($key,0,-3);
				$cont=$this->prefs->getPref('page.blocks.'.$blk.'.container');
				if ($cont==$container)
				{
					if (($this->prefs->isPrefSet('page.blocks.'.$blk.'.version'))&&($block->version==$this->prefs->getPref('page.blocks.'.$blk.'.version')))
					{
						$result[]=$blk;
					}
					else if ($block->isCurrentVersion())
					{
						$result[]=$blk;
					}
				}
			}
		}
		return $result;
	}
	
	function &getReferencedBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref='page.blocks.'.$id;
			if ($this->prefs->isPrefSet($blockpref.'.id'))
			{
				$container=$this->prefs->getPref($blockpref.'.container');
				$block=$this->prefs->getPref($blockpref.'.id');
				if ($container=='page')
				{
					$blockobj = &$this->getBlock($block);
				}
				else
				{
					$container=&getContainer($container);
					if ($this->prefs->isPrefSet($blockpref.'.version'))
					{
						$version=$this->prefs->getPref($blockpref.'.version');
					}
					else
					{
						$version=false;
					}
					$blockobj=&$container->getBlock($block,$version);
				}
				$this->blocks[$id]=&$blockobj;
			}
			else if ($this->hasResource('block',$id))
			{
				$block=&$this->getBlock($id);
				if ($block!==false)
				{
					$this->blocks[$id]=&$block;
				}
				else
				{
					$this->blocks[$id]=false;
				}
			}
			else
			{
				$this->blocks[$id]=false;
			}
		}
		return $this->blocks[$id];
	}
	
	function &getReferencedTemplate()
	{
		$template=&Resource::decodeResource($this->prefs->getPref('page.template'));
		return $template;
	}
}

function &getAllPages()
{
	global $_PREFS;
	
	$pages=array();
	$containers=&getAllContainers();
	foreach(array_keys($containers) as $id)
	{
		$container=&$containers[$id];
		$newpages=&$container->getResources('page');
		$pages=array_merge($pages,$newpages);
	}
	return $pages;
}

?>