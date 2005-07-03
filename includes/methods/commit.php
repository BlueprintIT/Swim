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
	
	$resource = &Resource::decodeResource($request);

	if ($resource!==false)
	{
		if ($_USER->canWrite($resource))
		{
			if ($resource->isBlock())
			{
				$oldversion=&$resource;
				$details=$resource->getWorkingDetails();
				if ($details->isMine())
				{
					$workingversion=$resource->makeWorkingVersion();
					$newversion=$workingversion->makeNewVersion();
					$details->free();
	
					if ($oldversion->isCurrentVersion())
					{
						$newversion->makeCurrentVersion();
					}
	
					if (is_a($oldversion->container,'Page'))
					{
						redirect($request->nested);
					}
					else
					{
						$nresource=&Resource::decodeResource($request->nested);
						if ($nresource->isPage())
						{
							$page=&$nresource;
							$usage=$page->getBlockUsage($oldversion);
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
									$newpage=&$page->makeNewVersion();
	
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
						$list=&getAllPages();
						foreach(array_keys($list) as $pkey)
						{
							$page=&$list[$pkey];
							$usage=$page->getBlockUsage($oldversion);
							
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
										$newpage=&$page->makeNewVersion();
	
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
										$pages[]=&$page;
									}
								}
							}
						}
						if (count($pages)>0)
						{
							$request->query['newversion']=$newversion->version;
							$request->query['pages']=&$pages;
							$internal=&getContainer('internal');
							$page=&$internal->getPage('commit');
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
					displayLocked($request,$block->getResource());
				}
			}
			else if ($resource->isPage())
			{
				$newpage=&$resource->makeNewVersion();
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();
				if ($resource->isCurrentVersion())
					$newpage->makeCurrentVersion();
				redirect($request->nested);
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