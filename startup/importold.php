<?

/*
 * Swim
 *
 * Automated tasks designed for running from a cron job
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

require('startup.php');

LoggerManager::setLogOutput('',new StdOutLogOutput());

SwimEngine::ensureStarted();

setContentType('text/plain');

function importStructure($section, $old, $category)
{
	global $OLDSTORAGE,$pagemap;
	
	$log = LoggerManager::getLogger('structureimport');
	
	$items = array();
	
	$results = $OLDSTORAGE->query('SELECT * FROM Category WHERE parent='.$old.';');
	while ($results->valid())
	{
		$details = $results->fetch();
		$item = Item::createItem($section, FieldSetManager::getClass('category'));
		$variant = $item->createVariant('default');
		$version = $variant->createNewVersion();
		$field = $version->getField('name');
		$field->setValue($details['name']);
		$version->setComplete(true);
		$version->setCurrent(true);
		importStructure($section, $details['id'], $item);
		$items[$details['sortkey']] = $item;
	}

	$results = $OLDSTORAGE->query('SELECT * FROM PageCategory WHERE category='.$old.';');
	while ($results->valid())
	{
		$details = $results->fetch();
		$id = basename($details['page']);
		if (isset($pagemap[$id]))
			$items[$details['sortkey']] = $pagemap[$id];
		else
			$log->warn('Missing page '.$id);
	}
	
	$results = $OLDSTORAGE->query('SELECT * FROM LinkCategory WHERE category='.$old.';');
	while ($results->valid())
	{
		$details = $results->fetch();
		$item = Item::createItem($section, FieldSetManager::getClass('link'));
		$variant = $item->createVariant('default');
		$version = $variant->createNewVersion();
		$field = $version->getField('name');
		$field->setValue($details['name']);
		$field = $version->getField('link');
		$field->setValue($details['link']);
		$version->updateModified();
		$version->setComplete(true);
		$version->setCurrent(true);
		$items[$details['sortkey']] = $item;
	}
	
	ksort($items);
	$sequence = $category->getMainSequence();
	foreach($items as $pos => $item)
	{
		$sequence->appendItem($item);
	}
}

function importPageVersion($id, $variant, $basedir)
{
	global $_STORAGE, $pagemap;
	
	$log = LoggerManager::getLogger('pageimport');
	
	$version = $variant->createNewVersion();
	$prefs = new Preferences();
	if (is_file($basedir.'/resource.conf'))
	{
		$file = fopen($basedir.'/resource.conf','r');
		$prefs->loadPreferences($file);
		fclose($file);
	}
	$vars = $prefs->getPrefBranch('page.variables');
	foreach ($vars as $name => $value)
	{
		if ($name == 'title')
			$name = 'name';
			
		if ($version->hasField($name))
		{
			$field = $version->getField($name);
			$field->setValue($value);
		}
		else
		{
			$log->warn('Attempt to import unknown field '.$name);
		}
	}
	
	$dir = opendir($basedir.'/blocks');
	while (($file = readdir($dir)) !== false)
	{
		if ((is_dir($basedir.'/blocks/'.$file)) && ($file != '.') && ($file != '..'))
		{
			if ($version->hasField($file))
			{
				$field = $version->getField($file);
				switch ($field->getType())
				{
					case 'html':
						if (is_file($basedir.'/blocks/'.$file.'/block.html'))
						{
							$html = file_get_contents($basedir.'/blocks/'.$file.'/block.html');
							$html = str_replace('/global/page/'.$id.'/file/attachments/', $version->getStorageUrl().'/', $html);
							foreach ($pagemap as $id => $page)
							{
					      $req = new Request();
					      $req->setMethod('view');
					      $req->setPath($page->getId());
								$html = str_replace('/global/page/'.$id, $req->encode(), $html);
							}
							$field->setValue($html);
						}
						break;
					default:
						$log->warn('Attempt to import unknown field type '.$field->getType());
				}
			}
			else
			{
				$log->warn('Attempt to import unknown field '.$file);
			}
		}
	}
	closedir($dir);
	
	if (is_dir($basedir.'/attachments'))
	{
		$fileprefs = new Preferences();
		if (is_file($basedir.'/attachments/.descriptions'))
		{
			$file = fopen($basedir.'/attachments/.descriptions','r');
			$fileprefs->loadPreferences($file);
			fclose($file);
		}
		
		$dir = opendir($basedir.'/attachments');
		while (($file = readdir($dir)) !== false)
		{
			if ((is_file($basedir.'/attachments/'.$file)) && ($file != '.descriptions'))
			{
				if ($fileprefs->isPrefSet($file))
					$description = $fileprefs->getPref($file);
				else
					$description = '';
				
				recursiveMkDir($version->getStoragePath());
				copy($basedir.'/attachments/'.$file, $version->getStoragePath().'/'.$file);
		  	$_STORAGE->queryExec('INSERT INTO File (itemversion,file,description) VALUES ('.$version->getId().',"'.$_STORAGE->escape($file).'","'.$description.'");');
			}
		}
		closedir($dir);
	}
	
	$version->updateModified($prefs->getPref('resource.modified'));
	$version->setComplete(true);
}

function importPage($id, $page, $basedir)
{
	$variant = $page->createVariant('default');
	$version = 1;
	while (is_dir($basedir.'/'.$version))
	{
		importPageVersion($id, $variant, $basedir.'/'.$version);
		$version++;
	}
	return $page;
}

function initialImportNamespace($namespace, $section, $basedir)
{
	global $pagemap;
	
	$section = FieldSetManager::getSection($section);
	
	$dir = opendir($basedir.'/pages');
	while (($file = readdir($dir)) !== false)
	{
		if ((is_dir($basedir.'/pages/'.$file)) && ($file != '.') && ($file != '..'))
		{
			$item = Item::createItem($section, FieldSetManager::getClass('page'));
			$pagemap[$file] = $item;
		}
	}
	closedir($dir);
}

function importNamespace($namespace, $section, $basedir)
{
	global $OLDSTORAGE, $pagemap;
	
	$section = FieldSetManager::getSection($section);
	
	$dir = opendir($basedir.'/pages');
	while (($file = readdir($dir)) !== false)
	{
		if ((is_dir($basedir.'/pages/'.$file)) && ($file != '.') && ($file != '..'))
		{
			$item = $pagemap[$file];
			importPage($file, $item, $basedir.'/pages/'.$file);
			$version = file_get_contents($basedir.'/pages/'.$file.'/version');
			$current = $item->getVariant("default")->getVersion($version);
			$current->setCurrent(true);
		}
	}
	closedir($dir);
	
	$results = $OLDSTORAGE->query('SELECT rootcategory FROM Namespace WHERE name="'.$OLDSTORAGE->escape($namespace).'";');
	importStructure($section, $results->fetchSingle(), $section->getRootItem());
}

$OLDSTORAGE = new SqliteStorage($_PREFS->getPref('storage.config').'/storage.db');
$pagemap = array();

initialImportNamespace("website", "content", $_PREFS->getPref('storage.sitedir').'/content');
importNamespace("website", "content", $_PREFS->getPref('storage.sitedir').'/content');


?>
