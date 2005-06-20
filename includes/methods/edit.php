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

function displayLocked(&$request,$resource)
{
}

function method_edit(&$request)
{
	global $_USER;
	
	$resource = Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->type=='block')
			{
				$block=&$resource->getBlock();
				$temp=getTempVersion($block->getResource());
				if ($temp===false)
				{
					displayLocked($request,$block->getResource());
					return;
				}
				if (is_object($block->container))
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
				
				if (is_object($block->container))
				{
					$container=&loadPage($block->container->container,$block->container->id,$temp);
				}
				else
				{
					$container=$block->container;
				}
				
				$page=&$block->getBlockEditor();
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