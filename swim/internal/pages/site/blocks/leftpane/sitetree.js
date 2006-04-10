function textContent(element)
{
	var content = "";
	var node = element.firstChild;
	while (node)
	{
		if (node.nodeType == 3)
			content+=node.nodeValue;
		
		node = node.nextSibling;
	}
	return content;
}

BlueprintIT.widget.SiteTree = function(url, div) {
	this.location=url;
	this.element=div;
	
	YAHOO.util.Event.addListener(window, "load", this.init, this, true);
}

BlueprintIT.widget.SiteTree.prototype = {
	element: null,
	location: null,
	pages: null,
	
	init: function(event, obj) {
		this.loadTree();
	},
	
	loadItem: function(node, parentnode) {
		var details = {
			label: textContent(node),
			iconClass: node.tagName
		};
		if (node.hasAttribute("infolink")) {
			details.href = node.getAttribute("infolink");
			details.target = "main";
		}
		if (node.tagName == "category") {
		}
		else if (node.tagName == "page") {
			this.pages[node.getAttribute("path")]=true;
		}
		else if (node.tagName == "link") {
		}

		var treenode = new BlueprintIT.widget.StyledTextNode(details, parentnode, false);
		if (node.tagName == "category") {
			this.loadCategory(node, treenode);
		}
	},
	
	loadCategory: function(element, parentnode) {
		var node = element.firstChild;
		while (node) {
			if (node.nodeType == 1) {
				parentnode.expanded=true;
				this.loadItem(node, parentnode);
			}
			node = node.nextSibling;
		}
	},

	loadFromDocument: function(doc) {
		var nodes = doc.getElementsByTagName("tree");
		if (nodes.length < 1)
			return;
			
		this.pages = [];
		var tree = new YAHOO.widget.TreeView(this.element);
		var web = new YAHOO.widget.TextNode("Website", tree.getRoot(), true);
		this.loadCategory(nodes[0], web);
		var unused = new YAHOO.widget.TextNode("Unused Pages", tree.getRoot(), true);
		nodes = doc.getElementsByTagName("pages");
		if (nodes.length > 0) {
			var node = nodes[0].firstChild;
			while (node) {
				if ((node.nodeType == 1) && (node.tagName == "page")) {
					if (!this.pages[node.getAttribute("path")])
						this.loadItem(node, unused);
				}
				node = node.nextSibling;
			}
		}
		tree.draw();
	},
	
	loadTree: function() {
		var callback = {
			success: function(obj) {
				this.loadFromDocument(obj.responseXML);
			},
			argument: null,
			scope: null
		};
		callback.scope = this;
		YAHOO.util.Connect.asyncRequest("GET", this.location, callback, null);
	}
}
