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

function save_apply($resource,$name,$value)
{
  if (substr($name,0,5)=='pref:')
  {
    $name=substr($name,5);
    $resource->prefs->setPref($name,$value);
    $resource->savePreferences();
  }
  else if (substr($name,0,5)=='file:')
  {
    $name=substr($name,5);
    $file=$resource->openFileWrite($name);
    fwrite($file,$value);
    $resource->closeFile($file);
  }
  else if (substr($name,0,6)=='block:')
  {
    $name=substr($name,6);
    $pos=strpos($name,':');
    if ($pos>0)
    {
      $block=substr($name,0,$pos);
      $name=substr($name,$pos+1);
      if ($resource instanceof Page)
      {
        $newr = $resource->getReferencedBlock($block);
      }
      else
      {
        $newr = $resource->getResource('block',$block);
      }
      if ($newr!==null)
      {
        save_apply($newr,$name,$value);
      }
    }
  }
}

function method_save($request)
{
  global $_USER;
  
  checkSecurity($request, true, true);
  
  $resource=Resource::decodeResource($request);
  $log=LoggerManager::getLogger("swim.method.save");

  if ($resource!==null)
  {
    if (($resource->isPage())||($resource->isBlock()))
    {
      $log->debug('Checking write access');
      if ($_USER->canWrite($resource))
      {
        $details=$resource->getWorkingDetails();
        
        if ((!$details->isMine())&&(isset($request->query['forcelock'])))
        {
          if ($request->query['forcelock']=='continue')
          {
            $details->takeOver();
          }
          else if ($request->query['forcelock']=='discard')
          {
            $details->takeOverClean();
          }
        }
        
        if ($details->isMine())
        {
          $working=$resource->makeWorkingVersion();
          foreach ($request->query as $name => $value)
          {
            if (substr($name,0,7)=='action:')
            {
              if (strlen($value)>0)
              {
                $type=substr($name,7);
                if (isset($request->query[$type]))
                {
                  $redirect='http://'.$_SERVER['HTTP_HOST'].$request->query[$type];
                }
              }
            }
            else
            {
              save_apply($working,$name,$value);
            }
          }
          if (isset($request->query['layout']))
          {
            $working->setLayout($request->query['layout']);
          }
          if (!isset($redirect))
          {
            if (isset($request->query['default']))
            {
              $redirect='http://'.$_SERVER['HTTP_HOST'].$request->query['default'];
            }
          }
          header('Location: '.$redirect);
          SwimEngine::shutdown();
        }
        else
        {
          displayLocked($request,$details,$resource);
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
  	$parts = explode('/',$request->resource);
  	if (($parts[1]=='categories')&&((count($parts)==2)||(count($parts)==3)))
  	{
  		$container = getContainer($parts[0]);
  		if ($container !== null)
  		{
  			$category=null;
  			if (count($parts)==3)
	  			$category = $container->getCategory($parts[2]);
	  		else
	  		{
					$parent = $container->getCategory($request->query['parent']);
	  			$category = new Category($container, null, null, "");
	  		}
  			if ($category !== null)
  			{
          foreach ($request->query as $name => $value)
          {
            if (substr($name,0,7)=='action:')
            {
              if (strlen($value)>0)
              {
                $type=substr($name,7);
                if (isset($request->query[$type]))
                {
                  $redirect='http://'.$_SERVER['HTTP_HOST'].$request->query[$type];
                }
              }
            }
          }
          if ($type != 'cancel')
          {
          	if (isset($request->query['name']))
          	{
          		$category->name = $request->query['name'];
          	}
          	if (isset($request->query['icon']))
          	{
          		$category->icon = $request->query['icon'];
          	}
          	if (isset($request->query['hovericon']))
          	{
          		$category->hovericon = $request->query['hovericon'];
          	}
          	if ($category->id===null)
			  			$parent->add($category);
          	$category->save();
          	// TODO remove this crappy code
          	$redirect.='&category='.$category->id;
          }
          redirect($redirect);
  			}
  		}
	    else
	    {
	    	$log->warn('Invalid container');
	      displayNotFound($request);
	    }
  	}
  	else if (($parts[1]=='links')&&((count($parts)==2)||(count($parts)==3)))
  	{
  		$container = getContainer($parts[0]);
  		if ($container !== null)
  		{
  			$link=null;
  			if (count($parts)==3)
	  			$link = $container->getLink($parts[2]);
	  		else
	  		{
					$parent = $container->getCategory($request->query['parent']);
	  			$link = new Link(null, null, "", "");
	  		}
  			if ($link !== null)
  			{
          foreach ($request->query as $name => $value)
          {
            if (substr($name,0,7)=='action:')
            {
              if (strlen($value)>0)
              {
                $type=substr($name,7);
                if (isset($request->query[$type]))
                {
                  $redirect='http://'.$_SERVER['HTTP_HOST'].$request->query[$type];
                }
              }
            }
          }
          if ($type != 'cancel')
          {
          	if (isset($request->query['name']))
          	{
          		$link->name = $request->query['name'];
          	}
          	if (isset($request->query['address']))
          	{
          		$link->address = $request->query['address'];
          	}
          	if ($link->id===null)
			  			$parent->add($link);
          	$link->save();
          	// TODO remove this crappy code
          	$redirect.='&link='.$link->id;
          }
          redirect($redirect);
  			}
  		}
	    else
	    {
	    	$log->warn('Invalid container');
	      displayNotFound($request);
	    }
  	}
    else
    {
	    $log->warn('Invalid path');
      displayNotFound($request);
    }
  }
}


?>