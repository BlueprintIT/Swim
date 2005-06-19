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


function method_view(&$request)
{
	global $_USER;
	
	$resource=&Resource::decodeResource($request->resource);

	if ($resource!==false)
	{
		if ($resource->isFile())
		{
			if ($_USER->canRead($resource))
			{
				$file=$resource->dir.'/'.$resource->path;
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
			else
			{
				displayLogin($request);
			}
		}
		else if ($resource->isPage())
		{
			$page = &$resource->getPage();
			$page->display($request);
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