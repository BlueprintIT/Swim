<?

/*
 * Swim
 *
 * The page class
 *
 * Copyright Blueprint IT Ltd. 2006
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
  
	function getViewPath($request)
	{
	  if ((!isset($this->parent)) && ($this->prefs->getPref('url.humanreadable') === true))
  	  return '/site/'.$this->container->id.'/-/'.$this->id.'/'.$this->prefs->getPref('page.variables.title');
  	else
  	  return parent::getViewPath($request);
	}
	
  function delete()
  {
  	$cats=$this->container->getPageCategories($this);
  	foreach ($cats as $category)
  	{
  		$pos = $category->indexOf($this);
  		if ($pos===false)
  		{
  			$this->log->error('Page appears to be in category '.$category->id.' but indexOf returned false.');
  		}
  		else
  		{
	  		$category->remove($category->indexOf($this));
	  	}
  	}
  	parent::delete();
  }
  
  function applyLayout()
  {
    $layout = $this->getLayout();
    if ($layout == null)
    	return;
    	
    if ($this->isWritable())
    {
      foreach ($layout->blocks as $block => $details)
      {
        if (!$this->hasResource('block',$block))
        {
          list($id,$pdir)=$this->createNewResource('block',$block);
          if ($details->hasDefaultFiles())
          {
            LockManager::lockResourceWrite($pdir);
            recursiveCopy($details->getDefaultFileDir(),$pdir,true);
            LockManager::unlockResource($pdir);
          }
        }
      }
    }
    $layprefs = new Preferences($layout->prefs);
    $layprefs->setParent($this->baseprefs);
    $this->prefs->setParent($layprefs);
    $layprefs->setDelegate($this->prefs);
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
					if ($block!==null)
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
		if ($template===null)
			$this->log->errorTrace("Null template for ".$request->resourcePath);
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
			return $block!==null;
		}
	}
	
	function setReferencedBlock($id,$block)
	{
		if ($block===null)
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
		if ($block!==null)
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
    return $this->container->layouts->getPageLayout($this->prefs->getPref('page.layout'));
  }
  
  function setLayout($id)
  {
    if ($id!=$this->prefs->getPref('page.layout'))
    {
      $newl = $this->container->layouts->getPageLayout($id);
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
	
  function getReferencedBlockFromPref($id,$blockpref)
  {
    if ($this->prefs->isPrefSet($blockpref.'.version'))
    {
      $version=$this->prefs->getPref($blockpref.'.version');
    }
    else
    {
      $version=false;
    }
    $block=Resource::decodeResource(substr($this->prefs->getPref($blockpref.'.resource'),1),$version);
    $this->blocks[$id]=$block;
    return $block;
  }
  
	function getReferencedBlock($id)
	{
		if (!isset($this->blocks[$id]))
		{
			$blockpref='page.blocks.'.$id;
			if (($this->prefs->isPrefSet($blockpref.'.resource'))&&(!$this->prefs->isPrefInherited($blockpref.'.resource')))
			{
        return $this->getReferencedBlockFromPref($id,$blockpref);
			}
			else if ($this->hasResource('block',$id))
			{
				$block=$this->getBlock($id);
				if ($block!==null)
				{
					$this->blocks[$id]=$block;
					return $block;
				}
				else
				{
					$this->blocks[$id]=null;
					return null;
				}
			}
      else if ($this->prefs->isPrefSet($blockpref.'.resource'))
      {
        return $this->getReferencedBlockFromPref($id,$blockpref);
      }
			else
			{
				$this->blocks[$id]=null;
				return null;
			}
		}
		return $this->blocks[$id];
	}
	
	function getReferencedTemplate()
	{
    $this->log->debug("Referenced template is ".$this->prefs->getPref('page.template'));
		$template=Resource::decodeResource(substr($this->prefs->getPref('page.template'),1));
		return $template;
	}
}

function getAllPages()
{
	return getAllResources('page');
}

?>