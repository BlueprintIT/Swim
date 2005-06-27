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

$_CONTAINERS = array();

function &getContainer($id)
{
	global $_CONTAINERS,$_PREFS;
	
	if (!isset($_CONTAINERS[$id]))
	{
		if ($_PREFS->isPrefSet('container.'.$id.'.basedir'))
		{
			$_CONTAINERS[$id] = new Container($id);
		}
		else
		{
			$_CONTAINERS[$id]=null;
		}
	}
	return $_CONTAINERS[$id];
}

?>