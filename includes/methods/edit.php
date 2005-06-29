<?

/*
 * Swim
 *
 * Resource editing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// TODO fix this - Bug 8
function displayLocked(&$request,$resource)
{
}

function method_edit(&$request)
{
	global $_USER;
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$block=&$resource->getBlock();
				$temp=getTempVersion($block->getResource());
				if ($temp===false)
				{
					displayLocked($request,$block->getResource());
					return;
				}
				if (is_a($block->container,'Page'))
				{
					$blockdir='/blocks/'.$resource->block;
				}
				else
				{
					$blockdir='';
				}
				if (!is_readable($block->getResource().'/'.$temp.$blockdir.'/block.conf'))
				{
					cloneTemp($block->getResource(),$resource->version);
				}
				
				$page=&$block->getBlockEditor();
				$page->display($request);
			}
			else if ($resource->isPage())
			{
				$container=&getContainer('internal');
				$page=&$container->getPage('pageedit');
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