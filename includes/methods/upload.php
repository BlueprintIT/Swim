<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2005
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
  
	if ($resource!==false)
	{
    if ($resource->isFile())
		{
			$log->debug('Checking write access');
			if ($_USER->canWrite($resource))
			{
				if (!is_a($resource->parent,'Container'))
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
								shutdown();
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
      if ($_USER->inGroup('admin'))
      {
        $cm = getCategoryManager($parts[1]);
        if ($_SERVER['REQUEST_METHOD']=='PUT')
        {
          $doc = new DOMDocument();
          if ($doc->load('php://input'))
          {
            $log->debug('XML successfully loaded');
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
        header($_SERVER["SERVER_PROTOCOL"]." 401 Not Authorized");
        print("You only have access to edit working versions.");
        return;
      }
    }
    else
    {
      $log->warn('Nowhere to uplaod to');
      displayNotFound($request);
    }
  }
}


?>