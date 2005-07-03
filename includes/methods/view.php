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


function method_view(&$request)
{
	global $_USER;
	
	$resource=&Resource::decodeResource($request);
	$log=&LoggerManager::getLogger("swim.method.view");

	if ($resource!==false)
	{
		if ($_USER->canRead($resource))
		{
      if ($resource->isFile())
			{
				if ($_SERVER['REQUEST_METHOD']=='GET')
				{
					if ($resource->exists())
					{
						setModifiedDate($resource->getModifiedDate());
						setContentType($resource->getContentType());
						$resource->outputFile();
  				}
  				else
  				{
  					displayError($request);
  				}
  			}
  			else if ($_SERVER['REQUEST_METHOD']=='PUT')
  			{
  				$log->debug('Preparing to write file');
  			  $in=@fopen('php://input','rb');
  			  if ($in!==false)
  			  {
  					$log->debug('Opened input');
    			  $out=$resource->openFileWrite();
    			  if ($out!==false)
    			  {
  						$log->debug('Opened output');
      			  while (!feof($in))
      			  {
      			    $data=fread($in,1024);
        			  fwrite($out,$data);
      			  	$log->debug('Read '.$data);
        			}
 
  						$log->debug('Closing files');
 							$resource->closeFile($out);
     			    fclose($in);
            	header($_SERVER["SERVER_PROTOCOL"]." 202 Accepted");
            	print("Resource accepted");
            	return;
    			  }
    			  else
    			  {
    			    $log->warn("Couldn't open file for writing.");
    			  }
   			    fclose($in);
   			  }
   			  else
   			  {
   			    $log->warn('Unable to open standard input');
   			    displayError($request);
   			  }
  			}
  			else
  			{
  			  displayError($request);
  			}
			}
			else if ($resource->isPage())
			{
				$resource->display($request);
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