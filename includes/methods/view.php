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
	
	$resource=&Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canRead($resource))
		{
			if ($resource->isFile())
			{
				$file=$resource->getDir().'/'.$resource->path;
				if (is_readable($file))
				{
					$resource->lockRead();
					$stats=stat($file);
					setModifiedDate($stats['mtime']);
					setContentType(determineContentType($file));
					readfile($file);
					$resource->unlock();
				}
				else
				{
					displayError($request);
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
			displayLogin($request);
		}
	}
	else
	{
		displayError($request);
	}
}


?>