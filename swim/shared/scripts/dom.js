YAHOO.util.Event.getButton = function(ev) {
	if (window.event) {
		if (window.event.button & 1)
			return 0;
		else if (window.event.button & 2)
			return 2;
		else if (window.event.button & 4)
			return 1;
		else if (this.type == 'click')
			return 0;
		else
			return null;
	}
	else
		return ev.button;
}

YAHOO.util.Dom.allTextContent = function(el) {
	el = this.get(el);
	var content = "";
	var node = el.firstChild;
	while (node) {
		if (node.nodeType == 3)
			content+=node.nodeValue;
		else if (node.nodeType == 1)
			content+=YAHOO.util.Dom.allTextContent(node);
			
		node = node.nextSibling;
	}
	return content;
}

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

// Gets a DOM compliant event
YAHOO.util.Event.baseGetEvent = YAHOO.util.Event.getEvent;
YAHOO.util.Event.getEvent = function (event) {
	var result = YAHOO.util.Event.baseGetEvent(event);
	if (window.event)
		result.wrapped = new DOMEventWrapper();
	else if (event)
		result.wrapped = event;
	return result;
}

// Wraps the Microsoft window.event into a DOM compliant event. Not all properties are usable.
function DOMEventWrapper() {
	this.timeStamp = new Date();
	this.event=window.event;
	this.type=window.event.type;
	if (window.event.type=='mouseout') {
		this.target=window.event.fromElement;
		this.relatedTarget=window.event.toElement;
	} 
	else if (window.event.type=='mouseover') {
		this.relatedTarget=window.event.fromElement;
		this.target=window.event.toElement;
	} else {
		this.relatedTarget=null;
		this.target=window.event.srcElement;
	}

	this.clientX=window.event.clientX;
	this.clientY=window.event.clientY;
	this.screenX=window.event.screenX;
	this.screenY=window.event.screenY;
	this.ctrlKey=window.event.ctrlKey;
	this.altKey=window.event.altlKey;
	this.shiftKey=window.event.shiftKey;
	this.metaKey=window.event.metaKey;
	
	if (window.event.button & 1) {
		this.button=0;
	}
	else if (window.event.button & 2) {
		this.button=2;
	}
	else if (window.event.button & 4) {
		this.button=1;
	} else if (this.type == 'click') {
		this.button=0;
	}
	else {
		this.button=null;
	}

	this.currentTarget=null;
	this.bubbles=!window.event.cancelBubble;
}

DOMEventWrapper.prototype = {
	
	// Standard event properties
	type: null,
	target: null,
	currentTarget: null,   // Set to null since Microsoft does not provide this
	eventPhase: null,      // Unable to find
	bubbled: null,
	cancelable: true,
	timeStamp: null,

	// Mouse event properties
	screenX: null,
	screenY: null,
	clientX: null,
	clientY: null,
	ctrlKey: null,
	shiftKey: null,
	altKey: null,
	metaKey: null,
	button: null,
	relatedTarget: null,
	
	// Standard event methods
	preventDefault: function() {
		this.event.returnValue=false;
	},
	
	stopPropagation: function() {
		this.event.cancelBubble=true;
		this.bubbles=false;
	}
}
