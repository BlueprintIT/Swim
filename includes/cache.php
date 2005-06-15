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

function &loadBlock($block,$blockdir)
{
	global $_PREFS;
	
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
	if (class_exists($class))
	{
		$object = new $class($block,$blockdir);
		$object->prefs = $blockprefs;
		return $object;
	}
	else
	{
		trigger_error('Invalid block found');
	}
}

function &loadTemplate($name)
{
	global $_TEMPLATES;
	
	if (isset($_TEMPLATES[$name]))
	{
		return $_TEMPLATES[$name];
	}
	$template = new Template($name);
	$_TEMPLATES[$name]=&$template;
	return $template;
}

function &loadPage($id,$version)
{
	global $_PREFS,$_PAGES;

	if (!isset($version))
	{
		$version=getCurrentVersion($_PREFS->getPref('storage.pages').'/'.$id);
	}
	if (!isset($_PAGES[$id][$version]))
	{
		$_PAGES[$id][$version] = new Page($id,$version);
	}
	return $_PAGES[$id][$version];
}

 ?>