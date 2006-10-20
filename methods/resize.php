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


function method_resize($request)
{
	global $_PREFS;
	
	$log=LoggerManager::getLogger("swim.method.resize");
  
  $filepath = '/'.$request->getPath();
  $base = $_PREFS->getPref('url.base');
  $filepath = substr($filepath, strlen($base));
  $filename = $_PREFS->getPref('storage.sitedir').$filepath;
  if (is_file($filename))
  {
  	setDefaultCache();
  	
  	$mimetype = determineContentType($filename);
  	if ($request->hasQueryVar('cache'))
  	{
  		$cachefile = $_PREFS->getPref('storage.sitecache').'/'.$request->getQueryVar('cache').$filepath;
  		$cachedir = dirname($cachefile);
  		if (is_file($cachefile) && (filemtime($cachefile)>=filemtime($filename)))
  		{
		  if ($request->hasQueryVar('type'))
			  $mimetype = $request->getQueryVar('type');
  			setContentType($mimetype);
  			setCacheInfo(filemtime($cachefile));
  			readfile($cachefile);
  			return;
  		}
  	}
  	
  	setCacheInfo(filemtime($filename));
  	
		if ($mimetype=="image/jpeg")
			$image = imagecreatefromjpeg($filename);
		else if ($mimetype=="image/gif")
			$image = imagecreatefromgif($filename);
		else if ($mimetype=="image/png")
			$image = imagecreatefrompng($filename);
		else
		{
			displayGeneralError($request,'Only images can be resized, this is '.$mimetype);
			return;
		}
		
		$br=255;
		$bg=255;
		$bb=255;
		$transparent=false;

		$width=imagesx($image);
		$height=imagesy($image);
		$x=0;
		$y=0;
		$sourceaspect=$width/$height;
		
		if (($request->hasQueryVar('width')) && ($request->hasQueryVar('height')))
		{
			$newwidth = $request->getQueryVar('width');
			$newheight = $request->getQueryVar('height');
			$targetaspect = $newwidth/$newheight;

			if ($request->hasQueryVar('padding'))
			{
				$actualwidth = $newwidth;
				$actualheight = $newheight;
				
        if ($request->getQueryVar('padding')=='transparent')
          $transparent=true;
				else
				{
					if (substr($request->getQueryVar('padding'),0,1)=='#')
						$hex = substr($request->getQueryVar('padding'),1);
					else
						$hex = $request->getQueryVar('padding');
					$value = hexdec($hex);
					$br = 0xFF & ($value >> 0x10);
					$bg = 0xFF & ($value >> 0x08);
					$bb = 0xFF & $value;
				}
			}
			
      if ($targetaspect>$sourceaspect)
        $newwidth = $newheight*$sourceaspect;
      else
        $newheight = $newwidth/$sourceaspect;
		}
		else if ($request->hasQueryVar('width'))
		{
			$newwidth = $request->getQueryVar('width');
			$newheight = $newwidth/$sourceaspect;
		}
		else if ($request->hasQueryVar('height'))
		{
			$newheight = $request->getQueryVar('height');
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

		$newimage=imagecreatetruecolor($actualwidth,$actualheight);
		$backg=imagecolorallocate($newimage,$br,$bg,$bb);
		if ($transparent)
			imagecolortransparent($newimage,$backg);
//		imagerectangle($newimage, 0, 0, $actualwidth, $actualheight, $backg);
		imagefill($newimage, 1, 1, $backg);
		if (true)
			imagecopyresampled($newimage,$image,$x,$y,0,0,$newwidth,$newheight,$width,$height);
		else
			imagecopyresized($newimage,$image,$x,$y,0,0,$newwidth,$newheight,$width,$height);
		
		if ($request->hasQueryVar('type'))
			$mimetype = $request->getQueryVar('type');

		if ($mimetype=='image/gif')
			imagetruecolortopalette($newimage, false, 255);
		else if ($transparent)
		{
			$mimetype = 'image/png';
			imagetruecolortopalette($newimage, false, 255);
		}
		else if ($mimetype=='image/jpeg')
		{
			imageinterlace($newimage);
			if ($request->hasQueryVar('quality'))
				$quality = $request->getQueryVar('quality');
			else
				$quality = 80;
		}

		setContentType($mimetype);

		if (($request->hasQueryVar('cache')) && (is_dir($cachedir) || @recursiveMkDir($cachedir)))
		{
			if ($mimetype=='image/jpeg')
				imagejpeg($newimage, $cachefile, $quality);
			else if ($mimetype=='image/png')
				imagepng($newimage, $cachefile);
			else if ($mimetype=='image/gif')
				imagegif($newimage, $cachefile);
		}
		
		if ($mimetype=='image/jpeg')
			imagejpeg($newimage, '', $quality);
		else if ($mimetype=='image/png')
			imagepng($newimage);
		else if ($mimetype=='image/gif')
			imagegif($newimage);
  }
  else
  {
  	displayNotFound($request);
  }
}


?>
