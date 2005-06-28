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
		// TODO possibly a better security check here
		if ($type=='page')
		{
			$container->lockWrite();
			$dir=$container->getPageDir();
			do
			{
				$id=rand(10000,99999);
			} while (is_dir($dir.'/'.$id));
			$pdir=$dir.'/'.$id;
			$result=mkdir($pdir);
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

				$newv=createNextVersion($pdir);
				$pdir=getResourceVersion($pdir,$newv);

				$lock=resourceLockWrite($pdir);
				recursiveCopy($template->dir.'/defaultpage',$pdir,true);
				resourceUnlock($lock);
				
				$newpage=&$container->getPage($id);
				
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,5)=='page.')
					{
						$newpage->prefs->setPref($name,$value);
					}
				}
				$newpage->savePreferences();

				setCurrentVersion($newpage->getResource(),$newpage->version);
				
				$request = new Request();
				$nrequest->method=$request->nested->method;
				$nrequest=$container->id.'/page/'.$id;
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