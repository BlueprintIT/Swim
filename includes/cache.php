<?

/*
 * Swim
 *
 * Keeps a cache of big objects we create
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$_TEMPLATES = array();
$_PAGES = array();

function &loadBlock($container,$id,$version=false)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger('swim.cache');
	
	$log->debug('Loading block');
	if (is_object($container))
	{
		$log->debug('Block is page specific');
		$blockdir=$container->getDir().'/blocks/'.$id;
		$version=$container->version;
	}
	else
	{
		$resource=$_PREFS->getPref('storage.blocks.'.$container).'/'.$id;
		if ($version===false)
		{
			$version=getCurrentVersion($resource);
		}
		$blockdir=getResourceVersion($resource,$version);
	}
	$log->debug('Loading block from '.$blockdir.' - version '.$version);
	$lock=lockResourceRead($blockdir);

	$blockprefs = new Preferences();
	$blockprefs->setParent($_PREFS);
	if (is_readable($blockdir.'/block.conf'))
	{
		$blockprefs->loadPreferences($blockdir.'/block.conf','block');
	}
	$class=$blockprefs->getPref('block.class');
	if ($blockprefs->isPrefSet('block.classfile'))
	{
		require_once $blockprefs->getPref('storage.blocks.classes').'/'.$blockprefs->getPref('block.classfile');
	}
	else if (is_readable($blockdir.'/block.class'))
	{
		require_once $blockdir.'/block.class';
	}

	unlockResource($lock);

	if (class_exists($class))
	{
		$log->debug('Block loaded');
		$object = new $class();
		$object->setContainer($container);
		$object->setID($id);
		$object->setVersion($version);
		$object->prefs = $blockprefs;
		$object->blockInit();
		$object->init();
		return $object;
	}
	else
	{
		trigger_error('Invalid block found');
	}
}

function &loadTemplate($name,$version=false)
{
	global $_TEMPLATES;
	
	if (isset($_TEMPLATES[$name]))
	{
		return $_TEMPLATES[$name];
	}
	$template = new Template('templates',$name);
	$_TEMPLATES[$name]=&$template;
	return $template;
}

function &loadPage($container,$id,$version=false)
{
	global $_PREFS,$_PAGES;

	if ($version===false)
	{
		$version=getCurrentVersion($_PREFS->getPref('storage.pages.'.$container).'/'.$id);
	}
	if (!isset($_PAGES[$id][$version]))
	{
		if (isValidPage($container,$id,$version))
		{
			$_PAGES[$container][$id][$version] = new Page($container,$id,$version);
		}
		else
		{
			$_PAGES[$container][$id][$version]=false;
		}
	}
	return $_PAGES[$container][$id][$version];
}

 ?>