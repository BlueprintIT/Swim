if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function(item) {
		var pos = 0;
		while (pos<this.length) {
			if (this[pos] == item)
				return pos;
			pos++;
		}
		return -1;
	}
}

YAHOO.widget.Node.prototype.appendChild = function(node) {
	var check = this;
	while (check) {
		if (check == node) {
			throw "Cannot append a node to one of it's descendants.";
		}
		check = check.parent;
	}
	
	if (node.parent && node.parent.children.indexOf(node)>=0)
		node.parent.removeChild(node);
	
	if (this.hasChildren()) {
    var sib = this.children[this.children.length - 1];
    sib.nextSibling = node;
    node.previousSibling = sib;
	}
	
	this.tree.regNode(node);
  node.parent = this;
  node.depth = this.depth + 1;
  node.multiExpand = this.multiExpand;
	this.children[this.children.length] = node;
	this.childrenRendered = false;
	
	return node;
}

YAHOO.widget.Node.prototype.insertBefore = function(node, ref) {
	if (ref == null)
		return this.appendChild(node);

	var check = this;
	while (check) {
		if (check == node) {
			throw "Cannot append a node to one of it's descendants.";
		}
		check = check.parent;
	}

	if (node.parent.children.indexOf(node)>=0)
		node.parent.removeChild(node);

	var pos = this.children.indexOf(ref);
	if (pos<0)
		throw "Not a child of this node";

	node.nextSibling = ref;
	node.previousSibling = ref.previousSibling;
	ref.previousSibling = node;
	
	this.tree.regNode(node);
  node.parent = this;
  node.depth = this.depth + 1;
  node.multiExpand = this.multiExpand;
	this.children.splice(pos, 0, node);
	this.childrenRendered = false;
	
	return node;
}

YAHOO.widget.Node.prototype.removeChild = function(node) {
	var pos = this.children.indexOf(node);
	if (pos<0)
		throw "Not a child of this node";
		
	if (node.nextSibling)
		node.nextSibling.previousSibling = node.previousSibling;
	if (node.previousSibling)
		node.previousSibling.nextSibling = node.nextSibling;
	node.nextSibling = null;
	node.previousSibling = null;
	
	this.children.splice(pos, 1);
	this.childrenRendered = false;
	return node;
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
