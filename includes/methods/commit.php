<?

/*
 * Swim
 *
 * Commits block edits
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

function method_commit(&$request)
{
	global $_USER,$_PREFS;
	
	$resource = Resource::decodeResource($request->resource);

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
				$newversion=cloneVersion($block->getResource());
				freeTempVersion($block->getResource());

				$oldversion=$block->version;
				if ($oldversion==getCurrentVersion($block->getResource()))
				{
					setCurrentVersion($block->getResource(),$newversion);
				}

				if (is_object($block->container))
				{
					redirect($request->nested);
				}
				else
				{
					$pagedata = Resource::decodeResource($request->nested->resource);
					$sourcepage=$pagedata->getPage();
					$stores=$_PREFS->getPrefBranch('storage.pages');
					$pages=array();
					foreach ($stores as $container => $path)
					{
						$dir=opendir($path);
						while (false !== ($entry=readdir($dir)))
						{
							if ($entry[0]!='.')
							{
								if (($container!=$sourcepage->container)||($entry!=$sourcepage->id))
								{
									if (isValidPage($container,$entry))
									{
										$page=&loadPage($container,$entry);
										$blocks=$page->prefs->getPrefBranch('page.blocks');
										foreach ($blocks as $key=>$id)
										{
											if (substr($key,-3,3)=='.id')
											{
												$blk=substr($key,0,-3);
												if (($id==$block->id)&&($page->prefs->getPref('page.blocks.'.$blk.'.container')==$block->container))
												{
													if ($page->prefs->getPref('page.blocks.'.$blk.'.version','-1')==$oldversion)
													{
														print("MATCH");
														$pages[]=&$page;
													}
												}
											}
										}
									}
								}
							}
						}
						closedir($dir);
					}
					
				}
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