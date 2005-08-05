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

function listContainer(&$container,$type=false)
{
	print("\t<container id=\"".$container->id."\">\n");
	
	if (($type==false)||($type=="template"))
	{
		$set=&$container->getResources('template');
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<template id=\"".$item->id."\"");
			print(" version=\"".$item->version."\"");
			print(">\n");
			print("\t\t</template>\n");
		}
	}
	
	if (($type==false)||($type=="page"))
	{
		$set=&$container->getResources('page');
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<page id=\"".$item->id."\"");
			print(" version=\"".$item->version."\"");
			print(" title=\"".htmlspecialchars($item->prefs->getPref('page.variables.title'))."\"");
			print(">\n");
			print("\t\t</page>\n");
		}
	}
	
	if (($type==false)||($type=="block"))
	{
		$set=&$container->getResources('block');
		foreach (array_keys($set) as $k)
		{
			$item=&$set[$k];
			print("\t\t<block id=\"".$item->id."\"");
			print(" type=\"".get_class($item)."\"");
			print(" version=\"".$item->version."\"");
			print(">\n");
			print("\t\t</block>\n");
		}
	}
	print("\t</container>\n");
}

function listDir($path,$name)
{
	global $_PREFS;
	$lockfile=$_PREFS->getPref('locking.lockfile');
	$templockfile=$_PREFS->getPref('locking.templockfile');
	$log=&LoggerManager::getLogger('swim.method.list');
	print('<dir');
	print(' name="'.$name.'"');
	print('>');
	$dir=opendir($path);
	if ($dir!==false)
	{
		while (($file = readdir($dir)) !== false)
		{
			if (($file[0]!='.')&&($file!=$lockfile)&&($file!=$templockfile))
			{
				if (is_dir($path.'/'.$file))
				{
					listDir($path.'/'.$file,$file);
				}
				else
				{
					listFile($path.'/'.$file,$file);
				}
			}
		}
		closedir($dir);
	}
	else
	{
		$log->warn('Could not open directory '.$path);
	}
	print('</dir>');
}

function listFile($path,$name)
{
	$log=&LoggerManager::getLogger('swim.method.list');
	print('<file');
	print(' name="'.$name.'"');
	if (is_readable($path))
	{
		print(' exists="true"');
		$stats=stat($path);
		print(' size="'.$stats['size'].'"');
		print(' modified="'.$stats['mtime'].'"');
		print(' created="'.$stats['ctime'].'"');
	}
	else
	{
		print(' exists="false"');
	}
	print('/>');
}

function method_list(&$request)
{
	$log=&LoggerManager::getLogger('swim.method.list');
	
	setContentType('text/xml');
	if (strlen($request->resource)>0)
	{
		$paths = explode('/',$request->resource);
		$container=&getContainer($paths[0]);
		if (count($paths)==1)
		{
			listContainer($container);
		}
		else if (count($paths)==2)
		{
			listContainer($container,$paths[1]);
		}
		else
		{
			$resource = &Resource::decodeResource($request);
			if ($resource->isFile())
			{
				$path=$resource->getDir().'/'.$resource->id;
			}
			else
			{
				$path=$resource->getDir();
			}
			$name=basename($path);
			if (is_dir($path))
			{
				listDir($path,$name);
			}
			else
			{
				listFile($path,$name);
			}
		}
	}
	else
	{
		print ("<site>\n");
		$containers=&getAllContainers();
		foreach (array_keys($containers) as $id)
		{
			$container=&$containers[$id];
			listContainer($container);
		}
		print ("</site>\n");
	}
}

?>