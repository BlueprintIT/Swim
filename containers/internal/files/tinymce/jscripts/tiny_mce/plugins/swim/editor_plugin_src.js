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
	win.open(tinyMCE.getParam('swim_browser',''),'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=600,height=400');
}

/**
 * Gets executed when a editor instance is initialized
 */
function TinyMCE_swim_initInstance(inst)
{
	// You can take out plugin specific parameters
	tinyMCE.settings['file_browser_callback']='TinyMCE_swim_file_browser_callback';
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
		return true;
	}
	return false;
}

/**
 * Gets executed when the selection/cursor position was changed.
 */
function TinyMCE_swim_handleNodeChange(editor_id, node, undo_index, undo_levels, visual_aid, any_selection)
{
}

/**
 * Gets executed when contents is inserted / retrived.
 */
 
function expandURL(url)
{
	if (url.substring(0,12)=="attachments/")
	{
		url=tinyMCE.getParam('swim_attachments','')+url.substring(12);
	}
	return url;
}

function compressURL(url)
{
}

function TinyMCE_swim_cleanup(type, content)
{
	if (type=="insert_to_editor_dom")
	{
		var nodes = content.getElementsByTagName("A");
		for (var i=0; i<nodes.length; i++)
		{
			nodes[i].href=expandURL(nodes[i].getAttribute('href'));
		}
		nodes = content.getElementsByTagName("IMG");
		for (var i=0; i<nodes.length; i++)
		{
			nodes[i].src=expandURL(nodes[i].getAttribute('src'));
		}
	}
	else if (type=="get_from_editor_dom")
	{
	}
	return content;
}
