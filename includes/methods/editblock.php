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
	$temp=getTempVersion($resource);
	$blockdir=$resource.'/'.$temp.$blockrel;
	if (!is_readable($blockdir.'/block.conf'))
	{
		cloneTemp($resource,getCurrentVersion($resource));
	}
	$block=&loadBlock('content',$blockdir);
	$block->setContainer($container);
	$block=&$block->getBlockEditor();
	$page=&loadPage('internal','admin');
	$page->setBlock('content',$block);
	$page->display($request);
}


?>