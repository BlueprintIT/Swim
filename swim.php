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

// Include various utils
require_once getPref("storage.includes")."/logging.php";
require_once getPref("storage.includes")."/urls.php";
require_once getPref("storage.includes")."/parser.php";
require_once getPref("storage.includes")."/blocks.php";

// Figure out what page we are viewing
decodeRequest();

// If there is no page then use the default page
if (!isset($page))
{
	$page=getPref("pages.default");
}

// If the page does not exist then use the error page
if (!is_readable(getPref("storage.pages")."/".$page."/page.conf"))
{
	$page=getPref("pages.error");
	// If this page doesnt exist (really shouldnt happen) then try the default page
	if (!is_readable(getPref("storage.pages")."/".$page."/page.conf"))
	{
		$page=getPref("pages.default");
		// If we still dont have a valid page then we are in trouble
		if (!is_readable(getPref("storage.pages")."/".$page."/page.conf"))
		{
			trigger_error("This website has not been properly configured.");
			exit;
		}
	}
}

// Load the page's preferences
loadPreferences("page",getPref("storage.pages")."/".$page."/page.conf");

// Find the page's template or use the default
if (isPrefSet("page.template"))
{
	$template=getPref("page.template");
}
else
{
	$template=getPref("templates.default");
}

// If the template doesnt exist then there is a problem
if (!is_dir(getPref("storage.templates")."/".$template))
{
	trigger_error("This website has not been properly configured.");
	exit;
}

// If the template has prefs then load them
if (is_readable(getPref("storage.templates")."/".$template."/template.conf"))
{
	loadPreferences("template",getPref("storage.templates")."/".$template."/template.conf");
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
if (!is_readable(getPref("storage.templates")."/".$template."/".$templatefile))
{
	trigger_error($template." is invalid.");
	exit;
}

// Parse the template and display
$parser = new TemplateParser();
$parser->addCallback("block","displayBlock");
$parser->addCallback("var","displayVar");
$parser->parseFile(getPref("storage.templates")."/".$template."/".$templatefile);

?>