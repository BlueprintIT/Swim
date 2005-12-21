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
	var url = tinyMCE.getParam('swim_browser','');
	if (url.indexOf('?')>0)
	{
		url=url+'&';
	}
	else
	{
		url=url+'?';
	}
	url=url+'action=document.forms[0].elements['+"'"+field_name+"'"+'].value=selected';
	var newwin = win.open(url,'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=630,height=400');
	if (!newwin)
	{
		alert("You must disable popup blocking to use this file browser.");
	}
}

function TinyMCE_swim_convertURL(url, node, on_save)
{
	var hostpart="http://"+tinyMCE.getParam('document_host','');
	var base=tinyMCE.getParam('document_base_url','');
	var view=tinyMCE.getParam('swim_view','');
	if (base.indexOf(hostpart)==0)
	{
		base=base.substring(hostpart.length);
	}

	//alert(base);
	//alert(view);
	
	var page='/'+base.substring(view.length);	
	var basetemp = view+'version/temp'+page;
		
	if (url.indexOf('tinymce')>0)
	{
		url=url.substring(url.indexOf('tinymce')+8);
	}
	else if (url.indexOf(hostpart)==0)
	{
		url=url.substring(hostpart.length);
	}
	
	//alert(url);
	
	if (url.indexOf(base)==0)
	{
		url=url.substring(base.length);
	}
	else if (url.indexOf(basetemp)==0)
	{
		url=url.substring(basetemp.length);
	}
	else if (url.indexOf(page)==0)
	{
		url=url.substring(page.length);
	}
	else if (url.indexOf(view)==0)
	{
		url=url.substring(view.length-1);
	}
	
	//alert(url);
	
	if (!on_save)
	{
		if (url.substring(0,1)=='/')
		{
			url=hostpart+view+url.substring(1);
		}
		else if (url.indexOf('://')>0)
		{
		}
		else
		{
			url=hostpart+basetemp+url;
		}
	}
	else
	{
		if (url.substring(0,1)=='/')
		{
			if (tinyMCE.getParam('swim_external',false))
			{
				url=view+url.substring(1);
			}
		}
		else if (url.indexOf('://')>0)
		{
		}
		else
		{
			url=page+url;
		}
	}
	//alert(url);
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
		var newwin = window.open(tinyMCE.getParam('swim_pagebrowser',''),'swimpagebrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=640,height=400,resizable');
		if (!newwin)
		{
			alert("You must disable popup blocking in your web browser to use this feature.");
		}
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
	if (type=='insert_to_editor_dom')
	{
		var nodes = content.getElementsByTagName('IMG');
		for (var i=0; i<nodes.length; i++)
		{
			var src = nodes[i].getAttribute('src');
			nodes[i].src=TinyMCE_swim_convertURL(src,nodes[i],false);
			
			var floatval;
			if (tinyMCE.isMSIE)
				floatval = nodes[i].style.styleFloat;
			else
				floatval = nodes[i].style.cssFloat;
			var vertval = nodes[i].style.verticalAlign;
			if ((floatval)&&(floatval!='')&&(floatval!='none'))
			{
				nodes[i].setAttribute('align',floatval);
			}
			else if ((vertval)&&(vertval!='')&&(vertval!='baseline'))
			{
				nodes[i].setAttribute('align',vertval);
			}
			if (tinyMCE.isMSIE)
				nodes[i].style.styleFloat = '';
			else
				nodes[i].style.cssFloat = '';
			nodes[i].style.verticalAlign = '';
		}
		nodes = content.getElementsByTagName('A');
		for (var i=0; i<nodes.length; i++)
		{
			var src = nodes[i].getAttribute('href');
			nodes[i].href=TinyMCE_swim_convertURL(src,nodes[i],false);
		}
	}
	return content;
}
