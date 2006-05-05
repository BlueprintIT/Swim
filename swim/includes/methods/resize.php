<?

/*
 * Swim
 *
 * Image resizing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */


function isGD2()
{
	return true;
}

function method_resize($request)
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
						$mimetype=$resource->getContentType();
						$resource->lockRead();
						$filename = $resource->getFileName();
						if ($mimetype=="image/jpeg")
						{
							$image = imagecreatefromjpeg($filename);
						}
						else if ($mimetype=="image/gif")
						{
							$image = imagecreatefromgif($filename);
						}
						else if ($mimetype=="image/png")
						{
							$image = imagecreatefrompng($filename);
						}
						else
						{
							$resource->unlock();
							displayGeneralError($request,'Only images can be resized, this is '.$mimetype);
							return;
						}
						$resource->unlock();
						
						$br=255;
						$bg=255;
						$bb=255;
						$transparent=false;

						$width=imagesx($image);
						$height=imagesy($image);
						$x=0;
						$y=0;
						$sourceaspect=$width/$height;
						
						if ((isset($request->query['maxwidth'])) && (isset($request->query['maxheight'])))
						{
							$newwidth = $request->query['maxwidth'];
							$newheight = $request->query['maxheight'];
							$targetaspect = $newwidth/$newheight;
							
              if ($targetaspect>$sourceaspect)
                $newwidth = $newheight*$sourceaspect;
              else
                $newheight = $newwidth/$sourceaspect;

							if (isset($request->query['padding']))
							{
								$actualwidth = $newwidth;
								$actualheight = $newheight;
								
                if ($request->query['padding']=='transparent')
                {
                  $transparent=true;
                }
								else
								{
									if (substr($request->query['padding'],0,1)=='#')
									{
										$hex = substr($request->query['padding'],1);
									}
									else
									{
										$hex = $request->query['padding'];
									}
									$value = hexdec($hex);
									$br = 0xFF & ($value >> 0x10);
									$bg = 0xFF & ($value >> 0x08);
									$bb = 0xFF & $value;
								}
							}
						}
						else if (isset($request->query['maxwidth']))
						{
							$newwidth = $request->query['maxwidth'];
							$newheight = $newwidth/$sourceaspect;
						}
						else if (isset($request->query['maxheight']))
						{
							$newheight = $request->query['maxheight'];
							$newwidth = $newheight*$sourceaspect;
						}
						else
						{
							$newheight = $height;
							$newwidth = $width;
						}
						
						if (!isset($actualwidth))
							$actualwidth = $newwidth;
						else
							$x = ($actualwidth-$newwidth)/2;

						if (!isset($actualheight))
							$actualheight = $newheight;
						else
							$y = ($actualheight-$newheight)/2;

						if (isGD2())
						{
							$newimage=imagecreatetruecolor($actualwidth,$actualheight);
							$backg=imagecolorallocate($newimage,$br,$bg,$bb);
							imagefill($newimage,0,0,$backg);
							if ($transparent=true)
								imagecolortransparent($newimage,$backg);
							if (true)
							{
								imagecopyresampled($newimage,$image,$x,$y,0,0,$newwidth,$newheight,$width,$height);
							}
							else
							{
								imagecopyresized($newimage,$image,$x,$y,0,0,$newwidth,$newheight,$width,$height);
							}
						}
						else
						{
							$newimage=imagecreate($actualwidth,$actualheight);
							$newimage=imagecreatetruecolor($actualwidth,$actualheight);
							$backg=imagecolorallocate($newimage,$br,$bg,$bb);
							imagefill($newimage,0,0,$backg);
							if ($transparent=true)
								imagecolortransparent($newimage,$backg);
							imagecopyresized($newimage,$image,$x,$y,0,0,$newwidth,$newheight,$width,$height);
						}
						
						if ($mimetype=="image/jpeg")
						{
							setContentType($mimetype);
							imageinterlace($image);
							imagejpeg($newimage,"",75);
						}
						else if ($mimetype=="image/gif")
						{
							setContentType("image/png");
							imagepng($newimage);
						}
						else if ($mimetype=="image/png")
						{
							setContentType($mimetype);
							imagepng($newimage);
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
			else
			{
			  displayServerError($request);
			}
		}
		else
		{
			displayGeneralError($request,'Only images can be resized');
		}
	}
	else
	{
		displayNotFound($request);
	}
}


?>