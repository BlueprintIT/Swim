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
	$iv = $item->getCurrentVersion($variant);
	if ($iv === null)
	{
		$published = 'false';
		$iv = $item->getNewestVersion($variant);
	}
	else
		$published = 'true';
	if ($iv !== null)
	{
		$name = $iv->getField('name');
		print "<item id=\"".$item->getId()."\" class=\"".$item->getClass()->getId()."\" published=\"".$published."\" name=\"".htmlspecialchars($name->toString())."\"";
		$sequence = $iv->getMainSequence();
		if ($sequence !== null)
		{
			$contents = " contains=\"";
			foreach ($sequence->getVisibleClasses() as $class)
			{
				$contents .= $class->getId().",";
			}
			$contents = substr($contents,0,-1);
			print $contents."\">\n";
			foreach ($sequence->getItems() as $subitem)
				displayTreeItem($subitem,$variant);
		}
		else
			print ">\n";
		print "</item>\n";
	}
}

function displayUncategorised($section, $variant)
{
	global $_STORAGE;
	
	print "<item id=\"uncat\" class=\"uncategorised\" name=\"Uncategorised Items\" contains=\"";
	foreach ($section->getVisibleClasses() as $class)
		print $class->getId().",";

	print "\">\n";
	$root = $section->getRootItem();
	$results = $_STORAGE->query('SELECT Item.* FROM Item LEFT JOIN Sequence ON Item.id=Sequence.item WHERE ISNULL(Sequence.Item) AND section="'.$_STORAGE->escape($section->getId()).'" AND id!='.$root->getId().' AND (ISNULL(archived) OR archived<>1);');
	while ($results->valid())
	{
		$details = $results->fetch();
		$item = Item::getItem($details['id'], $results->fetch());
		displayTreeItem($item, $variant);
	}
	print "</item>\n";
}

function method_tree($request)
{
  global $_USER, $_STORAGE, $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.tree');
  checkSecurity($request, true, true);
  
  setContentType("text/xml");
	print "<?xml version=\"1.0\"?>\n\n<tree>\n";
	if ($request->hasQueryVar('root'))
	{
		$item = Item::getItem($request->getQueryVar('root'));
		displayTreeItem($item, Session::getCurrentVariant());
	}
	else if ($request->hasQueryVar('section'))
	{
		$section = SectionManager::getSection($request->getQueryVar('section'));
		$item = $section->getRootItem();
		displayTreeItem($item, Session::getCurrentVariant());
		displayUncategorised($section, Session::getCurrentVariant());
	}
	else if ($request->hasQueryVar('archive'))
	{
		$sections = SectionManager::getSections();
		foreach ($sections as $section)
		{
			print "<item class=\"section\" name=\"".htmlspecialchars($section->getName())."\">\n";
			$results = $_STORAGE->query('SELECT Item.* FROM Item LEFT JOIN Sequence ON Item.id=Sequence.item WHERE ISNULL(Sequence.Item) AND section="'.$_STORAGE->escape($section->getId()).'" AND archived=1;');
			while ($results->valid())
			{
				$details = $results->fetch();
			  $item = Item::getItem($details['id'], $details);
			  displayTreeItem($item, Session::getCurrentVariant());
			}
		}
	}
	else
	{
		$sections = SectionManager::getSections();
		foreach ($sections as $section)
		{
			print "<item class=\"section\" name=\"".htmlspecialchars($section->getName())."\">\n";
			$item = $section->getRootItem();
			displayTreeItem($item, Session::getCurrentVariant());
			displayUncategorised($section, Session::getCurrentVariant());
			print "</item>\n";
		}
	}
	print "</tree>\n";
}

?>