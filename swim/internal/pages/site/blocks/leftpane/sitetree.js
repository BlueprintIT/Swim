YAHOO.util.Dom.textContent = function(el) {
	el = this.get(el);
	var content = "";
	var node = el.firstChild;
	while (node) {
		if (node.nodeType == 3)
			content+=node.nodeValue;
		
		node = node.nextSibling;
	}
	return content;
}

YAHOO.util.Dom.addClass = function(el, name) {
	el = this.get(el);
	if (el)
		el.className+=" "+name;
}

YAHOO.util.Dom.removeClass = function(el, name) {
	el = this.get(el);
	if (el) {
		var classes = el.className.split(' ');
		if (classes.length>0) {
			var newclass = '';
			for (var k in classes) {
				if (classes[k]!=name)
					newclass+=' '+classes[k];
			}
			el.className=newclass.substring(1,newclass.length);
		}
	}
}

BlueprintIT.widget.SiteTree = function(siteadmin, url, div) {
	this.location=url;
	this.element=div;
	this.baselocation=siteadmin;
	this.loading=true;
	
	YAHOO.util.Event.addListener(window, "load", this.init, this, true);
}

BlueprintIT.widget.SiteTree.prototype = {
	element: null,
	location: null,
	baselocation: null,
	items: null,
	selected: null,
	loading: null,
	
	init: function(event, obj) {
		this.loadTree();
	},
	
	selectCategory: function(category) {
		this.selectItem("category/"+category);
	},
	
	selectPage: function(page) {
		this.selectItem("page/"+page);
	},
	
	selectItem: function(id) {
		if (this.loading) {
			this.selected = id;
			return;
		}
		
		if (this.selected) {
			for (var i = 0; i<this.items[this.selected].length; i++) {
				var label = this.items[this.selected][i].getLabelEl();
				YAHOO.util.Dom.removeClass(label, "selected");
			}
			this.selected = null;
		}
		if (id && this.items[id]) {
			for (var i = 0; i<this.items[id].length; i++) {
				var label = this.items[id][i].getLabelEl();
				YAHOO.util.Dom.addClass(label, "selected");
			}
			this.selected = id;
		}
	},
	
	loadItem: function(node, parentnode) {
		var details = {
			label: YAHOO.util.Dom.textContent(node),
			iconClass: node.tagName
		};
		if (node.getAttribute("infolink")) {
			details.href = node.getAttribute("infolink");
			details.target = "main";
		}
		var id = node.tagName+"/";
		if (node.tagName == "category") {
			id+=node.getAttribute("id");
		}
		else if (node.tagName == "page") {
			id+=node.getAttribute("path");
		}
		else if (node.tagName == "link") {
			id+=node.getAttribute("id");
		}
		if (!this.items[id]) {
			this.items[id] = [];
		}
		
		var treenode = new BlueprintIT.widget.StyledTextNode(details, parentnode, false);
		this.items[id].push(treenode);
		
		if (node.tagName == "category") {
			this.loadCategory(node, treenode);
		}
	},
	
	loadCategory: function(element, treenode) {
		var node = element.firstChild;
		while (node) {
			if (node.nodeType == 1) {
				treenode.expanded=true;
				this.loadItem(node, treenode);
			}
			node = node.nextSibling;
		}
	},

	loadFromDocument: function(doc) {
		var node = doc.getElementsByTagName("tree");
		
		if (!node || node.length==0)
			return;
		
		this.items = [];
		var tree = new YAHOO.widget.TreeView(this.element);
		this.loadCategory(node[0], tree.getRoot());
		var unused = null;
		var nodes = doc.getElementsByTagName("pages");
		if (nodes.length > 0) {
			var node = nodes[0].firstChild;
			while (node) {
				if ((node.nodeType == 1) && (node.tagName == "page")) {
					if (!this.items["page/"+node.getAttribute("path")]) {
						if (!unused)
							unused = new YAHOO.widget.TextNode("Uncategorised Pages", tree.getRoot(), true);
						this.loadItem(node, unused);
					}
				}
				node = node.nextSibling;
			}
		}
		tree.draw();
		this.loading = false;
		if (this.selected) {
			var selected = this.selected;
			this.selected = null;
			this.selectItem(selected);
		}
	},
	
	loadTree: function() {
		this.loading = true;
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
