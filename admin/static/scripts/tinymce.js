tinyMCEparams = {
	// General
	mode: "textareas",
	theme: "advanced",
	plugins: "print,spellchecker,paste,searchreplace,table",
	editor_selector: "HTMLEditor",
	button_tile_map: true,
	dialog_type: "modal",
	
	// Cleanup/Output
	cleanup_on_startup: true,
	remove_linebreaks: false,
	fix_list_elements: true,
	fix_table_elements: true,
	
	// URL
	relative_urls: false,
	remove_script_host: true,

	// Callbacks
	init_instance_callback: "tinyMCEEditorInit",
	
	// Layout
	content_css: "../../swim/shared/yui/reset/reset-min.css,../../swim/shared/yui/fonts/fonts-min.css",

	// Theme
	theme_advanced_toolbar_location: "top",
	theme_advanced_toolbar_align: "left",
	theme_advanced_statusbar_location: "bottom",
	theme_advanced_buttons1: "undo,redo,separator,spellchecker,separator,"
	                        +"search,replace,separator,"
	                        +"selectall,removeformat,separator,"
	                        +"cut,copy,paste,pastetext,pasteword,separator,"
	                        +"print,separator,"
	                        +"link,unlink,separator,"
	                        +"image,table,charmap",
	theme_advanced_buttons2: "bold,italic,underline,strikethrough,separator,"
	                        +"sub,sup,separator,"
	                        +"bullist,numlist,separator,"
	                        +"outdent,indent,separator,"
	                        +"justifyleft,justifycenter,justifyfull,separator",
	theme_advanced_buttons3: "",
	theme_advanced_path: true
};

function tinyMCEEditorInit(inst)
{
	// Applies a unique style to the editor body for styling
	inst.getBody().className+=" "+inst.formTargetElementId;
}

function initialiseTinyMCE()
{
	tinyMCE.init(tinyMCEparams);
}
