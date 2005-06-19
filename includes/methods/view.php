<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function displayFile(&$request,$dir,$file)
{
	$file=$dir.'/'.$file;
	if (is_readable($file))
	{
		$stats=stat($file);
		setModifiedDate($stats['mtime']);
		setContentType(determineContentType($file));
		readfile($file);
	}
	else
	{
		displayError($request);
	}
}

function method_view(&$request)
{
	$data=decodeResource($request->resource);
	$version=false;
	
	if ($data!==false)
	{
		if (isset($data['file']))
		{
			if ($data['type']=='page')
			{
				$page=&loadPage($data['container'],$data['page']);
				displayFile($request,$page->getDir(),$data['file']);
			}
			else if ($data['type']=='template')
			{
				$template = &loadTemplate($data['template']);
				displayFile($request,$template->dir,$data['file']);
			}
			else if ($data['type']=='block')
			{
				if (isset($data['page']))
				{
					$page=&loadPage($data['container'],$data['page']);
					$block=&$page->getBlock($data['block']);
				}
				else
				{
					$blockdir=getCurrentResource($_PREFS->getPref('storage.blocks.'.$data['container']).'/'.$data['block']);
					$block=&loadBlock($data['block'],$blockdir);
				}
				displayFile($request,$block->dir,$data['file']);
			}
		}
		else if ($data['type']=='page')
		{
			if (isValidPage('global',$request->resource,$version))
			{
				$page = &loadPage('global',$request->resource,$version);
				$page->display($request);
			}
		}
		else
		{
			displayError($request);
		}
	}
	else
	{
		displayError($request);
	}
}


?>