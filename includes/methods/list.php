<?

/*
 * Swim
 *
 * Lists pages and blocks available at the site
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_list(&$request)
{
	$log=&LoggerManager::getLogger('swim.method.list');
	
	setContentType('text/xml');
	print ("<site>\n");
	$containers=&getAllContainers();
	foreach (array_keys($containers) as $id)
	{
		print("\t<container id=\"".$id."\">\n");
		$container=&$containers[$id];
		
		$set=&$container->getTemplates();
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<template id=\"".$item->id."\"");
			print(" version=\"".$item->version."\"");
			print(">\n");
			print("\t\t</template>\n");
		}
		
		$set=&$container->getPages();
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<page id=\"".$item->id."\"");
			print(" version=\"".$item->version."\"");
			print(" title=\"".$item->prefs->getPref('page.variables.title')."\"");
			print(">\n");
			print("\t\t</page>\n");
		}
		
		$set=&$container->getBlocks();
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<block id=\"".$item->id."\"");
			print(" type=\"".get_class($item)."\"");
			print(" version=\"".$item->version."\"");
			print(">\n");
			print("\t\t</block>\n");
		}
		print("\t</container>\n");
	}
	print ("</site>\n");
}

?>