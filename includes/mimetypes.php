<?

/*
 * Swim
 *
 * COde for handling mime types
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function setContentType($type)
{
	header('Content-Type: '.$type);
}

function guessContentType($extension)
{
	global $_TYPEMAP;
	
	$extension=strtolower($extension);
	return $_TYPEMAP->getPref($extension,'text/plain');
}

function determineContentType($file, $filename = "")
{
  if ($filename == "")
    $filename = basename($file);
	if (function_exists('mime_content_type') && is_file($file))
	{
    $type = mime_content_type($file);
    if ($type != false)
      return $type;
	}
	$parts=explode('.',$filename);
	if (count($parts)==1)
	{
		return 'text/plain';
	}
	else
	{
		return guessContentType($parts[count($parts)-1]);
	}
}

function initContentTypes()
{
	global $_TYPEMAP,$_PREFS;

	$file=fopen($_PREFS->getPref('storage.bootstrap').'/mimetypes.conf','r');
	$_TYPEMAP->loadPreferences($file);
	fclose($file);
}

$GLOBALS['_TYPEMAP'] = new Preferences();
initContentTypes();

?>