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
					if (isset($resource->page))
					{
						$page=&$resource->getPage();
						$newv=cloneVersion($page->getResource(),$page->version);
						$newpage=&loadPage($page->container,$page->id,$newv);
						$newpage->prefs->setPref('page.blocks.'.$resource->block.'.version',$newversion);
						$newpage->savePreferences();
						if (getCurrentVersion($page->getResource())==$page->version)
							setCurrentVersion($newpage->getResource(),$newv);
					}
					$stores=$_PREFS->getPrefBranch('storage.pages');
					$pages=array();
					$autocommit=$_PREFS->getPref('update.autocommit',false);
					foreach ($stores as $container => $path)
					{
						$dir=opendir($path);
						while (false !== ($entry=readdir($dir)))
						{
							if ($entry[0]!='.')
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
													if ($autocommit)
													{
														$newv=cloneVersion($page->getResource(),$page->version);
														$newpage=&loadPage($page->container,$page->id,$newv);
														$newpage->prefs->setPref('page.blocks.'.$blk.'.version',$newversion);
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
									}
								}
							}
						}
						closedir($dir);
					}
					if (count($pages)>0)
					{
						$request->query['newversion']=$newversion;
						$request->query['pages']=&$pages;
						$page=&loadPage('internal','commit');
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