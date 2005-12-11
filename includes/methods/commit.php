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

function method_commit($request)
{
	global $_USER,$_PREFS;
	
	$resource = Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			$oldversion=$resource;
			$details=$resource->getWorkingDetails();

			if ((!$details->isMine())&&(isset($request->query['forcelock'])))
			{
				if ($request->query['forcelock']=='continue')
				{
					$details->takeOver();
				}
				else if ($request->query['forcelock']=='discard')
				{
					$details->takeOverClean();
				}
			}
			
			if ($details->isMine())
			{
				$workingversion=$resource->makeWorkingVersion();
				$newversion=$workingversion->makeNewVersion();
				$details->free();

				$newversion->makeCurrentVersion();
	
        if ($resource->isBlock())
        {
					if (!isset($oldversion->parent))
					{
						redirect($request->nested);
					}
					else
					{
						$nresource=Resource::decodeResource($request->nested);
						if ($nresource->isPage())
						{
							$page=$nresource;
							$usage=$page->getReferencedBlockUsage($oldversion);
							if (count($usage)>0)
							{
								$clone=false;
								foreach ($usage as $id)
								{
									if ($page->prefs->isPrefSet('page.blocks.'.$id.'.version'))
									{
										$clone=true;
										break;
									}
								}
								if ($clone)
								{
									$newpage=$page->makeNewVersion();
	
									foreach ($usage as $id)
									{
										if ($newpage->prefs->isPrefSet('page.blocks.'.$id.'.version'))
										{
											$newpage->prefs->setPref('page.blocks.'.$id.'.version',$newversion);
										}
									}
	
									$newpage->savePreferences();
									if ($page->isCurrentVersion())
										$newpage->makeCurrentVersion();
								}
							}
						}
	
						$autocommit=$_PREFS->getPref('update.autocommit',false);
						$list=getAllPages();
						$pages=array();
						foreach($list as $page)
						{
							$usage=$page->getReferencedBlockUsage($oldversion);
							
							if (count($usage)>0)
							{
								$clone=false;
								foreach ($usage as $id)
								{
									if ($page->prefs->isPrefSet('page.blocks.'.$id.'.version'))
									{
										$clone=true;
										break;
									}
								}
								if ($clone)
								{
									if ($autocommit)
									{
										$newpage=$page->makeNewVersion();
	
										foreach ($usage as $id)
										{
											if ($newpage->prefs->isPrefSet('page.blocks.'.$id.'.version'))
											{
												$newpage->prefs->setPref('page.blocks.'.$id.'.version',$newversion);
											}
										}
	
										$newpage->savePreferences();
										$newpage->makeCurrentVersion();
									}
									else
									{
										$pages[]=$page;
									}
								}
							}
						}
						if (count($pages)>0)
						{
							$request->data['newversion']=$newversion->version;
							$request->data['pages']=&$pages;
							$internal=getContainer('internal');
							$page=$internal->getPage('commit');
							$page->display($request);
						}
						else
						{
							redirect($request->nested);
						}
					}
        }
        else
        {
          redirect($request->nested);
        }
			}
			else
			{
				displayLocked($request,$details,$resource);
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