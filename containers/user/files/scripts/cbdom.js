// Wraps an element giving functions for position and display control
function ElementWrapper(element)
{
	this.element=element;
}

ElementWrapper.prototype = {
	
	element: null,
	
	addClass: function(cls)
	{
		var newclass = '';
		var classes = this.element.className.split(' ');
		for (var k in classes)
		{
			if (classes[k]!=cls)
				newclass+=classes[k]+' ';
		}
		this.element.className=newclass+cls;
	},
	
	removeClass: function(cls)
	{
		var classes = this.element.className.split(' ');
		if (classes.length>0)
		{
			var newclass = '';
			for (var k in classes)
			{
				if (classes[k]!=cls)
					newclass+=' '+classes[k];
			}
			this.element.className=newclass.substring(1,newclass.length);
		}
	},
	
	hasClass: function(cls)
	{
		var classes = this.element.className.split(' ');
		for (var k in classes)
		{
			if (classes[k]==cls)
				return true;
		}
		return false;
	},
	
	getContainingBlock: function()
	{
		var el = this.element;
		try
		{
			while (el.parentNode)
			{
				el=el.parentNode;
				var wrap = new ElementWrapper(el);
				if ((el.tagName)&&(el.tagName=='HTML'))
				{
					return wrap;
				}
				var pos = wrap.getComputedStyle().position;
				if ((pos=='absolute')||(pos=='relative')||(pos=='fixed'))
					return wrap;
			}
		}
		catch (e) { }
		return null;
	},
	
	getLeft: function()
	{
		if (this.element.offsetLeft===null)
		{
			return parseInt(this.getComputedStyle().left);
		}
		else
		{
			var value=this.element.offsetLeft;
			var parent = this.element.offsetParent;
			while (parent)
			{
				value+=parent.offsetLeft;
				parent=parent.offsetParent;
			}
			return value;
		}
	},
	
	getTop: function()
	{
		if (this.element.offsetTop===null)
		{
			return parseInt(this.getComputedStyle().top);
		}
		else
		{
			var value=this.element.offsetTop;
			var parent = this.element.offsetParent;
			while (parent)
			{
				value+=parent.offsetTop;
				parent=parent.offsetParent;
			}
			return value;
		}
	},
	
	setLeft: function(value)
	{
		var block = this.getContainingBlock();
		if (block)
			value-=block.getLeft();
		this.getAssignedStyle().left=value+'px';
	},
	
	setTop: function(value)
	{
		var block = this.getContainingBlock();
		if (block)
			value-=block.getTop();
		this.getAssignedStyle().top=value+'px';
	},
	
	getWidth: function()
	{
    if (!(this.element.offsetWidth===null))
    {
      return this.element.offsetWidth;
    }
    else if (this.element.clip && (!(this.element.clip.width===null)))
    {
      return this.element.clip.width;
    }
    else if (this.element.style && (!(this.element.style.pixelWidth===null)))
    {
      return this.element.style.pixelWidth;
    }
    else if (!(this.element.clientWidth===null))
    {
      return this.element.clientWidth;
    }
   	else
    {
    	return parseInt(this.getComputedStyle().width);
    }
	},
	
	getHeight: function()
	{
    if (!(this.element.offsetHeight===null))
    {
      return this.element.offsetHeight;
    }
    else if (this.element.clip && (!(this.element.clip.height===null)))
    {
      return this.element.clip.height;
    }
    else if (this.element.style && (!(this.element.style.pixelHeight===null)))
    {
      return this.element.style.pixelHeight;
    }
    else if (!(this.element.clientHeight===null))
    {
      return this.element.clientHeight;
    }
    else
    {
    	return parseInt(this.getComputedStyle().height);
    }
	},
	
	setWidth: function(value)
	{
		this.getAssignedStyle().width=value+'px';
	},
	
	setHeight: function(value)
	{
		this.getAssignedStyle().height=value+'px';
	},
	
	getComputedStyle: function()
	{
		if (window.getComputedStyle)
		{
			return window.getComputedStyle(this.element,"");
		}
		else if (this.element.currentStyle)
		{
			return this.element.currentStyle;
		}
		else
		{
			return this.getAssignedStyle();
		}
	},
	
	getAssignedStyle: function()
	{
		if (this.element.style)
		{
			return this.element.style;
		}
		else
		{
			return this.element;
		}
	},
	
	getOpacity: function()
	{
		var style = this.getComputedStyle();
		if (!(style.opacity===null))
		{
			return parseFloat(style.opacity);
		}
		else if (!(style.MozOpacity===null))
		{
			return parseFloat(style.MozOpacity);
		}
		else if (!(style.filter===null))
		{
			menuManager.log(style.filter);
			var filter=style.filter;
			var pos = filter.indexOf('alpha(opacity=');
			if (pos>=0)
			{
				return parseFloat(filter.substring(pos+14,filter.length))/100.0;
			}
		}
		else
		{
			return 1;
		}
	},
	
	setOpacity: function(value)
	{
		var style = this.getAssignedStyle();
		style.opacity=value;
		style.MozOpacity=value;
		style.filter='alpha(opacity='+value*100+')';
	},
	
	getDisplay: function()
	{
		return this.getComputedStyle().display;
	},
	
	setDisplay: function(value)
	{
		this.getAssignedStyle().display=value;
	},
	
	getVisibility: function()
	{
		return this.getComputedStyle().visibility=="visible";
	},
	
	setVisibility: function(value)
	{
		var style=this.getAssignedStyle();

		if (value)
		{
			style.visibility="visible";
		}
		else
		{
			style.visibility="hidden";
		}
	},
	
	shift: function(x,y)
	{
		this.setLeft(this.getLeft()+x);
		this.setTop(this.getTop()+y);
	}
}

// Gets a DOM compliant event
function getDOMEvent(event)
{
	if (window.event)
	{
		return new DOMEventWrapper();
	}
	else
	{
		return event;
	}
}

// Wraps the Microsoft window.event into a DOM compliant event. Not all properties are usable.
function DOMEventWrapper()
{
	this.timeStamp = new Date();
	this.event=window.event;
	this.type=window.event.type;
	if (window.event.type=='mouseout')
	{
		this.target=window.event.fromElement;
		this.relatedTarget=window.event.toElement;
	}
	else if (window.event.type=='mouseover')
	{
		this.relatedTarget=window.event.fromElement;
		this.target=window.event.toElement;
	}
	else
	{
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
	
	if (window.event.button & 1)
	{
		this.button=0;
	}
	else if (window.event.button & 2)
	{
		this.button=2;
	}
	else if (window.event.button & 4)
	{
		this.button=1;
	}
	else
	{
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
	preventDefault: function()
	{
		this.event.returnValue=false;
	},
	
	stopPropagation: function()
	{
		this.event.cancelBubble=true;
		this.bubbles=false;
	}
}

function addEvent(evtarget, evType, func, capture)
{
	if (evtarget.addEventListener)
	{
		evtarget.addEventListener(evType, func, capture);
	}
	else if (evtarget.attachEvent)
	{
		evtarget.attachEvent("on"+evType, func);
	}
	else
	{
		evtarget['on'+evType]=func;
	}
}
