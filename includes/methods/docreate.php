<?

/*
 * Swim
 *
 * Resource creation method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_docreate(&$request)
{
	global $_USER,$_PREFS;
	
	list($container,$type)=explode('/',$request->resource);
	
	$container=&getContainer($container);
	if (($container!==false)&&($container->isWritable()))
	{
		// TODO possibly a better security check here Bug 3
		if ($type=='page')
		{
			$container->lockWrite();
			do
			{
				$id=rand(10000,99999);
			} while (is_dir($container->getPageResource($id)));
			$presource=$container->getPageResource($id);
			$result=mkdir($presource);
			$container->unlock();
			if ($result)
			{
				if (isset($request->query['page.template']))
				{
					$template=$request->query['page.template'];
				}
				else
				{
					$template=$_PREFS->getPref('page.template');
				}
				list($tcontainer,$template)=explode('/',$template);
				$tcontainer=&getContainer($tcontainer);
				$template=&$tcontainer->getTemplate($template);

				$newv='1';
				$pdir=$presource.'/'.$newv;
				mkdir($pdir);

				$lock=lockResourceWrite($pdir);
				recursiveCopy($template->dir.'/defaultpage',$pdir,true);
				unlockResource($lock);
				
				$newpage=&$container->getPage($id,$newv);
				
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();
				$newpage->makeCurrentVersion();
				
				$nrequest = new Request();
				$nrequest->method=$request->nested->method;
				$nrequest->resource=$container->id.'/page/'.$id;
				redirect($nrequest);
			}
			else
			{
				displayError();
			}
		}
		else
		{
			displayError();
		}
	}
	else
	{
		displayError();
	}
}


?>