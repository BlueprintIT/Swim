BlueprintIT.widget.ItemNode = function(oId, oLabel, oType, oPublished, oContents, oParent) {
	var oData = {
		id: oId,
		label: oLabel,
		published: oPublished,
		type: oType,
		contains: oContents,
		selected: false
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
	if (this.data.selected)
		style+= " selected";
	
	return style;
}

BlueprintIT.widget.ItemNode.prototype.setSelected = function(oValue) {
	if (oValue) {
		YAHOO.util.Dom.addClass(this.getContentEl(), "selected");
	} else {
		YAHOO.util.Dom.removeClass(this.getContentEl(), "selected");
	}
	this.data.selected = oValue;
}

BlueprintIT.widget.ItemNode.prototype.setLabel = function(oLabel) {
	this.data.label = oLabel;
	this.initContent(this.data, true);
	this.getContentEl().innerHTML = this.html;
}

BlueprintIT.widget.ItemNode.prototype.setPublished = function(oPublished) {
	this.data.published = oPublished;
	this.getContentEl().className = this.getContentStyle();
}

BlueprintIT.widget.SiteTree = function(url, div, data) {
	this.location=url;
	this.element=div;
	this.loading=true;
	this.siteData = data;
	
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
	siteData: null,
	
	log: function(message, obj) {
		YAHOO.log("[SiteTree] "+message, "info", obj);
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
	
	getItems: function(id) {
		if (this.items[id])
			return this.items[id];
		return null;
	},
	
	selectItem: function(id) {
		if (this.loading) {
			this.selected = id;
			return;
		}
		
		if (this.selected) {
			for (var i = 0; i<this.items[this.selected].length; i++)
				this.items[this.selected][i].setSelected(false);
			this.selected = null;
		}
		if (id && this.items[id]) {
			for (var i = 0; i<this.items[id].length; i++)
				this.items[id][i].setSelected(true);
			this.selected = id;
		}
	},
	
	loadItem: function(item, parentnode) {
		var label = item["name"];
		var type = item["class"];
		var published = item["published"] == "true";
		var contents = null;
		
		if (!label)
			label = '[Unnamed]';
		
		var id = item["id"];
		if (id) {
			if (!this.items[id])
				this.items[id] = [];
		}
		
		if (item["contains"]) {
			contents = {};
			var content = item["contains"].split(",");
			for (var i = 0; i<content.length; i++)
				contents[content[i]] = true;
		}
			
		var treenode = new BlueprintIT.widget.ItemNode(id, label, type, published, contents, parentnode);
		if (id)
			this.items[id].push(treenode);
		
		this.loadCategory(item["subitems"], treenode);
	},
	
	loadCategory: function(root, treenode) {
		if (root && root.length>0) {
			treenode.expanded = true;
			for (var i=0; i<root.length; i++)
				this.loadItem(root[i], treenode);
		}
	},

	loadFromDocument: function(root) {
		this.items = [];
		this.tree = null;
		if (this.draggable)
			this.tree = new BlueprintIT.widget.DraggableTreeView(this.element, this);
		else
			this.tree = new YAHOO.widget.TreeView(this.element);
		this.loadCategory(root, this.tree.getRoot());
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
		if (this.siteData) {
			this.loadFromDocument(this.siteData);
			this.siteData = null;
			BlueprintIT.dialog.Wait.hide();
		}
		else {
			var callback = {
				success: function(obj) {
					var root = obj.responseText.parseJSON();
					this.log("load complete", root);
					this.loadFromDocument(root);
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
}
