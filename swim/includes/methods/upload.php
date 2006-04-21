<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function method_upload($request)
{
	global $_USER;
	
  $log=LoggerManager::getLogger("swim.method.upload");

	$resource=$request->resource;
  
  $log->debug('upload');
  
	if ($resource!==null)
	{
    if ($resource->isFile())
		{
			$log->debug('Checking file write access');
			if ($_USER->canWrite($resource))
			{
				if (!($resource->parent instanceof Container))
				{
					$log->debug('Checking that this is the working version');
					if ($resource->version=='temp')
					{
						$log->debug('Checking that we have the working lock');
						$details=$resource->getWorkingDetails();
	
						if (!$details->isMine())
						{
							header($_SERVER["SERVER_PROTOCOL"]." 409 Conflict");
							print("Someone else has locked this resource.");
              $log->debug('Working file not mine');
							return;
						}
					}
					else
					{
						displayGeneralError($request,'Can only upload to the working copy of this resource');
						return;
					}
				}
				$log->debug('Preparing to write file '.$resource->getDir().'/'.$resource->id);
				if (isset($_FILES['content']))
				{
				}
				else if (isset($request->query['content']))
				{
					$file=$resource->openFileWrite();
					fwrite($file,$request->query['content']);
					$resource->closeFile($file);
				}
				else
				{
					displayGeneralError($request,'There was an error with the file upload');
					return;
				}
				foreach ($request->query as $name => $value)
				{
					if (substr($name,0,7)=='action_')
					{
						if (strlen($value)>0)
						{
							$type=substr($name,7);
							if (isset($request->query[$type]))
							{
								$url='http://'.$_SERVER['HTTP_HOST'].$request->query[$type];
								header('Location: '.$url);
								SwimEngine::shutdown();
							}
						}
					}
				}
			}
			else
			{
				$log->debug('Inaccessible file '.$request->resourcePath);
				displayLogin($request,'You must log in to write to this resource.');
			}
		}
		else
		{
			$log->debug('Attempt to upload non-file');
			displayGeneralError($request,'You can only upload files.');
		}
	}
  else
  {
  	$log->warn('Unknown upload to '.$request->resourcePath);
    displayNotFound($request);
    return;
  }
}


?>