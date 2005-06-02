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

// Include various utils
require_once $_PREFS->getPref("storage.includes")."/includes.php";

require_once $_PREFS->getPref("storage.blocks.classes")."/HtmlBlock.php";

// Load the page to display
$page = new Page();

// Parse the template and display
$parser = new TemplateParser();
$parser->addCallback("block","displayBlock");
$parser->addCallback("var","displayVar");
$parser->parseFile($page->template->dir."/".$page->template->file);

?>