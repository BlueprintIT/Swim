<?

/*
 * Swim
 *
 * Combined site templates
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function method_combine($request)
{
  global $_PREFS;
  
  $log = LoggerManager::getLogger('swim.method.layout');
  checkSecurity($request, true, true);
  
  if ($request->hasQueryVar('paths'))
  {
  	$paths = $request->getQueryVar('paths');
  	if (is_array($paths) && count($paths)>0)
  	{
  		$type = $request->getQueryVar('type');
	    setContentType($type);
	    $newest = 0;
  		foreach ($paths as $path)
  		{
  			if (is_file($_PREFS->getPref('storage.sitedir').$path))
  			{
		    	$newest = max($newest, filemtime($_PREFS->getPref('storage.sitedir').$path));
  			}
  			else
  			{
	  			if (strpos($path, '?') !== false)
	  			{
	  				$query = decodeQuery(substr($path, strpos($path, '?')+1));
	  				$path = substr($path, 0, strpos($path, '?'));
	  			}
	  			else
	  				$query = array();
	
	  			$subreq = Request::decode($path, $query, $request->getProtocol());
				  
				  $path = $_PREFS->getPref('storage.site.templates').'/'.$subreq->getPath();
				  $path = findDisplayableFile($path);
				  if ($path != null)
			    	$newest = max($newest, filemtime($path));
  			}
  		}
			switch ($type)
			{
				case 'text/css':
				case 'text/javascript':
		  		RequestCache::setCacheInfo($newest);
					break;
				default:
					RequestCache::setNoCache();
			}
  		foreach ($paths as $path)
  		{
  			switch ($type)
  			{
  				case 'text/css':
  					print "/* $path */\n";
  					break;
  			}
  			
  			if (is_file($_PREFS->getPref('storage.sitedir').$path))
  				include $_PREFS->getPref('storage.sitedir').$path;
  			else
  			{
	  			if (strpos($path, '?') !== false)
	  			{
	  				$query = decodeQuery(substr($path, strpos($path, '?')+1));
	  				$path = substr($path, 0, strpos($path, '?'));
	  			}
	  			else
	  				$query = array();
	
	  			$subreq = Request::decode($path, $query, $request->getProtocol());
				  
				  switch ($subreq->getMethod())
				  {
				  	case 'admin':
				  		$base = $_PREFS->getPref('storage.admin.templates');
				  		break;
				  	default:
				  		$base = $_PREFS->getPref('storage.site.templates');
				  }
				  $path = $base.'/'.$subreq->getPath();
				  $path = findDisplayableFile($path);
				  if ($path != null)
				  {
				    if (isTemplateFile($path))
				    {
				      $smarty = createSmarty($subreq, $type);
				      $log->debug('Starting display.');
				      $smarty->display($path);
				      $log->debug('Display complete.');
				    }
				    else
				      include($path);
				  }
  			}
  		}
	  }
	  else
	    displayNotFound($request);
  }
  else
    displayNotFound($request);
}

?>