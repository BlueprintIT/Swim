<?

/*
 * Swim
 *
 * Commits block edits
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_docommit($request)
{
	global $_USER,$_PREFS;
	
  checkSecurity($request, true, true);
  
	$resource = Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$block=$resource;
				$oldversion=$block->version;
				$newversion=$request->query['newversion'];
				if (isset($request->query['commit']))
				{
					$commits=$request->query['commit'];
					if (count($commits)>0)
					{
						$containers=$request->query['container'];
						$ids=$request->query['id'];
						$versions=$request->query['ver'];
						foreach ($commits as $key=>$value)
						{
							if ($value)
							{
								$container=getContainer($containers[$key]);
								$page=$container->getPage($ids[$key],$versions[$key]);
								$newpage=$page->makeNewVersion();

								$usage=$newpage->getReferencedBlockUsage($oldversion);
								
								if (count($usage)>0)
								{
									foreach ($usage as $id)
									{
										if ($newpage->prefs->isPrefSet('page.blocks.'.$id.'.version'))
										{
											$newpage->prefs->setPref('page.blocks.'.$id.'.version',$newversion);
										}
									}
								}

								$newpage->savePreferences();
								$newpage->makeCurrentVersion();
							}
						}
					}
				}
				redirect($request->nested);
			}
			else
			{
				displayGeneralError($request,'docommit expects a block.');
			}
		}
		else
		{
			displayAdminLogin($request);
		}
	}
	else
	{
		displayNotFound($request);
	}
}

?>