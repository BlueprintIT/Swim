<?

/*
 * Swim
 *
 * Display functions for parsing
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function displayBlock($tag,$attrs,$text)
{
	global $page;
	
	$block=$page->getBlock($attrs['id']);
	return $block->display($attrs,$text);
}

function displayVar($tag,$attrs,$text)
{
	global $page;
	
	$name=$attrs['name'];
	if (isset($attrs['namespace']))
	{
		$name=$attrs['namespace'].".".$name;
	}
	else
	{
		if (strpos($name,".")===false)
		{
			$name="page.variables.".$name;
		}
	}
	if ($page->prefs->isPrefSet($name))
	{
		print($page->prefs->getPref($name));
	}
}

?>