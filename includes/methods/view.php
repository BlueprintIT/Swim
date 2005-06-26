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
				$file=$resource->getDir().'/'.$resource->path;
				if ($_SERVER['REQUEST_METHOD']=='GET')
				{
  				if (is_readable($file))
  				{
  					$resource->lockRead();
  					$stats=stat($file);
  					setModifiedDate($stats['mtime']);
  					setContentType(determineContentType($file));
  					readfile($file);
  					$resource->unlock();
  				}
  				else if (is_readable($file.'.php'))
  				{
  					$resource->lockRead();
  					$stats=stat($file.'.php');
  					setModifiedDate($stats['mtime']);
  					include($file.'.php');
  					$resource->unlock();
  				}
  				else
  				{
  					displayError($request);
  				}
  			}
  			else if ($_SERVER['REQUEST_METHOD']=='PUT')
  			{
  			  $in=@fopen('php://input','rb');
  			  if ($in!==false)
  			  {
    			  $resource->lockWrite();
    			  $out=@fopen($file,'wb');
    			  if ($out!==false)
    			  {
      			  while (!feof($in))
      			  {
      			    $data=fread($in,1024);
        			  fwrite($out,$data);
        			}
 
    			    fclose($out);
    			    $resource->unlock();
     			    fclose($in);
            	header($_SERVER["SERVER_PROTOCOL"]." 202 Accepted");
            	print("Resource accepted");
            	return;
    			  }
    			  else
    			  {
    			    $log->warn("Couldn't open file for writing.");
    			  }
   			    $resource->unlock();
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
				$page = &$resource->getPage();
				$page->display($request);
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