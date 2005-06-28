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

				if (is_a($block->container,'Page'))
				{
					redirect($request->nested);
				}
				else
				{
					$nresource=&Resource::decodeResource($request->nested);
					if (isset($nresource->page))
					{
						$page=&$nresource->getPage();
						$usage=$page->getBlockUsage($block);
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
								$newv=cloneVersion($page->getResource(),$page->version);
								$newpage=&$page->container->getPage($page->id,$newv);

								foreach ($usage as $id)
								{
									if ($newpage->prefs->isPrefSet('page.blocks.'.$id.'.version'))
									{
										$newpage->prefs->setPref('page.blocks.'.$id.'.version',$newversion);
									}
								}

								$newpage->savePreferences();
								if (getCurrentVersion($page->getResource())==$page->version)
									setCurrentVersion($newpage->getResource(),$newv);
							}
						}
					}

					$autocommit=$_PREFS->getPref('update.autocommit',false);
					$list=&getAllPages();
					foreach(array_keys($list) as $pkey)
					{
						$page=&$list[$pkey];
						$usage=$page->getBlockUsage($block);
						
						$blocks=$page->prefs->getPrefBranch('page.blocks');
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
									$newv=cloneVersion($page->getResource(),$page->version);
									$newpage=&$page->container->getPage($page->id,$newv);

									foreach ($usage as $id)
									{
										if ($newpage->prefs->isPrefSet('page.blocks.'.$id.'.version'))
										{
											$newpage->prefs->setPref('page.blocks.'.$id.'.version',$newversion);
										}
									}

									$newpage->savePreferences();
									setCurrentVersion($newpage->getResource(),$newv);
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
						$request->query['newversion']=$newversion;
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
			else if ($resource->isPage())
			{
				$page=$resource->getPage();
				$newv=cloneVersion($page->getResource(),$page->version);
				$newpage=&$page->container->getPage($page->id,$newv);
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();
				if ($page->version==getCurrentVersion($page->getResource()))
					setCurrentVersion($newpage->getResource(),$newv);
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