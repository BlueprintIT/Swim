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
					
					// Find the current blocks and remove any style set attributes on them
					var selection = editor.getSel();
					var doc = editor.getDoc();
					var nodes = plugin.getTextNodes(plugin.getSelectionStart(doc, selection), plugin.getSelectionEnd(doc, selection));
					var block = null;
					for (var i = 0; i<nodes.length; i++) {
						var next = tinyMCE.getParentBlockElement(nodes[i]);
						if (next != block) {
							oldformat = plugin.findMatchingFormat(next);
							if (oldformat) {
								for (var attr in plugin.formats[oldformat].attributes) {
									if (attr == "class")
										next.className="";
									else
										next.removeAttribute(attr);
								}
							}
							block = next;
						}
					}

					var newformat = plugin.formats[value];
					tinyMCE.execInstanceCommand(editor_id, "FormatBlock", user_interface, "<" + newformat.tag + ">");

					// Find the new blocks and set attributes as appropriate
					var selection = editor.getSel();
					var doc = editor.getDoc();
					var nodes = plugin.getTextNodes(plugin.getSelectionStart(doc, selection), plugin.getSelectionEnd(doc, selection));
					var block = null;
					var changed = false;
					for (var i = 0; i<nodes.length; i++) {
						var next = tinyMCE.getParentBlockElement(nodes[i]);
						if (next != block) {
							if (next.tagName.toLowerCase() == newformat.tag) {
								for (var attr in newformat.attributes) {
									if (attr == "class")
										next.className = newformat.attributes[attr];
									else
										next.setAttribute(attr, newformat.attributes[attr]);
									changed = true;
								}
							}
							else
								alert("Possible issue, found a block "+next.tagName.toLowerCase()+" expected a "+newformat.tag);
							block = next;
						}
					}
					if (changed)
						tinyMCE.triggerNodeChange();
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
			var editor = tinyMCE.getInstanceById(editor_id);
			
			// Check all parent blocks. If they are all the same format then display it.
			var selection = editor.getSel();
			var doc = editor.getDoc();
			var nodes = plugin.getTextNodes(plugin.getSelectionStart(doc, selection), plugin.getSelectionEnd(doc, selection));
			var block = null;
			for (var i = 0; i<nodes.length; i++) {
				var next = tinyMCE.getParentBlockElement(nodes[i]);
				if (next != block) {
					var foundformat = plugin.findMatchingFormat(next);
					if (!foundformat) {
						format = null;
						break;
					}
					if (!block)
						format = foundformat;
					else if (format != foundformat) {
						format = null;
						break;
					}
					block = next;
				}
			}

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
	
	seekRangeStart: function(context, seeker, range)
	{
		if (!context.hasChildNodes)
			return context;
		
		var lastel = context.firstChild;
		var check = lastel;
		while (check) {
			if (check.nodeType == 1) {
				seeker.moveToElementText(check);
				var stcheck = seeker.compareEndPoints("StartToStart", range);
				var edcheck = seeker.compareEndPoints("EndToStart", range);
				if (stcheck > 0)
					return lastel;
				if (edcheck >= 0)
					return this.seekRangeEnd(check, seeker, range);
				lastel = check.nextSibling;
			}
			check = check.nextSibling;
		}
		return lastel;
	},
	
	seekRangeEnd: function(context, seeker, range)
	{
		if (!context.hasChildNodes)
			return context;
		
		var lastel = context.lastChild;
		var check = lastel;
		while (check) {
			if (check.nodeType == 1) {
				seeker.moveToElementText(check);
				var stcheck = seeker.compareEndPoints("StartToEnd", range);
				var edcheck = seeker.compareEndPoints("EndToEnd", range);
				if (edcheck < 0)
					return lastel;
				if (stcheck <= 0)
					return this.seekRangeEnd(check, seeker, range);
				lastel = check.previousSibling;
			}
			check = check.previousSibling;
		}
		return lastel;
	},
	
	getSelectionStart: function(doc, selection)
	{
		if (selection.getRangeAt)
			return selection.getRangeAt(0).endContainer;
		
		var range = selection.createRange();
		var seeker = range.duplicate();
		seeker.moveToElementText(doc.body);
		if (seeker.compareEndPoints("StartToStart", range)==0)
			return doc.body;
		
		return this.seekRangeStart(doc.body, seeker, range);
	},
	
	getSelectionEnd: function(doc, selection)
	{
		if (selection.getRangeAt)
			return selection.getRangeAt(0).endContainer;
		
		var range = selection.createRange();
		var seeker = range.duplicate();
		seeker.moveToElementText(doc.body);
		if (seeker.compareEndPoints("EndToEnd", range)==0)
			return doc.body;
		
		return this.seekRangeEnd(doc.body, seeker, range);
	},
	
	/**
	 * Scans from a start node to an end node and returns all the
	 * non-ignorable text nodes in order.
	 */
	getTextNodes: function(start, end)
	{
		var nodes = [];
		if (!end)
			end = start;
		var context = start;
		var ignorable = false;
		if (start.firstChild) {
			context = start.firstChild;
			ignorable = true;
		}
		var whitespace = /^\s*$/;
		while (true) {
		
			// Push text node onto stack if not ignorable
			if (context.nodeType == 3) {
				if (ignorable && !whitespace.test(context.nodeValue))
					ignorable = false;
				if (!ignorable)
					nodes.push(context);
			}
			
			// Recurse into node if necessary
			if (context.firstChild) {
				// Backtrack and remove any ignorable text nodes
				if (!ignorable) {
					var backtrack = context;
					while ((backtrack) && (backtrack.nodeType == 3) && (whitespace.test(backtrack.nodeValue))) {
						nodes.pop();
						backtrack = backtrack.previousSibling;
					}
					ignorable = true;
				}
				
				context = context.firstChild;
				continue;
			}
			
			// At the end of this node, bail if it's the last			
			if (context == end)
				return nodes;
			
			if (context.nextSibling)
				context = context.nextSibling;
			else {
				// Backtrack and remove any ignorable text nodes
				if (!ignorable) {
					var backtrack = context;
					while ((backtrack) && (backtrack.nodeType == 3) && (whitespace.test(backtrack.nodeValue))) {
						nodes.pop();
						backtrack = backtrack.previousSibling;
					}
					ignorable = true;
				}
				
				// Find the next node to check
				while (!context.nextSibling) {
					if (context == end)
						return nodes;
					context = context.parentNode;
				}
				context = context.nextSibling;
			}
		}
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
	
	/**
	 * Checks the http request to see if its done and if so load the formats.
	 */
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
					style.tag = nodes[i].getAttribute("element").toLowerCase();
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
