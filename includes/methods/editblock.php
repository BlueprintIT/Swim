<?

/*
 * Swim
 *
 * Block editing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_editblock(&$request)
{
	global $_PREFS;
	
	list($container,$block)=explode('/',$request->resource,2);
	
	if ($container=='page')
	{
		list($page,$block)=explode('/',$block,2);
		$resource=$_PREFS->getPref('storage.pages.global').'/'.$page;
		$blockrel='/blocks/'.$block;
	}
	else
	{
		$resource=$_PREFS->getPref('storage.blocks.'.$container).'/'.$block;
		$blockrel='';
	}
	cloneTemp($resource,getCurrentVersion($resource));
}


?>