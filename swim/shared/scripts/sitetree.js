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
	
	onDragDrop: function(node, point) {
		console.log("Drop " + node.data.id + " on " + point.parent.data.id + " at " + point.position);
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
			label: node.getAttribute("name"),
			iconClass: node.getAttribute("class"),
			type: node.getAttribute("class"),
			contains: []
		};
		
		if (node.getAttribute("id")) {
			var id = node.getAttribute("id");
			if (!this.items[id]) {
				this.items[id] = [];
			}
			details.id = id;
			details.href = "javascript:onTreeItemClick('"+id+"')";
		}
		
		if (node.getAttribute("contains")) {
			var content = node.getAttribute("contains").split(",");
			for (var i = 0; i<content.length; i++)
				details.contains[content[i]] = true;
		}
			
		var treenode = new BlueprintIT.widget.StyledTextNode(details, parentnode, false);
		if (node.getAttribute("id")) {
			this.items[id].push(treenode);
		}
		
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
		var tree = new BlueprintIT.widget.DraggableTreeView(this.element, this);
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
