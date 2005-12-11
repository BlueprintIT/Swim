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

function method_save($request)
{
  global $_USER;
  
  $resource=Resource::decodeResource($request);
  $log=LoggerManager::getLogger("swim.method.save");

  if ($resource!==false)
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
            else if (substr($name,0,5)=='pref:')
            {
              $name=substr($name,5);
              $working->prefs->setPref($name,$value);
            }
            else if (substr($name,0,5)=='file:')
            {
              $name=substr($name,5);
              $file=$working->openFileWrite($name);
              fwrite($file,$value);
              $resource->closeFile($file);
            }
          }
          $working->savePreferences();
          if (isset($redirect))
          {
            header('Location: '.$redirect);
            shutdown();
          }
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