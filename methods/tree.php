<?

/*
 * Swim
 *
 * Generates the site details tree
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function displayTreeItem($item, $variant)
{
	global $_STORAGE;
	
	$results = $_STORAGE->query('SELECT VariantVersion.current AS published,Field.textValue AS name FROM (ItemVariant JOIN VariantVersion ON ItemVariant.id=VariantVersion.itemvariant) LEFT JOIN Field ON VariantVersion.id=Field.itemversion WHERE ItemVariant.item='.$item->getId().' AND ItemVariant.variant="default" AND Field.field="name" ORDER BY VariantVersion.current DESC, VariantVersion.version DESC;');
	if ($results->valid())
	{
		$details = $results->fetch();
		if ($details['published']==1)
			$published = 'true';
		else
			$published = 'false';
		$name = $details['name'];

		print "{ \"id\": \"".$item->getId()."\", \"class\": \"".$item->getClass()->getId()."\", \"published\": \"".$published."\", \"name\": \"".addslashes($name)."\"";
		$sequence = $item->getMainSequence();
		if ($sequence !== null)
		{
			$contents = ", \"contains\": \"";
			foreach ($sequence->getVisibleClasses() as $class)
			{
				$contents .= $class->getId().",";
			}
			$contents = substr($contents,0,-1);
			print $contents."\", \"subitems\": [";
			$i = 0;
			$items = $sequence->getItems();
			foreach ($items as $subitem)
			{
				$i++;
				displayTreeItem($subitem,$variant);
				if ($i<count($items))
					print ", ";
			}
			print "]}";
		}
		else
			print ", \"contains\": \"\", \"subitems\": []}";
	}
}

function displayUncategorised($section, $variant)
{
	global $_STORAGE;
	
	print "{ \"id\": \"uncat\", \"class\": \"uncategorised\", \"name\": \"Uncategorised Items\", \"contains\": \"";
	foreach ($section->getVisibleClasses() as $class)
		print $class->getId().",";
	print "\", \"subitems\": [";
	$root = $section->getRootItem();
	$results = $_STORAGE->query('SELECT Item.* FROM Item LEFT JOIN Sequence ON Item.id=Sequence.item WHERE ISNULL(Sequence.Item) AND section="'.$_STORAGE->escape($section->getId()).'" AND id!='.$root->getId().' AND (ISNULL(archived) OR archived<>1);');
	while ($results->valid())
	{
		$details = $results->fetch();
		$item = Item::getItem($details['id'], $results->fetch());
		displayTreeItem($item, $variant);
		if ($results->valid())
			print ", ";
	}
	print "]}";
}

function displaySection($section, $variant)
{
	print "[";
	$item = $section->getRootItem();
	displayTreeItem($item, Session::getCurrentVariant());
	print ",";
	displayUncategorised($section, Session::getCurrentVariant());
	print "]";
}

function displayArchive($variant)
{
	global $_STORAGE;
	
	print "[";
	$i = 0;
	$sections = SectionManager::getSections();
	foreach ($sections as $section)
	{
		$i++;
		print "{ \"class\": \"section\", \"name\": \"".addslashes($section->getName())."\", \"subitems\": [";
		$results = $_STORAGE->query('SELECT Item.* FROM Item LEFT JOIN Sequence ON Item.id=Sequence.item WHERE ISNULL(Sequence.Item) AND section="'.$_STORAGE->escape($section->getId()).'" AND archived=1;');
		if ($results->valid())
		{
			while ($results->valid())
			{
				$details = $results->fetch();
			  $item = Item::getItem($details['id'], $details);
			  displayTreeItem($item, $variant);
			  if ($results->valid())
			  	print ", ";
			}
		}
		print "]}";
		if ($i<count($sections))
			print ", ";
	}
	print "]";
}

function displayAllSections($variant)
{
	print "[";
	$sections = SectionManager::getSections();
	$i = 0;
	foreach ($sections as $section)
	{
		$i++;
		print "{ \"class\": \"section\", \"name\": \"".addslashes($section->getName())."\", \"subitems\": ";
		displaySection($section, $variant);
		print "}";
		if ($i<count($sections))
			print ",";
	}
	print "]";
}

function method_tree($request)
{
  $log = LoggerManager::getLogger('swim.method.tree');
  checkSecurity($request, true, true);
  
  setContentType("text/plain");
	if ($request->hasQueryVar('root'))
	{
		$item = Item::getItem($request->getQueryVar('root'));
		displayTreeItem($item, Session::getCurrentVariant());
	}
	else if ($request->hasQueryVar('section'))
	{
		$section = SectionManager::getSection($request->getQueryVar('section'));
		displaySection($section, Session::getCurrentVariant());
	}
	else if ($request->hasQueryVar('archive'))
	{
		displayArchive(Session::getCurrentVariant());
	}
	else
	{
		displayAllSections(Session::getCurrentVariant());
	}
}

?>