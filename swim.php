<?

/*
 * Swim
 *
 * Root code for page creation
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// Load the preferences engine
require_once "prefs.php";

function printArray($array,$indent="")
{
	print($indent."{\n");
	$newindent=$indent."  ";
	while(list($key, $value) = each($array))
  {
  	if (is_array($value))
  	{
  		print($newindent."$key => Array\n");
  		printArray($value,$newindent);
  	}
  	else
  	{
	  	print($newindent."$key => $value\n");
	  }
  }
  print($indent."}\n");
}

function displayBlock($tag,$attrs,$text)
{
  $attrlist="id=\"".$attrs['id']."\"";
  if (isset($attrs['class']))
  {
    $attrlist="class=\"".$attrs['class']."\" ".$attrlist;
  }
  if (isset($attrs['style']))
  {
    $attrlist="style=\"".$attrs['style']."\" ".$attrlist;
  }
  print("<div ".$attrlist.">");
  print("</div>");
}

function displayVar($tag,$attrs,$text)
{
	if (isPrefSet("page.variables.".$attrs['name']))
	{
		print(getPref("page.variables.".$attrs['name']));
	}
}

function isValidPage($page)
{
	return is_readable(getCurrentVersion(getPref("storage.pages")."/".$page)."/page.conf");
}

// Include various utils
require_once getPref("storage.includes")."/logging.php";
require_once getPref("storage.includes")."/urls.php";
require_once getPref("storage.includes")."/parser.php";
require_once getPref("storage.includes")."/blocks.php";
require_once getPref("storage.includes")."/version.php";

// Figure out what page we are viewing
decodeRequest();

// If there is no page then use the default page
if (!isset($page))
{
	$page=getPref("pages.default");
}

// If the page does not exist then use the error page
if (!isValidPage($page))
{
	$page=getPref("pages.error");
	// If this page doesnt exist (really shouldnt happen) then try the default page
	if (!isValidPage($page))
	{
		$page=getPref("pages.default");
		// If we still dont have a valid page then we are in trouble
		if (!isValidPage($page))
		{
			trigger_error("This website has not been properly configured.");
			exit;
		}
	}
}

// Load the page's preferences
loadPreferences("page",getCurrentVersion(getPref("storage.pages")."/".$page)."/page.conf");

// Find the page's template or use the default
if (isPrefSet("page.template"))
{
	$template=getPref("page.template");
}
else
{
	$template=getPref("templates.default");
}

$templatedir=getCurrentVersion(getPref("storage.templates")."/".$template);
// If the template doesnt exist then there is a problem
if ($templatedir===false)
{
	trigger_error("This website has not been properly configured.");
	exit;
}

// If the template has prefs then load them
if (is_readable($templatedir."/template.conf"))
{
	loadPreferences("template",$templatedir."/template.conf");
}

// Find the template file name or use the default
if (isPrefSet("template.file"))
{
	$templatefile=getPref("template.file");
}
else
{
	$templatefile=getPref("templates.defaultname");
}

// If the file doesnt exist then we have a problem with the template.
if (!is_readable($templatedir."/".$templatefile))
{
	trigger_error($template." is invalid.");
	exit;
}

// Parse the template and display
$parser = new TemplateParser();
$parser->addCallback("block","displayBlock");
$parser->addCallback("var","displayVar");
$parser->parseFile($templatedir."/".$templatefile);

?>