<?

/*
 * Swim
 *
 * Containers hold the pages, blocks, templates and files that the website can serve
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Container extends Resource
{
	var $log;
	
	var $templates = array();
	var $pages = array();
	var $blocks = array();
	var $working = array();
	var $visible;
  var $versioned;

  var $rootcategory;
  var $modified;
  var $layouts;
	
	function Container($id)
	{
		global $_PREFS,$_STORAGE;
    
    $this->log=LoggerManager::getLogger('swim.container.'.$id);

		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->dir=$this->prefs->getPref('container.'.$id.'.basedir');
		$this->log->debug('Container '.$id.' is at '.$this->dir);
		if (is_readable($this->getDir().'/resource.conf'))
		{
			$file=fopen($this->getDir().'/resource.conf','r');
			if ($file!==false)
			{
				$this->prefs->loadPreferences($file);
				fclose($file);
			}
		}
    
    $this->layouts = new LayoutCollection($this->getDir(), LayoutManager::getRootCollection());

    $this->visible=$this->prefs->getPref('container.visible',true);
    $this->versioned=$this->prefs->getPref('container.versioned',true);
    $this->writable=$this->prefs->getPref('resource.writable',true);
    $name = "'".$_STORAGE->escape($this->id)."'";
    $set=$_STORAGE->query('SELECT Category.id,Category.name,date FROM Container,Category WHERE Category.id=rootcategory AND Container.id='.$name.';');
    if ($set->valid())
    {
      $details = $set->fetch();
      $this->modified=$details['date'];
      $this->rootcategory = new Category($this,null,$details['Category.id'],$details['Category.name']);
      ObjectCache::setItem('category', $this->rootcategory->id, $this->rootcategory);
    }
 	}
	
  function getModifiedDate()
  {
    return $this->modified;
  }
  
  function getRootCategory()
  {
    return $this->rootcategory;
  }
  
  function getLink($id)
  {
    global $_STORAGE;
    
    $link = ObjectCache::getItem('link', $id);
    if ($link === null)
    {
      $set=$_STORAGE->query('SELECT * FROM Link WHERE id='.$id.';');
      if ($set->valid())
      {
        $details = $set->fetch();
        $link = new Link($this->getCategory($details['category']),$details['id'],$details['name'],$details['link']);
        $link->newwindow = $details['newwindow'];
        ObjectCache::setItem('link', $id, $link);
      }
    }
    return $link;
  }
  
  function getReadyCategory($id,$parent,$name,$icon,$hovericon)
  {
    $category = ObjectCache::getItem('category', $id);
    if ($category === null)
    {
      $category = new Category($this,$this->getCategory($parent),$id,$name);
      $category->icon = $icon;
      if ($hovericon===null)
      	$category->hovericon=$icon;
      else
      	$category->hovericon=$hovericon;
      ObjectCache::setItem('category', $id, $category);
    }
    return $category;
  }
  
  function getCategory($id)
  {
    global $_STORAGE;
    
    $category = ObjectCache::getItem('category', $id);
    if ($category === null)
    {
      $set=$_STORAGE->query('SELECT id,parent,name,icon,hovericon FROM Category WHERE id='.$id.';');
      if ($set->valid())
      {
        $details = $set->fetch();
        $category = new Category($this,$this->getCategory($details['parent']),$details['id'],$details['name']);
        $category->icon = $details['icon'];
        if ($details['hovericon']===null)
        {
	        $category->hovericon = $details['icon'];
        }
        else
        {
	        $category->hovericon = $details['hovericon'];
        }
        ObjectCache::setItem('category', $id, $category);
      }
    }
    return $category;
  }
  
  function getPageCategories($page)
  {
    global $_STORAGE;

    $path="'".$_STORAGE->escape($page->getPath())."'";
    $set=$_STORAGE->query('SELECT id,parent,name,icon,hovericon FROM Category JOIN PageCategory ON Category.id=PageCategory.category WHERE PageCategory.page='.$path.';');
    $results = array();
    while ($set->valid())
    {
      $details=$set->fetch();
      $results[]=$this->getReadyCategory($details['id'],$details['parent'],$details['name'],$details['icon'],$details['hovericon']);
      $this->log->debug('Found page '.$page->getPath().' in category '.$details['id']);
    }
    return $results;
  }
  
	function getDir()
	{
		return $this->dir;
	}
	
	function getPath()
	{
		return $this->id;
	}
	
	function fileIsWritable($filename)
	{
		return false;
	}
	
	function fileIsReadable($filename)
	{
		return (is_readable($this->getDir().'/'.$filename));
	}
	
	function getCurrentVersion($dir)
	{
    if ($this->isVersioned())
    {
    	if (is_dir($dir))
    	{
    		if ($this->isWritable())
    		{
    			LockManager::lockResourceRead($dir);
    		}
    
    		$vers=fopen($dir.'/version','r');
    		$version=fgets($vers);
    		fclose($vers);
    
    		if ($this->isWritable())
    		{
    			LockManager::unlockResource($dir);
    		}
    		
    		return $version;
    	}
    }
		return false;
	}
	
	function getResourceBaseDir($resource)
	{
		if ($resource instanceof File)
		{
			return $this->getDir().'/files';
		}
		else
		{
			return $this->getDir().'/'.$resource->getTypeName().'s/'.$resource->id;
		}
	}
	
	function getResourceVersions($resource)
	{
		$versions=array();

		$dir=$this->getResourceBaseDir($resource);

		if (($this->isVersioned())&&($res=@opendir($dir)))
		{
			while (($file=readdir($res))!== false)
			{
				if (!(substr($file,0,1)=='.'))
				{
					if ((is_dir($dir.'/'.$file))&&((is_numeric($file))||($file=='base')))
					{
						if ($resource instanceof Page)
						{
							$versions[$file] = $this->getPage($resource->id,$file);
						}
						else if ($resource instanceof Block)
						{
							$versions[$file] = $this->getBlock($resource->id,$file);
						}
						else if ($resource instanceof Template)
						{
							$versions[$file] = $this->getTemplate($resource->id,$file);
						}
					}
				}
			}
			closedir($res);
		}
    else
    {
      $versions[$resource->id] = &$resource;
    }
		
		return $versions;
	}
	
	function getResourceWorkingDetails($resource)
	{
		global $_USER;
		
		$dir=$this->getResourceBaseDir($resource);
		if ($resource instanceof Page)
		{
			$type='page';
		}
		else if ($resource instanceof Block)
		{
			$type='block';
		}
		else if ($resource instanceof Template)
		{
			$type='template';
		}
		
		if (!isset($this->working[$type][$resource->id]))
		{
			$this->working[$type][$resource->id] = new WorkingDetails($this,$resource->id,$this->prefs->getPref('version.working'),$dir.'/'.$this->prefs->getPref('version.working'));
		}
		return $this->working[$type][$resource->id];
	}
	
	function makeNewResourceVersion($resource)
	{
		$dir=$this->getResourceBaseDir($resource);
		
		LockManager::lockResourceWrite($dir);
		
		$newest=-1;
		if ($res=@opendir($dir))
		{
			while (($file=readdir($res))!== false)
			{
				if (!(substr($file,0,1)=='.'))
				{
					if ((is_dir($dir.'/'.$file))&&(is_numeric($file)))
					{
						if ($file>$newest)
						{
							$newest=$file;
						}
					}
				}
			}
			closedir($res);
		}
		if ($newest>=0)
		{
			$next=$newest+1;
		}
		else
		{
			$next=1;
		}
		
		mkdir($dir.'/'.$next);
	
		LockManager::unlockResource($dir);
		
		$source=$resource->getDir();
		$target=$dir.'/'.$next;
		$resource->lockRead();
		LockManager::lockResourceWrite($target);
		recursiveCopy($source,$target,true);
		LockManager::unlockResource($target);
		$resource->unlock();

		if ($resource instanceof Page)
		{
			return $this->getPage($resource->id,$next);
		}
		else if ($resource instanceof Block)
		{
			return $this->getBlock($resource->id,$next);
		}
		else if ($resource instanceof Template)
		{
			return $this->getTemplate($resource->id,$next);
		}
	}
	
	function makeResourceWorkingVersion($resource)
	{
		$source=$resource->getDir();
		$details=$resource->getWorkingDetails();
		if ($details->isNew())
		{
			$resource->lockRead();
			LockManager::lockResourceWrite($details->getDir());
			recursiveCopy($source,$details->getDir(),true);
			LockManager::unlockResource($details->getDir());
			$resource->unlock();
			$details->blank=false;
		}

		if ($resource instanceof Page)
		{
			return $this->getPage($resource->id,$details->version);
		}
		else if ($resource instanceof Block)
		{
			return $this->getBlock($resource->id,$details->version);
		}
		else if ($resource instanceof Template)
		{
			return $this->getTemplate($resource->id,$details->version);
		}
	}
	
	function isCurrentResourceVersion($resource)
	{
    if ($this->isVersioned())
    {
    	$current=$this->getCurrentResourceVersion($resource);
    	return ($current->version==$resource->version);
    }
    else
    {
      return true;
    }
	}
	
	function makeCurrentResourceVersion($resource)
	{
		$dir=$this->getResourceBaseDir($resource);

		if (($this->isWritable())&&($this->isVersioned()))
		{
			LockManager::lockResourceWrite($dir);
    	$vers=fopen($dir.'/version','w');
    	fwrite($vers,$resource->version);
    	fclose($vers);
			LockManager::unlockResource($dir);
		}
    else if ($this->isWritable())
    {
      $this->log->warn("Attempt to alter version on an unversioned resource.");
    }
    else
    {
      $this->log->warn("Attempt to alter version on an unwritable resource.");
    }
	}
	
	function getCurrentResourceVersion($resource)
	{
    if ($this->isVersioned())
    {
    	$dir=$this->getResourceBaseDir($resource);
    
    	$version=$this->getCurrentVersion($dir);
    
    	if ($resource instanceof Page)
    	{
    		return $this->getPage($resource->id,$version);
    	}
    	else if ($resource instanceof Block)
    	{
    		return $this->getBlock($resource->id,$version);
    	}
    	else if ($resource instanceof Template)
    	{
    		return $this->getTemplate($resource->id,$version);
    	}
    }
    else
    {
      return $resource;
    }
	}
	
  function isVersioned()
  {
    return $this->versioned;
  }
  
	function isVisible()
	{
		return $this->visible;
	}
	
	function getResourceDir($resource)
	{
		if (($resource instanceof File)||(!$this->isVersioned()))
		{
			return $this->getResourceBaseDir($resource);
		}
		else
		{
			return $this->getResourceBaseDir($resource).'/'.$resource->version;
		}
	}
	
	function loadBlock($id,$version = false)
	{
    $dir = $this->getDir().'/blocks/'.$id;
    if ($this->isVersioned())
    {
      $dir = $dir.'/'.$version;
    }
		$block = loadBlock($dir,$this,$id,$version);
		if (($block!==null)&&($block->exists()))
		{
			return $block;
		}
		return null;
	}
	
	function loadFile($id,$version = false)
	{
		return parent::loadFile($id,false);
	}
	
	function hasResource($type,$id,$version = false)
	{
		$ext=$id;
		if (($version!==false)&&($this->isVersioned()))
		{
			$ext=$id.'/'.$version;
		}
		return is_dir($this->getDir().'/'.$type.'s/'.$ext);
	}
	
	function getResource($type,$id,$version = false)
	{
		if (($version===false)&&(($type=='block')||($type=='page')||($type=='template'))&&($this->isVersioned()))
		{
			$version=$this->getCurrentVersion($this->getDir().'/'.$type.'s/'.$id);
			if ($version===false)
			{
				return null;
			}
		}
		return parent::getResource($type,$id,$version);
	}

	function createNewResource($type, $id=false)
	{
		$this->lockWrite();
		list($id,$rdir)=parent::createNewResource($type,$id);
    if ($this->isVersioned())
    {
    	$version='base';
    	$pdir=$rdir.'/'.$version;
    	$vers=fopen($rdir.'/version','w');
    	fwrite($vers,$version);
    	fclose($vers);
    	mkdir($pdir);
    	$this->unlock();
    }
		return array($id,$pdir);
	}
}

class ContainerAdminSection
{
  private $container;
  
  public function ContainerAdminSection($container)
  {
    $this->container = $container;
  }
  
  public function getName()
  {
    $cat = $this->container->getRootCategory();
    return $cat->name;
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_CONTENT;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->method='view';
    $request->resource='internal/page/site';
    $request->query['container']=$this->container->id;
    return $request->encode();
  }
  
  public function isAvailable()
  {
    global $_USER;

    if (!$_USER->hasPermission('documents',PERMISSION_READ))
      return false;
    return isset($this->container->rootcategory);
  }
  
  public function isSelected($request)
  {
    global $_PREFS;
    
    if ($request->method!='view')
      return false;
   	
   	if ($request->resource != 'internal/page/site')
   		return false;
   		
   	if (!isset($request->query['container']))
   		return $this->container->id == $_PREFS->getPref('container.default');

		return $this->container->id == $request->query['container'];   	
  }
}

function getAllContainers()
{
	global $_PREFS;
	
	$containers=array();
	$list = $_PREFS->getPrefBranch('container');
	foreach (array_keys($list) as $text)
	{
		list($id,$base)=explode('.',$text,2);
		if ($base=='basedir')
		{
			$container=getContainer($id);
			if ($container->isVisible())
				$containers[$id]=getContainer($id);
		}
	}
	return $containers;
}

function getContainer($id)
{
	global $_PREFS;
	
  $container = ObjectCache::getItem('container',$id);
	if (($container===null)&&($_PREFS->isPrefSet('container.'.$id.'.basedir')))
	{
		$container = new Container($id);
    ObjectCache::setItem('container', $id, $container);
	}
	return $container;
}

function containerAdminInit()
{
  global $_STORAGE;
  
  $set=$_STORAGE->query('SELECT id FROM Container;');
  while ($set->valid())
  {
    $details = $set->fetch();
    $container = getContainer($details['id']);
    if ($container!==null)
      AdminManager::addSection(new ContainerAdminSection($container));
  }
}

containerAdminInit();

?>