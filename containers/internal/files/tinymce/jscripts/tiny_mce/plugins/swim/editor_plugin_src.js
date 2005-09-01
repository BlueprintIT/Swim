/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('swim', 'en'); // <- Add a comma separated list of all supported languages

/****
 * Steps for creating a plugin from this template:
 *
 * 1. Change all "template" to the name of your plugin.
 * 2. Remove all the callbacks in this file that you don't need.
 * 3. Remove the popup.htm file if you don't need any popups.
 * 4. Add your custom logic to the callbacks you needed.
 * 5. Write documentation in a readme.txt file on how to use the plugin.
 * 6. Upload it under the "Plugins" section at sourceforge.
 *
 ****/

function TinyMCE_swim_file_browser_callback(field_name, url, type, win)
{
	var newwin = win.open(tinyMCE.getParam('swim_browser',''),'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=630,height=400');
	if (newwin)
	{
		newwin.targetField=field_name;
	}
	else
	{
		alert("You must disable popup blocking to use this file browser.");
	}
}

function TinyMCE_swim_convertURL(url, node, on_save)
{
	alert(url);
	return url;
}

/**
 * Gets executed when a editor instance is initialized
 */
function TinyMCE_swim_initInstance(inst)
{
	// You can take out plugin specific parameters
	tinyMCE.settings['file_browser_callback']='TinyMCE_swim_file_browser_callback';
	TinyMCE.prototype.convertURL=TinyMCE_swim_convertURL;
}

/**
 * Gets executed when a editor needs to generate a button.
 */
function TinyMCE_swim_getControlHTML(control_name)
{
	switch (control_name)
	{
		case "pagelink":
			return '<img id="{$editor_id}_pagelink" src="{$pluginurl}/images/pagelink.gif" title="{$lang_swim_pagelinkdesc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mcePageLink\', true);" />';
		case "commit":
			return '<input id="{$editor_id}_commit" title="{$lang_swim_committip}" name="action_commit" value="{$lang_swim_commitdesc}" type="submit" style="height: 20px" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);">';
		case "save":
			return '<input id="{$editor_id}_save" title="{$lang_swim_savetip}" name="action_continue" value="{$lang_swim_savedesc}" type="submit" style="height: 20px" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);">';
		case "cancel":
			return '<button id="{$editor_id}_cancel" title="{$lang_swim_canceltip}" onclick="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceSwimCancel\', true);return false;" style="height: 20px" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);">{$lang_swim_canceldesc}</button>';
	}
	return "";
}

/**
 * Gets executed when a command is called.
 */
function TinyMCE_swim_execCommand(editor_id, element, command, user_interface, value)
{
	if (command=="mcePageLink")
	{
		var newwin = window.open(tinyMCE.getParam('swim_pagebrowser',''),'swimpagebrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=600,height=400');
		if (!newwin)
		{
			alert("You must disable popup blocking in your web browser to use this feature.");
		}
		return true;
	}
	else if (command=="mceSwimCancel")
	{
		window.location.href=tinyMCE.getParam('swim_cancel','');
		return true;
	}
	return false;
}

/**
 * Gets executed when the selection/cursor position was changed.
 */
function TinyMCE_swim_handleNodeChange(editor_id, node, undo_index, undo_levels, visual_aid, any_selection)
{
	// Get link
	var anchorLink = tinyMCE.getParentElement(node, "a", "href");

	if (any_selection || anchorLink)
	{
		tinyMCE.switchClassSticky(editor_id + '_pagelink', anchorLink ? 'mceButtonSelected' : 'mceButtonNormal', false);
	}
	else
	{
		tinyMCE.switchClassSticky(editor_id + '_pagelink', 'mceButtonDisabled', true);
	}
}

/**
 * Gets executed when contents is inserted / retrived.
 */
function TinyMCE_swim_cleanup(type, content)
{
	return content;
}
