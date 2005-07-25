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
    if ($resource->isFile())
		{
			if ($_SERVER['REQUEST_METHOD']=='GET')
			{
				if ($_USER->canRead($resource))
				{
					if ($resource->exists())
					{
						if ((isset($request->query['version']))&&($request->query['version']!='temp'))
						{
							setValidTime(60);
						}
						else
						{
							setDefaultCache();
						}
						setCacheInfo($resource->getModifiedDate(),$resource->getETag());
						$type=$resource->getContentType();
						setContentType($type);
						if ($type=='text/css')
						{
							include 'csshandler.php';
							$handlerf = new CSSHandlerFactory();
							$handlerf->output($resource);
						}
						else
						{
							$resource->outputFile();
						}
					}
					else
					{
						displayNotFound($request);
					}
				}
				else
				{
					displayLogin($request,'You must log in to view this resource.');
				}
			}
			else if ($_SERVER['REQUEST_METHOD']=='PUT')
			{
				if ($_USER->canWrite($resource))
				{
					if ($resource->version=='temp')
					{
						$details=&$resource->getWorkingDetails();
						if ($details->isMine())
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
		     			    $details->saveDetails();
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
		   			    displayServerError($request);
		   			  }
						}
						else
						{
							header($_SERVER["SERVER_PROTOCOL"]." 401 Not Authorized");
							print("Someone else has locked this resource.");
							return;
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
					displayLogin($request,'You must log in to write to this resource.');
				}
			}
			else
			{
			  displayServerError($request);
			}
		}
		else if ($resource->isPage())
		{
			if ($_USER->canRead($resource))
			{
				$template=false;
				setDefaultCache();
				$modified=$resource->getTotalModifiedDate();
				if (isset($request->query['template']))
				{
					list($cont,$templ)=explode('/',$request->query['template']);
					$container=&getContainer($cont);
					if ($container!==false)
					{
						$template=&$container->getTemplate($templ);
						if ($template!==false)
						{
							$modified=max($modified,$template->getModifiedDate());
						}
					}
				}
				if ($template===false)
				{
					$template=&$resource->getTemplate();
				}
				setCacheInfo($modified,$resource->getETag());
				$template->display($request,$resource);
			}
			else
			{
				displayLogin($request,'You must log in to view this resource.');
			}
		}
		else
		{
			displayGeneralError($request,'You can only view pages or files.');
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>