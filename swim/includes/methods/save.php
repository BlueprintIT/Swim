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
    displayNotFound($request);
  }
}


?>