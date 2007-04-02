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

function method_view($request)
{
	global $_USER,$_PREFS;
	
	$resource=$request->resource;
	$log=LoggerManager::getLogger("swim.method.view");
  
	if ($resource!==null)
	{
    checkSecurity($request, $resource->prefs->getPref('security.sslrequired'), $resource->prefs->getPref('security.sslallowed'));
  
    if ($resource->isFile())
		{
			if ($_SERVER['REQUEST_METHOD']=='GET')
			{
				if ($_USER->canRead($resource))
				{
					if ($resource->exists())
					{
						if (((isset($request->query['version']))&&($request->query['version']!='temp'))||($resource->version===false))
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
						$resource->outputFile($request);
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
				$log->debug('Checking write access');
				if ($_USER->canWrite($resource))
				{
					$log->debug('Checking that this is the working version');
					if ($resource->version=='temp')
					{
						$log->debug('Checking that we have the working lock');
						$details=$resource->getWorkingDetails();

						if ($details->isMine())
						{
		  				$log->debug('Preparing to write file '.$resource->getDir().'/'.$resource->id);
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
		 							$log->debug('Resource closed');
		     			    fclose($in);
		 							$log->debug('Input closed');
		     			    $details->saveDetails();
		 							$log->debug('Details saved');
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
							header($_SERVER["SERVER_PROTOCOL"]." 409 Conflict");
							print("Someone else has locked this resource.");
							return;
						}
					}
					else
					{
						$log->info('Version was '.$resource->version);
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
				$template=null;
				setNoCache();
				if (isset($request->query['template']))
					$template=Resource::decodeResource(substr($request->query['template'],1));
				if ($template===null)
					$template=$resource->getReferencedTemplate();
				$template->display($request,$resource);
			}
			else
			{
				displayLogin($request,'You must log in to view this resource.');
			}
		}
		else if ($resource->isBlock())
		{
			if ($_USER->canRead($resource))
			{
				setDefaultCache();
			  $preview = Resource::decodeResource($_PREFS->getPref('method.preview.page'));
			  $preview->display($request);
			}
			else
			{
				displayLogin($request,'You must log in to view this resource.');
			}
		}
		else
		{
			displayGeneralError($request,'You can only view pages, blocks or files.');
		}
	}
	else
	{
    $parts = explode('/',$request->resourcePath);
    if (($parts[1]=='categories')&&(count($parts)==2))
    {
      $container = getContainer($parts[0]);
      if ($container!==null)
      {
        $page = Resource::decodeResource('internal/page/categories');
        $page->display($request);
      }
      else
      {
        displayNotFound($request);
      }
    }
    else if (($parts[1]=='categories')&&(count($parts)==3))
    {
      $container = getContainer($parts[0]);
      if ($container===null)
      {
        displayNotFound($request);
        return;
      }
      if ($parts[2]=='root')
        $category = $container->getRootCategory();
      else
        $category = $container->getCategory($parts[2]);
      if ($category===null)
      {
        displayNotFound($request);
        return;
      }

      if ($container->prefs->isPrefSet('categories.customlink'))
      {
        $req = new Request();
        $req->method = 'view';
        $req->resource = substr($container->prefs->getPref('categories.customlink'),1);
        $req->data['category'] = $category;
        SwimEngine::processRequest($req);
      }
      else
      {
        displayServerError($request);
      }
    }
    else
    {
      $log->debug('Resource not found - '.$request->resourcePath);
  		displayNotFound($request);
    }
	}
}


?>