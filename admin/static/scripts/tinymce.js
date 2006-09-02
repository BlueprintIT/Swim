tinyMCEparams = {
	// General
	mode: "textareas",
	editor_selector: "HTMLEditor",
	theme: "advanced",
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
	content_css: "../../swim/shared/yui/reset/reset-min.css,../../swim/shared/yui/fonts/fonts-min.css"
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
