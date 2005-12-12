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
  var $baseprefs;
	
  function Page($container, $id, $version)
  {
    $this->Resource($container, $id, $version);
    $this->baseprefs=$this->prefs->getParent();
    $this->applyLayout();
  }
  
  function applyLayout()
  {
    $layout = $this->getLayout();
    if ($this->isWritable())
    {
      foreach ($layout->blocks as $block => $details)
      {
        if (!$this->hasResource('block',$block))
        {
          list($id,$pdir)=$this->createNewResource('block',$block);
          if ($details->hasDefaultFiles())
          {
            lockResourceWrite($pdir);
            recursiveCopy($details->getDefaultFileDir(),$pdir,true);
            unlockResource($pdir);
          }
        }
      }
    }
    $layprefs = new Preferences($layout->prefs);
    $layprefs->setParent($this->baseprefs);
    $this->prefs->setParent($layprefs);
  }
  
	function getTotalModifiedDate()
	{
		$modified=$this->getModifiedDate();
		$template=$this->getReferencedTemplate();
		$modified=max($modified,$template->getModifiedDate());

		$blocks=$this->prefs->getPrefBranch('page.blocks');
		foreach ($blocks as $key=>$id)
		{
			if (substr($key,-9,9)=='.resource')
			{
				$blk=substr($key,0,-9);
				$cont=$this->prefs->getPref('page.blocks.'.$blk.'.container');
				if ($cont!='page')
				{
					$block=$this->getReferencedBlock($blk);
					if ($block!==false)
					{
						$modified=max($modified,$block->getModifiedDate());
					}
				}
			}
		}
		
		$blocks=$this->getResources('block');
		foreach ($blocks as $block)
		{
			$modified=max($modified,$block->getModifiedDate());
		}
		
		return $modified;
	}
	
	function display($request)
	{
		$template=$this->getReferencedTemplate();
		$template->display($request,$this);
	}
	
	function isReferencedBlock($id=false)
	{
		if ($id===false)
		{
			return false;
		}
		else
		{
			$block=$this->getReferencedBlock($id);
			return $block!==false;
		}
	}
	
	function setReferencedBlock($id,$block)
	{
		if ($block===false)
		{
			$this->prefs->unsetPref('page.blocks.'.$id.'.resource');
			$this->prefs->unsetPref('page.blocks.'.$id.'.version');
			unset($this->blocks[$id]);
		}
		else
		{
			$this->blocks[$id]=$block;
			if ($id==$block->id)
			{
				if ((isset($block->parent))&&($block->parent->getPath()==$this->getPath()))
				{
					$this->prefs->unsetPref('page.blocks.'.$id.'.resource');
					$this->prefs->unsetPref('page.blocks.'.$id.'.version');
					return;
				}
			}
			$this->prefs->setPref('page.blocks.'.$id.'.resource',$block->getPath());
			if ($block->isCurrentVersion())
			{
				$this->prefs->unsetPref('page.blocks.'.$id.'.version');
			}
			else
			{
				$this->prefs->setPref('page.blocks.'.$id.'.version',$block->version);
			}
		}
	}
	
	function canChangeReferencedBlock($id)
	{
		$block=$this->getReferencedBlock($id);
		if ($block!==false)
		{
			if (isset($block->parent))
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return true;
		}
	}
	
  function getLayout()
  {
    return LayoutManager::getPageLayout($this->prefs->getPref('page.layout'));
  }
  
  function setLayout($id)
  {
    if ($id!=$this->prefs->getPref('page.layout'))
    {
      $newl = LayoutManager::getPageLayout($id);
      if ($newl!==null)
      {
        $this->prefs->setPref('page.layout',$id);
        $this->applyLayout();
        $this->savePreferences();
      }
    }
  }
  
	function getReferencedBlockUsage($block)
	{
		if (isset($block->parent))
		{
			return array();
		}
		$container=$block->container->id;
		$result=array();
		$path=$block->getPath();
		$blocks=$this->prefs->getPrefBranch('page.blocks');
		foreach ($blocks as $key=>$id)
		{
			if ((substr($key,-9,9)=='.resource')&&($id==$path))
			{
				$blk=substr($key,0,-9);
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
		return $result;
	}
	
	function getReferencedBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref='page.blocks.'.$id;
			if ($this->prefs->isPrefSet($blockpref.'.resource'))
			{
				if ($this->prefs->isPrefSet($blockpref.'.version'))
				{
					$version=$this->prefs->getPref($blockpref.'.version');
				}
				else
				{
					$version=false;
				}
				$block=Resource::decodeResource($this->prefs->getPref($blockpref.'.resource'),$version);
				$this->blocks[$id]=$block;
			}
			else if ($this->hasResource('block',$id))
			{
				$block=$this->getBlock($id);
				if ($block!==false)
				{
					$this->blocks[$id]=$block;
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
	
	function getReferencedTemplate()
	{
		$template=Resource::decodeResource($this->prefs->getPref('page.template'));
		return $template;
	}
}

function getAllPages()
{
	return getAllResources('page');
}

?>