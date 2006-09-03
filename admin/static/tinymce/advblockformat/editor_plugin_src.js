/*
 * Swim
 *
 * Advanced block format plugin for TinyMCE
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('advblockformat', 'en,tr,he,nb,ru,ru_KOI8-R,ru_UTF-8,nn,fi,cy,es,is,pl'); // <- Add a comma separated list of all supported languages

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

// Singleton class
var TinyMCE_AdvBlockFormatPlugin = {
	/**
	 * Returns information about the plugin as a name/value array.
	 * The current keys are longname, author, authorurl, infourl and version.
	 *
	 * @returns Name/value array containing information about the plugin.
	 * @type Array 
	 */
	getInfo : function() {
		return {
			longname : 'Advanced Block Format plugin',
			author : 'Dave Townsend',
			authorurl : 'http://www.blueprintit.co.uk',
			infourl : 'http://www.blueprintit.co.uk',
			version : "1.0"
		};
	},

	formats: [
		{
			name: "Heading 1",
			tag: "h1",
			attributes: {}
		},
		{
			name: "Heading 2",
			tag: "h2",
			attributes: {}
		},
		{
			name: "Heading 3",
			tag: "h2",
			attributes: {}
		},
		{
			name: "Normal",
			tag: "p",
			attributes: {}
		},
	],
	
	editors: [],
	
	loading: false,
	loaded: false,
	request: false,
	
	/**
	 * Gets executed when a TinyMCE editor instance is initialized.
	 *
	 * @param {TinyMCE_Control} Initialized TinyMCE editor control instance. 
	 */
	initInstance : function(inst) {
		var plugin = TinyMCE_AdvBlockFormatPlugin;
		plugin.editors.push(tinyMCE.getEditorId(inst.formTargetElementId));
		if (!plugin.loading) {
			plugin.loading = true;
			var url = tinyMCE.getParam("advblockformat_stylesurl", null)
			if (url) {
				plugin.request = plugin.makeXMLHttpRequest();
				if (plugin.request) {
					plugin.request.open("GET", url, true);
					plugin.request.send("");
					window.setTimeout(plugin.processReqChange, 100);
				}
				else
					plugin.loaded = true;
			}
		}
	},

	/**
	 * Returns the HTML code for a specific control or empty string if this plugin doesn't have that control.
	 * A control can be a button, select list or any other HTML item to present in the TinyMCE user interface.
	 * The variable {$editor_id} will be replaced with the current editor instance id and {$pluginurl} will be replaced
	 * with the URL of the plugin. Language variables such as {$lang_somekey} will also be replaced with contents from
	 * the language packs.
	 *
	 * @param {string} cn Editor control/button name to get HTML for.
	 * @return HTML code for a specific control or empty string.
	 * @type string
	 */
	getControlHTML : function(cn) {
		var plugin = TinyMCE_AdvBlockFormatPlugin;
		switch (cn) {
			case "advblockformat":
				var html = '<select id="{$editor_id}_advblockformat" title="' + tinyMCE.getLang('lang_advblockformat_title') + '" name="{$editor_id}_advblockformat" onfocus="tinyMCE.addSelectAccessibility(event, this, window);" onchange="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceAdvFormatBlock\',false,this.options[this.selectedIndex].value);" class="mceSelectList">';

				if (!plugin.loaded) {
					html += '<option disabled="disabled" value="-1">' + tinyMCE.getLang('lang_advblockformat_loading') + '</option>';
				}
				else {
					html += '<option disabled="disabled" value="-1">' + tinyMCE.getLang('lang_advblockformat_unknown') + '</option>';
					for (var i in plugin.formats)
						html += '<option value="' + i + '">' + plugin.formats[i].name + '</option>';
				}

				html += '</select>';

				return html;
		}

		return "";
	},

	/**
	 * Executes a specific command, this function handles plugin commands.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that issued the command.
	 * @param {HTMLElement} element Body or root element for the editor instance.
	 * @param {string} command Command name to be executed.
	 * @param {string} user_interface True/false if a user interface should be presented.
	 * @param {mixed} value Custom value argument, can be anything.
	 * @return true/false if the command was executed by this plugin or not.
	 * @type
	 */
	execCommand : function(editor_id, element, command, user_interface, value) {
		var plugin = TinyMCE_AdvBlockFormatPlugin;
		switch (command) {
			case "mceAdvFormatBlock":
				if (plugin.loaded && plugin.formats[value]) {
					var editor = tinyMCE.getInstanceById(editor_id);
					var block = tinyMCE.getParentBlockElement(editor.getFocusElement());
					var oldformat = null;
					if (block) {
						oldformat = plugin.findMatchingFormat(block);
						if (oldformat)
							oldformat = plugin.formats[oldformat];
					}
					var newformat = plugin.formats[value];

					if (oldformat != newformat) {
						if (!block || block.tagName != newformat.tag) {
							// Change the block using standard commands.
							tinyMCE.execInstanceCommand(editor_id, "FormatBlock", user_interface, "<" + newformat.tag + ">");
							block = tinyMCE.getParentBlockElement(editor.getFocusElement());
						}
	
						// Set attributes from new format
						for (var attr in newformat.attributes) {
							block.setAttribute(attr, newformat.attributes[attr]);
						}
						
						if (oldformat) {
							// Remove any unnecessary attributes from old format
							for (var attr in oldformat.attributes) {
								if (!newformat.attributes[attr])
									block.removeAttribute(attr);
							}
						}
						
						tinyMCE.triggerNodeChange();
					}
				}
				return true;
		}

		// Pass to next handler in chain
		return false;
	},

	/**
	 * Gets called ones the cursor/selection in a TinyMCE instance changes. This is useful to enable/disable
	 * button controls depending on where the user are and what they have selected. This method gets executed
	 * alot and should be as performance tuned as possible.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was changed.
	 * @param {HTMLNode} node Current node location, where the cursor is in the DOM tree.
	 * @param {int} undo_index The current undo index, if this is -1 custom undo/redo is disabled.
	 * @param {int} undo_levels The current undo levels, if this is -1 custom undo/redo is disabled.
	 * @param {boolean} visual_aid Is visual aids enabled/disabled ex: dotted lines on tables.
	 * @param {boolean} any_selection Is there any selection at all or is there only a cursor.
	 */
	handleNodeChange : function(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
		var plugin = TinyMCE_AdvBlockFormatPlugin;
		var select = document.getElementById(editor_id + "_advblockformat");
		if (plugin.loaded && select) {
			var format = null;
			var block = tinyMCE.getParentBlockElement(node);
			if (block)
				format = plugin.findMatchingFormat(block);
			if (format)
				select.value = format;
			else
				select.value = -1;
		}
	},

	/**
	 * Gets called when a TinyMCE editor instance gets filled with content on startup.
	 *
	 * @param {string} editor_id TinyMCE editor instance id that was filled with content.
	 * @param {HTMLElement} body HTML body element of editor instance.
	 * @param {HTMLDocument} doc HTML document instance.
	 */
	setupContent : function(editor_id, body, doc) {
	},

	/**
	 * Gets called when the contents of a TinyMCE area is modified, in other words when a undo level is
	 * added.
	 *
	 * @param {TinyMCE_Control} inst TinyMCE editor area control instance that got modified.
	 */
	onChange : function(inst) {
	},

	/**
	 * Gets called when TinyMCE handles events such as keydown, mousedown etc. TinyMCE
	 * doesn't listen on all types of events so custom event handling may be required for
	 * some purposes.
	 *
	 * @param {Event} e HTML editor event reference.
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean
	 */
	handleEvent : function(e) {
		return true; // Pass to next handler
	},

	/**
	 * Gets called when HTML contents is inserted or retrived from a TinyMCE editor instance.
	 * The type parameter contains what type of event that was performed and what format the content is in.
	 * Possible valuses for type is get_from_editor, insert_to_editor, get_from_editor_dom, insert_to_editor_dom.
	 *
	 * @param {string} type Cleanup event type.
	 * @param {mixed} content Editor contents that gets inserted/extracted can be a string or DOM element.
	 * @param {TinyMCE_Control} inst TinyMCE editor instance control that performes the cleanup.
	 * @return New content or the input content depending on action.
	 * @type string
	 */
	cleanup : function(type, content, inst) {
		return content;
	},

	/**
	 * Called to find a format in our list that matches the given block element as closely as possible.
	 *
	 * @param {element} element Block level element.
	 */
	findMatchingFormat: function(element) {
		var tag = element.tagName.toLowerCase();
		for (var i in this.formats) {
			if (this.formats[i].tag == tag) {
				var match = true;
				for (var attr in this.formats[i].attributes) {
					if (element.getAttribute(attr)!=this.formats[i].attributes[attr]) {
						match = false;
						break;
					}
				}
				if (match)
					return i;
			}
		}
		return null;
	},
	
	/**
	 * Creates an XMLHttpRequest in most browsers.
	 */
	makeXMLHttpRequest: function() {
		if (window.XMLHttpRequest) {
			try {
				return new XMLHttpRequest();
			}
			catch (e) {
				return null;
			}
		}
		else if (window.ActiveXObject) {
			try {
				return new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					return new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e) {
					return null;
				}
			}
		}
		return null;
	},
	
	processReqChange: function() {
		var plugin = TinyMCE_AdvBlockFormatPlugin;
		if (plugin.request.readyState == 4) {
			if (plugin.request.status == 200) {
				plugin.formats = [];
				
				// Parse the xml to build the formats list
				var nodes = plugin.request.responseXML.documentElement.getElementsByTagName("Style");
				for (var i=0; i<nodes.length; i++) {
					var style = {};
					style.name = nodes[i].getAttribute("name");
					style.tag = nodes[i].getAttribute("element");
					style.attributes = {};
					var attrs = nodes[i].getElementsByTagName("Attribute");
					for (var j=0; j<attrs.length; j++) {
						var name = attrs[j].getAttribute("name");
						var value = attrs[j].getAttribute("value");
						if (name != "style")
							style.attributes[name] = value;
					}
					plugin.formats.push(style);
				}
			}
			for (var i in plugin.editors) {
			
				// Update all the editors controls.
				var editor_id = plugin.editors[i];
				var select = document.getElementById(editor_id + "_advblockformat");
				if (select) {
					// Insert the options into the UI.
					select.innerHTML = '';
					var option = document.createElement("option");
					option.setAttribute("disabled", "disabled");
					option.setAttribute("value", "-1");
					option.innerHTML = tinyMCE.getLang('lang_advblockformat_unknown');
					select.appendChild(option);

					for (var i in plugin.formats) {
						option = document.createElement("option");
						option.setAttribute("value", i);
						option.innerHTML = plugin.formats[i].name;
						select.appendChild(option);
					}
					
					// Select the right option.
					var inst = tinyMCE.getInstanceById(editor_id);					
					plugin.handleNodeChange(editor_id, inst.getFocusElement());
				}
			}
			plugin.loaded = true;
			plugin.request = null;
		}
		else
			window.setTimeout(plugin.processReqChange, 100);
	}
};

// Adds the plugin class to the list of available TinyMCE plugins
tinyMCE.addPlugin("advblockformat", TinyMCE_AdvBlockFormatPlugin);
