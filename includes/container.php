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

class Container
{
	var $prefs;
	var $id;
	var $dir;
	
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
	}
	
	function getBlockResource($id)
	{
		return $this->dir.'/blocks/'.$id;
	}
	
	function isBlock($id,$version)
	{
		$dir=$this->getBlockResource($id);
		$dir=getResourceVersion($dir,$version);
		if ((is_dir($dir))&&(is_readable($dir.'/block.conf')))
			return true;
		else
			return false;
	}
	
	function &getBlock($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getPageResource($id));
		}
		if (!isset($this->blocks[$id][$version]))
		{
			if ($this->isBlock($id,$version))
			{
				$dir=$this->getBlockResource($id);
				$dir=getResourceVersion($dir,$version);
				$this->blocks[$id][$version]=&loadBlock($dir,$this,$id,$version);
			}
			else
			{
				$this->blocks[$id][$version]=false;
			}
		}
		return $this->blocks[$id][$version];
	}
	
	function getPageResource($id)
	{
		return $this->dir.'/pages/'.$id;
	}
	
	function isPage($id,$version=false)
	{
		$dir=$this->getPageResource($id);
		if ($version===false)
		{
			$dir=getCurrentResource($dir);
		}
		else
		{
			$dir=getResourceVersion($dir,$version);
		}
		if ((is_dir($dir))&&(is_readable($dir.'/page.conf')))
			return true;
		else
			return false;
	}
	
	function &getPage($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getPageResource($id));
		}
		if (!isset($this->pages[$id][$version]))
		{
			if ($this->isPage($id,$version))
			{
				$this->pages[$id][$version] = new Page($this,$id,$version);
			}
			else
			{
				$this->pages[$id][$version]=false;
			}
		}
		return $this->pages[$id][$version];
	}
	
	function getTemplateResource($id)
	{
		return $this->dir.'/templates/'.$id;
	}
	
	function isTemplate($id,$version)
	{
		$dir=$this->getTemplateResource($id);
		$dir=getResourceVersion($dir,$version);
		if (is_dir($dir))
			return true;
		else
			return false;
	}
	
	function &getTemplate($id,$version=false)
	{
		if ($version===false)
		{
			$version=getCurrentVersion($this->getTemplateResource($id));
		}
		if (!isset($this->templates[$id][$version]))
		{
			if ($this->isTemplate($id,$version))
			{
				$this->templates[$id][$version] = new Template($this,$id,$version);
			}
			else
			{
				$this->templates[$id][$version]=false;
			}
		}
		return $this->templates[$id][$version];
	}
}

?>