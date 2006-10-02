BlueprintIT.widget.ItemNode = function(oId, oLabel, oType, oPublished, oContents, oParent) {
	var oData = {
		id: oId,
		label: oLabel,
		published: oPublished,
		type: oType,
		contains: oContents
	};
	if (oId) {
		oData.href = 'javascript:onTreeItemClick(\''+oId+'\')'
	}
	this.init(oData, oParent, false);
};

YAHOO.extend(BlueprintIT.widget.ItemNode, BlueprintIT.widget.IconNode);

BlueprintIT.widget.ItemNode.prototype.getContentStyle = function() {
	var style = "site_itemcontent";
	var parg = "";
	if (this.data.published)
		parg="_published";
	if (this.data.contains)
	{
		var state = "clsd";
		if (this.expanded)
			state = "open";
		
		style+= " site_container_"+state+parg;
		style+= " site_icon_"+this.data.type+parg+" site_icon_"+this.data.type+"_"+state+parg;
	}
	else
	{
		style+= " site_item"+parg;
		style+= " site_icon_"+this.data.type+parg;
	}
	
	return style;
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
	tree: null,
	expandAnim: null,
	collapseAnim: null,
	dragMode: null,
	
	log: function(message) {
		YAHOO.log("[SiteTree] "+message);
	},
	
	onDragStart: function() {
		this.dragging = true;
	},
	
	onDragEnd: function() {
		var self = this;
		window.setTimeout(function() { self.dragging = false }, 100);
	},
	
	canHold: function(parent, child, mode) {
		// Cannot drop at root level
		if (parent.tree.getRoot() == parent)
			return false;
		
		if (parent.data.id == 'uncat') {
			// Cannot copy to uncategorised
			if (mode == BlueprintIT.widget.DraggableTreeView.DRAG_COPY)
				return false;

			// Uncategorised cannot be reordered
			if (child.parent == parent)
				return false;
		}
		
		//console.log("Checking drop of "+child.data.id+" on "+parent.data.id);
		if (parent.data.contains && child.data.type && parent.data.contains[child.data.type])
			return true;
		
		return false;
	},
	
	canDrag: function(node) {
		if (node.parent == node.tree.getRoot())
			return false;
			
		return true;
	},
	
	onDragDrop: function(node, parent, position, mode) {
		var valid = false;
		var request = new Request();
		request.setMethod("mutatesequence");
		request.setQueryVar("item", node.data.id);
		request.setQueryVar("action", "move");
		if ((mode == BlueprintIT.widget.DraggableTreeView.DRAG_MOVE) && (node.parent.data.id != 'uncat')) {
			var findpos = node;
			var pos = 0;
			while (findpos.previousSibling) {
				pos++;
				findpos = findpos.previousSibling;
			}
			//console.log("Remove " + node.data.id + " from " + node.parent.data.id + " at " + pos);
			request.setQueryVar("removeitem", node.parent.data.id);
			request.setQueryVar("removepos", pos);
			valid = true;
			if ((parent == node.parent) && (pos < position))
				position--;
		}
		if (parent.data.id != 'uncat') {
			//console.log("Add " + node.data.id + " to " + parent.data.id + " at " + position);
			request.setQueryVar("insertitem", parent.data.id);
			request.setQueryVar("insertpos", position);
			valid = true;
		}
		
		if (valid) {
			var callback = {
				success: function(obj) {
					this.loadTree();
				},
				failure: function(obj) {
					alert("Operation failed");
					this.loadTree();
				},
				argument: null,
				scope: null
			};
			callback.scope = this;
			YAHOO.util.Connect.asyncRequest("GET", request.encode(), callback, null);
		}
	},
	
	init: function(event, obj) {
		this.log("init");
		this.loadTree();
	},
	
	setDragMode: function(mode) {
		if (this.tree)
			this.tree.setDefaultDragMode(mode);
		this.dragMode = mode;
	},
	
	setExpandAnim: function(anim) {
		if (this.tree)
			this.tree.setExpandAnim(anim);
		this.expandAnim = anim;
	},
	
	setCollapseAnim: function(anim) {
		if (this.tree)
			this.tree.setCollapseAnim(anim);
		this.collapseAnim = anim;
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
		var published = node.getAttribute("published")=="true";
		var contents = null;
		
		if (!label)
			label = '[Unnamed]';
		
		var id = node.getAttribute("id");
		if (id) {
			if (!this.items[id]) {
				this.items[id] = [];
			}
		}
		
		if (node.getAttribute("contains")) {
			contents = {};
			var content = node.getAttribute("contains").split(",");
			for (var i = 0; i<content.length; i++)
				contents[content[i]] = true;
		}
			
		var treenode = new BlueprintIT.widget.ItemNode(id, label, type, published, contents, parentnode);
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
		this.tree = null;
		if (this.draggable)
			this.tree = new BlueprintIT.widget.DraggableTreeView(this.element, this);
		else
			this.tree = new YAHOO.widget.TreeView(this.element);
		this.loadCategory(doc.documentElement, this.tree.getRoot());
		this.log("data parsed");
		if (this.dragMode)
			this.tree.setDefaultDragMode(this.dragMode);
		if (this.expandAnim)
			this.tree.setExpandAnim(this.expandAnim);
		if (this.collapseAnim)
			this.tree.setCollapseAnim(this.collapseAnim);
		this.tree.draw();
		this.log("tree drawn");
		this.loading = false;
		if (this.selected) {
			var selected = this.selected;
			this.selected = null;
			this.selectItem(selected);
		}
	},
	
	loadTree: function() {
		this.log("loadTree");
		BlueprintIT.dialog.Wait.show("Updating Site Structure...");
		this.loading = true;
		var callback = {
			success: function(obj) {
				this.log("load complete");
				this.loadFromDocument(obj.responseXML);
				this.log("finished");
				BlueprintIT.dialog.Wait.hide();
			},
			failure: function(obj) {
				this.log("load failed");
				BlueprintIT.dialog.Wait.hide();
				this.loading = false;
				BlueprintIT.dialog.Alert.show("Error", "There was a problem retrieving the site structure.<br>Please try logging out and in again.");
			},
			argument: null,
			scope: null
		};
		callback.scope = this;
		YAHOO.util.Connect.asyncRequest("GET", this.location, callback, null);
	}
}
