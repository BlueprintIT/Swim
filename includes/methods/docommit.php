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

function method_docommit(&$request)
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
				$oldversion=$block->version;
				$newversion=$request->query['newversion'];
				if (isset($request->query['commit']))
				{
					$commits=&$request->query['commit'];
					if (count($commits)>0)
					{
						$containers=$request->query['container'];
						$ids=$request->query['id'];
						$versions=$request->query['ver'];
						foreach ($commits as $key=>$value)
						{
							if ($value)
							{
								$container=&getContainer($containers[$key]);
								$page=&$container->getPage($ids[$key],$versions[$key]);
								$newv=cloneVersion($page->getResource(),$page->version);
								$newpage=&$container->getPage($page->id,$newv);

								$blocks=$newpage->prefs->getPrefBranch('page.blocks');
								foreach ($blocks as $key=>$id)
								{
									if (substr($key,-3,3)=='.id')
									{
										$blk=substr($key,0,-3);
										if (($id==$block->id)&&($newpage->prefs->getPref('page.blocks.'.$blk.'.container')==$block->container->id))
										{
											if ($newpage->prefs->getPref('page.blocks.'.$blk.'.version','-1')==$oldversion)
											{
												$newpage->prefs->setPref('page.blocks.'.$blk.'.version',$newversion);
											}
										}
									}
								}

								$newpage->savePreferences();
								setCurrentVersion($newpage->getResource(),$newv);
							}
						}
					}
				}
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