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

	$resource=Resource::decodeResource($request);
  
  $log->debug('upload');
  
	if ($resource!==false)
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
				displayLogin($request,'You must log in to write to this resource.');
			}
		}
		else
		{
			displayGeneralError($request,'You can only upload files.');
		}
	}
  else
  {
    $parts=split('/',$request->resource,2);
    if ((count($parts)==2)&&($parts[0]=='categories'))
    {
      $log->debug('Uploading category database');
      if ($_USER->hasPermission('documents',PERMISSION_WRITE))
      {
        $cm = getContainer($parts[1]);
        if ($_SERVER['REQUEST_METHOD']=='PUT')
        {
          $doc = new DOMDocument();
          if ($doc->load('php://input'))
          {
            $log->debug('XML successfully loaded');
            $log->debug($doc->saveXML());
            $cm->load($doc);
            header($_SERVER["SERVER_PROTOCOL"]." 202 Accepted");
            print("Resource accepted");
            return;
          }
          else
          {
            $log->error('Error loading XML');
            displayServerError($request);
          }
        }
        else
        {
          $log->error('Invalid HTTP method - '.$_SERVER['REQUEST_METHOD']);
          displayServerError($request);
        }
      }
      else
      {
        $log->debug('No write permission');
        header($_SERVER["SERVER_PROTOCOL"]." 401 Not Authorized");
        print("You don't have permission to edit categories.");
        return;
      }
    }
    else
    {
      displayNotFound($request);
      return;
    }
  }
}


?>