<?

/*
 * Swim
 *
 * Containers hold the pages, blocks, templates and files that the website can serve
 *
 * Copyright Blueprint IT Ltd. 2005
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
	
	function Container($id)
	{
		global $_PREFS;
		
		$this->log=&LoggerManager::getLogger('swim.container');
		$this->id=$id;
		$this->prefs = new Preferences();
		$this->prefs->setParent($_PREFS);
		$this->dir=$this->prefs->getPref('container.'.$id.'.basedir');
		$this->log->debug('Container '.$id.' is at '.$this->dir);
		if ($this->isFileReadable('resource.conf'))
		{
			$file=$this->openFileRead('resource.conf');
			$this->prefs->loadPreferences($file);
			$this->closeFile($file);
		}
	}
	
	function isVisible()
	{
		return $this->prefs->getPref('container.visible',true);
	}
	
	function isWritable()
	{
		return $this->prefs->getPref('container.writable',true);
	}
	
	function getResourceDir(&$resource)
	{
		if (is_a($resource,'Page'))
		{
			return $this->dir.'/pages/'.$resource->id.'/'.$resource->version;
		}
		if (is_a($resource,'Block'))
		{
			return $this->getBlockDir($resource->id,$resource->version);
		}
		if (is_a($resource,'Template'))
		{
			return $this->dir.'/templates/'.$resource->id.'/'.$resource->version;
		}
		if (is_a($resource,'File'))
		{
			return $this->dir.'/files';
		}
	}
	
	function getBlockDir($id,$version)
	{
		return $this->getDir().'/blocks/'.$id.'/'.$version;
	}
	
	function &getBlock($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getDir().'/blocks/'.$id);
		}
		if (!isset($this->blocks[$id][$version]))
		{
			$block = &loadBlock($this,$id,$version);
			if ($block->exists())
			{
				$this->blocks[$id][$version]=&$block;
			}
			else
			{
				$this->blocks[$id][$version]=false;
			}
		}
		return $this->blocks[$id][$version];
	}

	function &getBlocks()
	{
		$blocks=array();
		$dir=$this->dir.'/blocks';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$block=&$this->getBlock($entry);
				if ($block!==false)
				{
					$blocks[]=&$block;
				}
			}
		}
		closedir($dir);
		return $blocks;
	}
	
	function &getPage($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getDir().'/pages/'.$id);
		}
		if (!isset($this->pages[$id][$version]))
		{
			$page = new Page($this,$id,$version);
			if ($page->exists())
			{
				$this->pages[$id][$version]=&$page;
			}
			else
			{
				$this->pages[$id][$version]=false;
			}
		}
		return $this->pages[$id][$version];
	}
	
	function &getPages()
	{
		$pages=array();
		$dir=$this->dir.'/pages';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$page=&$this->getPage($entry);
				if ($page!==false)
				{
					$pages[]=&$page;
				}
			}
		}
		closedir($dir);
		return $pages;
	}

	function &getTemplate($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getDir().'/templates/'.$id);
		}
		if (!isset($this->templates[$id][$version]))
		{
			$template = new Template($this,$id,$version);
			if ($template->exists())
			{
				$this->templates[$id][$version] = &$template;
			}
			else
			{
				$this->templates[$id][$version]=false;
			}
		}
		return $this->templates[$id][$version];
	}

	function &getTemplates()
	{
		$templates=array();
		$dir=$this->dir.'/templates';
		$dir=opendir($dir);
		while (false !== ($entry=readdir($dir)))
		{
			if ($entry[0]!='.')
			{
				$template=&$this->getTemplate($entry);
				if ($template!==false)
				{
					$templates[]=&$template;
				}
			}
		}
		closedir($dir);
		return $templates;
	}
}

function &getAllContainers()
{
	global $_PREFS;
	
	$containers=array();
	$list = $_PREFS->getPrefBranch('container');
	foreach (array_keys($list) as $text)
	{
		list($id,$base)=explode('.',$text,2);
		if ($base=='basedir')
		{
			$container=&getContainer($id);
			if ($container->isVisible())
				$containers[$id]=&getContainer($id);
		}
	}
	return $containers;
}

?>