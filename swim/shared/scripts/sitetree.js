BlueprintIT.widget.ItemNode = function(oId, oLabel, oType, oContents, oParent) {
	var html = oLabel;
	if (oId) {
		html = '<a href="javascript:onTreeItemClick(\''+oId+'\')">' + html + '</a>';
	}
	oData = {
		html: html,
		type: oType,
		contains: oContents
	};
	this.init(oData, oParent, false);
  this.labelElId = "ygtvlabelel" + this.index;
	oData.html = '<div id="'+this.labelElId+'">' + html + '</div>';
	this.initContent(oData, true);
};

YAHOO.extend(BlueprintIT.widget.ItemNode, YAHOO.widget.HTMLNode);

BlueprintIT.widget.ItemNode.prototype.labelElId = null;
BlueprintIT.widget.ItemNode.prototype.getLabelEl = function() {
	return document.getElementById(this.labelElId);
}

BlueprintIT.widget.ItemNode.prototype.getContentStyle = function() {
	var style = "";
	var typ = "item";
	if (this.hasChildren(true))
		typ = "container";
		
	if (this.expanded)
		style = "itemcontent " + typ + "_open icon_"+this.data.type+" icon_"+this.data.type+"_open";
	else
		style = "itemcontent " + typ + "_clsd icon_"+this.data.type+" icon_"+this.data.type+"_clsd";

	return style;
}

BlueprintIT.widget.ItemNode.prototype.toggle = function() {
	BlueprintIT.widget.ItemNode.superclass.toggle.call(this);

	this.getContentEl().className = this.getContentStyle();
}

BlueprintIT.widget.ItemNode.prototype.getNodeHtml = function() {
	this.contentStyle = this.getContentStyle();
	
	return BlueprintIT.widget.ItemNode.superclass.getNodeHtml.call(this);
}

BlueprintIT.widget.SiteTree = function(url, div) {
	this.location=url;
	this.element=div;
	this.loading=true;
	
	YAHOO.util.Event.addListener(window, "load", this.init, this, true);
}

BlueprintIT.widget.SiteTree.prototype = {
	element: null,
	location: null,
	items: null,
	selected: null,
	loading: null,
	draggable: false,
	dragging: false,
	
	onDragStart: function() {
		this.dragging = true;
	},
	
	onDragEnd: function() {
		var self = this;
		window,setTimeout(function() { self.dragging = false }, 100);
	},
	
	canHold: function(parent, child) {
		if (parent.tree.getRoot() == parent)
			return false;
		
		if (parent.data.contains && child.data.type && parent.data.contains[child.data.type])
			return true;
		
		return false;
	},
	
	canDrag: function(node) {
		if (node.parent == node.tree.getRoot())
			return false;
			
		return true;
	},
	
	onDragDrop: function(node, parent, position) {
		//console.log("Drop " + node.data.id + " on " + point.parent.data.id + " at " + point.position);
	},
	
	init: function(event, obj) {
		this.loadTree();
	},
	
	selectItem: function(id) {
		if (this.loading) {
			this.selected = id;
			return;
		}
		
		if (this.selected) {
			for (var i = 0; i<this.items[this.selected].length; i++) {
				var label = this.items[this.selected][i].getContentEl();
				YAHOO.util.Dom.removeClass(label, "selected");
			}
			this.selected = null;
		}
		if (id && this.items[id]) {
			for (var i = 0; i<this.items[id].length; i++) {
				var label = this.items[id][i].getContentEl();
				YAHOO.util.Dom.addClass(label, "selected");
			}
			this.selected = id;
		}
	},
	
	loadItem: function(node, parentnode) {
		var label = node.getAttribute("name");
		var type = node.getAttribute("class");
		var contents = [];
		
		if (!label)
			label = '[Unnamed]';
		
		var id = node.getAttribute("id");
		if (id) {
			if (!this.items[id]) {
				this.items[id] = [];
			}
		}
		
		if (node.getAttribute("contains")) {
			var content = node.getAttribute("contains").split(",");
			for (var i = 0; i<content.length; i++)
				contents[content[i]] = true;
		}
			
		var treenode = new BlueprintIT.widget.ItemNode(id, label, type, contents, parentnode);
		if (id)
			this.items[id].push(treenode);
		
		this.loadCategory(node, treenode);
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
		this.items = [];
		var tree = null;
		if (this.draggable)
			tree = new BlueprintIT.widget.DraggableTreeView(this.element, this);
		else
			tree = new YAHOO.widget.TreeView(this.element);
		this.loadCategory(doc.documentElement, tree.getRoot());
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
