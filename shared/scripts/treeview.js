BlueprintIT.widget.IconNode = function(oData, oParent, expanded) {
	this.init(oData, oParent, expanded);
};

YAHOO.extend(BlueprintIT.widget.IconNode, YAHOO.widget.HTMLNode);

BlueprintIT.widget.IconNode.prototype.labelElId = null;
BlueprintIT.widget.IconNode.prototype.getLabelEl = function() {
	return document.getElementById(this.labelElId);
}

BlueprintIT.widget.IconNode.prototype.init = function(oData, oParent, expanded) {
	var html;
	if (oData.href) {
		html = '<a href="' + oData.href + '"';
		if (oData.target)
			html += ' target="' + oData.target + '"';
		html += '>' + oData.label + '</a>';
	}
	else
		html = oData.label;
	oData.html = html;

	BlueprintIT.widget.IconNode.superclass.init.call(this, oData, oParent, expanded);

  this.labelElId = "ygtvlabelel" + this.index;
	oData.html = '<div id="'+this.labelElId+'">' + html + '</div>';
	this.initContent(oData, true);
}

BlueprintIT.widget.IconNode.prototype.getContentStyle = function() {
	var style = "iconnode_content";
	if (this.hasChildren(false))
	{
		var state = "clsd";
		if (this.expanded)
			state = "open";
		
		style+= " iconnode_branch_"+state;
		if (this.data.type)
			style+= " iconnode_branch_"+this.data.type+" iconnode_branch_"+this.data.type+"_"+state;
	}
	else
	{
		style+= " iconnode_leaf";
		if (this.data.type)
			style+= " iconnode_leaf_"+this.data.type;
	}
	
	return style;
}

BlueprintIT.widget.IconNode.prototype.toggle = function() {
	BlueprintIT.widget.IconNode.superclass.toggle.call(this);

	this.getContentEl().className = this.getContentStyle();
}

BlueprintIT.widget.IconNode.prototype.getNodeHtml = function() {
	this.contentStyle = this.getContentStyle();
	
	return BlueprintIT.widget.IconNode.superclass.getNodeHtml.call(this);
}

BlueprintIT.widget.StyledTextNode = function(oData, oParent, expanded) {
	if (oParent) {
		this.init(oData, oParent, expanded);
		this.setUpLabel(oData);
		this.setUpStyles(oData);
	}
}

BlueprintIT.widget.StyledTextNode.prototype = new YAHOO.widget.TextNode();

BlueprintIT.widget.StyledTextNode.prototype.iconClass = null;
BlueprintIT.widget.StyledTextNode.prototype.labelClass = "";

BlueprintIT.widget.StyledTextNode.prototype.setUpStyles = function(oData) {
	if (oData.labelClass) {
		this.labelStyle = ' ' + oData.labelClass;
	}

	if (oData.iconClass) {
		this.iconClass = oData.iconClass;
	}
}

BlueprintIT.widget.StyledTextNode.prototype.getStyle = function() {
	var style = YAHOO.widget.TextNode.prototype.getStyle.call(this);
	if (this.iconClass) {
    var loc = (this.nextSibling) ? "t" : "l";
    var type = "n";
    if (this.hasChildren(true) || this.isDynamic()) {
      type = (this.expanded) ? "m" : "p";
    }
    style += ' ' + this.iconClass + ' ' + this.iconClass + loc + type;
  }
	return style;
}

BlueprintIT.widget.StyledTextNode.prototype.getHoverStyle = function() {
	var style = YAHOO.widget.TextNode.prototype.getHoverStyle.call(this);
	style += this.iconClass;
	return style;
}

BlueprintIT.widget.DraggableTreeNodeProxy = function(node, sGroup) {
	if (node) {
		this.node = node;
		var el = BlueprintIT.widget.DraggableTreeView.getNodeLabel(node);
		while (el && el.tagName != 'TD')
			el = el.parentNode;
		if (el && el.id)
			el = el.id;
		else
			el = BlueprintIT.widget.DraggableTreeView.getNodeLabelId(node);
		this.init(el, sGroup);
		delete this.invalidHandleTypes["A"];
		this.initFrame();
	}
}

YAHOO.extend(BlueprintIT.widget.DraggableTreeNodeProxy, YAHOO.util.DDProxy);

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.node = null;

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.indicatorDiv = null;

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.showFrame = function(iPageX, iPageY) {
	var el = this.getEl();
	var dragEl = this.getDragEl();
	dragEl.className = "dragframe "+el.className;
	dragEl.innerHTML = YAHOO.util.Dom.allTextContent(el);
	BlueprintIT.widget.DraggableTreeNodeProxy.superclass.showFrame.call(this, iPageX, iPageY);
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.getInsertPositionFromNode = function(node, e) {
	var ypos = YAHOO.util.Event.getPageY(e);
	var mode = this.node.tree.getDragMode(e);
	var pos = 0;
	var subnode = node.children[0];
	while (subnode) {
		var elregion = YAHOO.util.Dom.getRegion(subnode.getEl());
		if (ypos<elregion.top) {
			if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
				return { parent: node, position: pos };
			else
				return null;
		}
		if (subnode != this.node) {
			var chregion = YAHOO.util.Dom.getRegion(subnode.getChildrenEl());
			if (ypos<chregion.top) {
				if (ypos>((chregion.top+elregion.top)/2)) {
					if (this.node.tree.dragDropManager.canHold(subnode, this.node, mode))
						return { parent: subnode, position: 0 };
					else if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
						return { parent: node, position: pos+1 };
					else
						return null;
				}
				else if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
					return { parent: node, position: pos };
				else
					return null;
			}
			else if (ypos<chregion.bottom) {
				return this.getInsertPositionFromNode(subnode, e);
			}
		}
		if (ypos<elregion.bottom) {
			if (ypos>((elregion.top+elregion.bottom)/2)) {
				if ((subnode != this.node) && this.node.tree.dragDropManager.canHold(subnode, this.node, mode))
					return { parent: subnode, position: 0 };
				else if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
					return { parent: node, position: pos+1 };
				else
					return null;
			}
			else if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
				return { parent: node, position: pos };
			else
				return null;
		}
		pos++;
		subnode = subnode.nextSibling;
	}
	if (this.node.tree.dragDropManager.canHold(node, this.node, mode))
		return { parent: node, position: pos };
	else
		return null;
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.getInsertPosition = function(e) {
	var point = this.getInsertPositionFromNode(this.node.tree.getRoot(), e);
	if (point && this.node.tree.getDragMode(e) == BlueprintIT.widget.DraggableTreeView.DRAG_MOVE) {
		if (point.parent.children[point.position] == this.node)
			return null;
		if ((point.position>0) && (point.parent.children[point.position-1] == this.node))
			return null;
	}
	return point;
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.startDrag = function(x, y) {
	this.node.tree.dragDropManager.onDragStart();
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.onDragDrop = function(e, id) {
	if (id != this.node.tree.getRoot().getChildrenElId())
		return;
		
	this.node.tree.indicatorDiv.style.visibility = "hidden";

	var point = this.getInsertPosition(e);

	if (point)
		this.node.tree.dragDropManager.onDragDrop(this.node, point.parent, point.position, this.node.tree.getDragMode(e));
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.onDragOver = function(e, id) {
	if (id != this.node.tree.getRoot().getChildrenElId())
		return;
		
	var point = this.getInsertPosition(e);
	
	if (point) {
		var x;
		var y;
		var width;
	
		var node;
		var region;
		
		if (point.parent.children.length == 0) {
			node = point.parent;
			region = YAHOO.util.Dom.getRegion(node.getEl());
			y = region.bottom;
		}
		else {
			if (point.position < point.parent.children.length) {
				node = point.parent.children[point.position];
				region = YAHOO.util.Dom.getRegion(node.getEl());
				y = region.top;
			}
			else if (point.parent.children.length>0) {
				node = point.parent.children[point.parent.children.length-1];
				region = YAHOO.util.Dom.getRegion(node.getEl());
				y = region.bottom;
			}
		}
		var label = BlueprintIT.widget.DraggableTreeView.getNodeLabel(point.parent);
		region = YAHOO.util.Dom.getRegion(label);
		x = region.left;

		region = YAHOO.util.Dom.getRegion(BlueprintIT.widget.DraggableTreeView.getNodeLabel(this.node));
		width = region.right-region.left;
		
		var s = this.node.tree.indicatorDiv.style;
		s.width = width + "px";
		s.visibility = "";
		YAHOO.util.Dom.setXY(this.node.tree.indicatorDiv, [x, y]);
	}
	else
		this.node.tree.indicatorDiv.style.visibility = "hidden";
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.onDragOut = function(e, id) {
	this.node.tree.indicatorDiv.style.visibility = "hidden";
}

BlueprintIT.widget.DraggableTreeNodeProxy.prototype.endDrag = function(e) {
	this.node.tree.indicatorDiv.style.visibility = "hidden";
	this.node.tree.dragDropManager.onDragEnd();
}

BlueprintIT.widget.DraggableTreeView = function(id, dd) {
	if (id) {
		this.init(id);
		this.dragDropManager = dd;
		this.createIndicator();
	}
}

YAHOO.extend(BlueprintIT.widget.DraggableTreeView, YAHOO.widget.TreeView);

BlueprintIT.widget.DraggableTreeView.DRAG_MOVE = 0;
BlueprintIT.widget.DraggableTreeView.DRAG_COPY = 1;

BlueprintIT.widget.DraggableTreeView.prototype.dragType = BlueprintIT.widget.DraggableTreeView.DRAG_MOVE;

BlueprintIT.widget.DraggableTreeView.prototype.getDragMode = function(e) {
	return this.dragType;
}

BlueprintIT.widget.DraggableTreeView.prototype.setDefaultDragMode = function(mode) {
	this.dragType = mode;
}

BlueprintIT.widget.DraggableTreeView.getNodeLabelId = function(node) {
	if (node.labelElId)
		return node.labelElId;
	if (node.contentElId)
		return node.contentElId;
	if (node.getElId)
		return node.getElId();
	return null;
}

BlueprintIT.widget.DraggableTreeView.getNodeLabel = function(node) {
	if (node.getLabelEl)
		return node.getLabelEl();
	if (node.getContentEl)
		return node.getContentEl();
	if (node.getEl)
		return node.getEl();
	var id = BlueprintIT.widget.DraggableTreeView.getNodeLabelId(node);
	if (id)
		return document.getElementById(id);
	return null;
}

BlueprintIT.widget.DraggableTreeView.prototype.dragDropManager = null;

BlueprintIT.widget.DraggableTreeView.prototype.setupDD = function(node) {
	if (node != this.getRoot() && this.dragDropManager.canDrag(node))
		new BlueprintIT.widget.DraggableTreeNodeProxy(node);
	
	var pos = 0;
	for (pos = 0; pos<node.children.length; pos++)
		this.setupDD(node.children[pos]);
}

BlueprintIT.widget.DraggableTreeView.prototype.createIndicator = function() {
	if (!this.indicatorDiv) {
    this.indicatorDiv = document.createElement("div");
    var s = this.indicatorDiv.style;
    s.position = "absolute";
    s.visibility = "hidden";
    s.border = "1px solid black";
		s.height = "0px";
		s.lineHeight = "0px";
    s.zIndex = 999;

    document.body.appendChild(this.indicatorDiv);
	}
};

BlueprintIT.widget.DraggableTreeView.prototype.draw = function() {
	BlueprintIT.widget.DraggableTreeView.superclass.draw.call(this);
	if (this.dragDropManager) {
		this.setupDD(this.getRoot());
		new YAHOO.util.DDTarget(this.getRoot().getChildrenElId());
	}
}

BlueprintIT.widget.TreeViewLoader = function() {
}

BlueprintIT.widget.TreeViewLoader.prototype = {
}

BlueprintIT.widget.TreeViewLoader.prototype.loadNode = function(treenode, item) {
	var nodes = [];
	var node = item.firstChild;
	while (node) {
		if ((node.nodeType == 1) && (node.tagName.toLowerCase() != "ul") && (node.tagName.toLowerCase() != "ol")) {
			nodes.push(node);
		}
		if (node.nodeType == 3) {
			var text = node.nodeValue;
			if (text.search(/^\s*$/)==0) {
				text=" ";
				if (nodes.length==0) {
					text=null;
				}
			}
			if (text) {
				if ((nodes.length>0) && (typeof nodes[nodes.length-1] == "string")) {
					nodes[nodes.length-1] += text;
				}
				else {
					nodes.push(text);
				}
			}
		}
		node = node.nextSibling;
	}
	var newnode;
	if (nodes.length==1) {
		if (typeof nodes[0] == "string") {
			//alert("Creating text node");
			newnode = new YAHOO.widget.TextNode({ label: nodes[0] }, treenode, true);
		}
		else if (nodes[0].tagName.toLowerCase() == "a") {
			//alert("Creating link node");
			newnode = new YAHOO.widget.TextNode({ href: nodes[0].href, label: nodes[0].innerHTML }, treenode, true);
		}
		else {
			//alert("Creating html node");
			newnode = new YAHOO.widget.HTMLNode({ html: nodes[0].outerHTML }, treenode, true, true);
		}
	}
	else {
		var html = "";
		var i=0;
		for (i=0; i<nodes.length; i++) {
			if (typeof nodes[i] == "string") {
				html += nodes[i];
			}
			else {
				html += nodes[i].outerHTML;
			}
		}
		//alert("Creating html node");
		newnode = new YAHOO.widget.HTMLNode({ html: html }, treenode, true, true);
	}

	var node = item.firstChild;
	while (node) {
		if ((node.nodeType == 1) && ((node.tagName.toLowerCase() == "ul") || (node.tagName.toLowerCase() == "ol"))) {
			this.loadNodeContents(newnode, node);
		}
		node = node.nextSibling;
	}

	newnode.expanded = newnode.hasChildren();
}

BlueprintIT.widget.TreeViewLoader.prototype.loadNodeContents = function(treenode, list) {
	var node = list.firstChild;
	while (node) {
		if ((node.nodeType == 1) && (node.tagName.toLowerCase() == "li")) {
			this.loadNode(treenode, node);
		}
		node = node.nextSibling;
	}
}

BlueprintIT.widget.TreeViewLoader.prototype.loadFromList = function(treeid, listid) {
	var tree = new BlueprintIT.widget.TreeView(treeid);
	var list = document.getElementById(listid);
	if (list && (list.tagName.toLowerCase()=="ul" || list.tagName.toLowerCase()=="ol")) {
		this.loadNodeContents(tree.getRoot(), list);
	}
	return tree;
}
