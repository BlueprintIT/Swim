BlueprintIT.widget.ItemNode = function(oId, oLabel, oType, oContents, oParent) {
	var html = oLabel;
	if (oId) {
		html = '<a href="javascript:onTreeItemClick(\''+oId+'\')">' + html + '</a>';
	}
	oData = {
		id: oId,
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
	if (this.data.contains)
	{
		if (this.expanded)
			style = "itemcontent container_open icon_"+this.data.type+" icon_"+this.data.type+"_open";
		else
			style = "itemcontent container_clsd icon_"+this.data.type+" icon_"+this.data.type+"_clsd";
	}
	else
	{
		style = "itemcontent item icon_"+this.data.type;
	}
		

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
	tree: null,
	expandAnim: null,
	collapseAnim: null,
	
	onDragStart: function() {
		this.dragging = true;
	},
	
	onDragEnd: function() {
		var self = this;
		window,setTimeout(function() { self.dragging = false }, 100);
	},
	
	canHold: function(parent, child) {
		// Cannot drop at root level
		if (parent.tree.getRoot() == parent)
			return false;
		
		// Uncategorised cannot be reordered
		if ((parent.data.id == 'uncat') && (child.parent == parent))
			return false;
		
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
	
	onDragDrop: function(node, parent, position) {
		var valid = false;
		var request = new Request();
		request.setMethod("mutatesequence");
		request.setQueryVar("item", node.data.id);
		request.setQueryVar("action", "move");
		if (node.parent.data.id != 'uncat') {
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
		}
		if (parent.data.id != 'uncat') {
			if ((parent == node.parent) && (pos < position))
				position--;
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
		this.loadTree();
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
		this.tree = null;
		if (this.draggable)
			this.tree = new BlueprintIT.widget.DraggableTreeView(this.element, this);
		else
			this.tree = new YAHOO.widget.TreeView(this.element);
		this.loadCategory(doc.documentElement, this.tree.getRoot());
		if (this.expandAnim)
			this.tree.setExpandAnim(this.expandAnim);
		if (this.collapseAnim)
			this.tree.setCollapseAnim(this.collapseAnim);
		this.tree.draw();
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
