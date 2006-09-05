tinyMCEparams = {
	// General
	mode: "textareas",
	theme: "advanced",
	plugins: "print,spellchecker,paste,searchreplace,table,advblockformat",
	editor_selector: "HTMLEditor",
	button_tile_map: true,
	dialog_type: "modal",
	
	// Cleanup/Output
	cleanup_on_startup: true,
	invalid_elements: "font,div,applet,iframe,object",
	remove_linebreaks: false,
	fix_list_elements: true,
	fix_table_elements: true,
	
	// URL
	relative_urls: false,
	remove_script_host: true,

	// Callbacks
	init_instance_callback: "tinyMCEEditorInit",
	file_browser_callback: "tinyMCEFileBrowser",
	
	// Layout
	content_css: "../../swim/shared/yui/reset/reset-min.css,../../swim/shared/yui/fonts/fonts-min.css,../../swim/admin/static/styles/tinyMCEEditor.css",
	popups_css: "../../swim/admin/static/styles/tinyMCEPopups.css",
	editor_css: "../../swim/admin/static/styles/tinyMCEUI.css",
	
	// Theme
	theme_advanced_toolbar_location: "top",
	theme_advanced_toolbar_align: "left",
	theme_advanced_layout_manager: "RowLayout",
	theme_advanced_containers: "toolbar1,toolbar2,mceEditor,mceStatusbar",
	theme_advanced_containers_default_class: "mceToolbar",
	theme_advanced_containers_default_align: "left",
	theme_advanced_container_toolbar1: "undo,redo,separator,spellchecker,separator,"
	                                 + "search,replace,separator,"
	                                 + "selectall,removeformat,separator,"
	                                 + "cut,copy,paste,pastetext,pasteword,separator,"
	                                 + "print,separator,"
	                                 + "link,unlink,separator,"
	                                 + "image,table,charmap,endtoolbar",
	theme_advanced_container_toolbar2: "bold,italic,underline,strikethrough,separator,"
	                                 + "sub,sup,separator,"
	                                 + "bullist,numlist,separator,"
	                                 + "outdent,indent,separator,"
	                                 + "justifyleft,justifycenter,justifyright,justifyfull,separator,"
	                                 + "advblockformat,endtoolbar",
	
	// Plugins
	advblockformat_stylesurl: ""
};

function tinyMCEFileBrowser(field, url, type, win)
{
	var request = new Request();
	request.setMethod('admin');
	request.setPath('browser/filebrowser.tpl');
	request.setQueryVar('item', item.item);
	request.setQueryVar('variant', item.variant);
	request.setQueryVar('version', item.version);
	request.setQueryVar('api', 'tinymce');
	request.setQueryVar('field', field);
	if (type == "file")
		request.setQueryVar('type', 'link');
	else
		request.setQueryVar('type', 'image');
	win.open(request.encode(),"_blank","modal");
}

function tinyMCEEditorInit(inst)
{
	// Applies a unique style to the editor body for styling
	inst.getBody().className+=" "+inst.formTargetElementId;
}

function initialiseTinyMCE()
{
	tinyMCE.init(tinyMCEparams);
}
