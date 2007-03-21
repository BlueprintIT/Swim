/*
 * Blueprint Menu Manager
 *
 * Copyright Blueprint IT Ltd.
 *
 */

BlueprintIT.util.Anim = function(el, attributes, duration,  method) {
  BlueprintIT.util.Anim.superclass.constructor.call(this, el, attributes, duration, method);
};

YAHOO.extend(BlueprintIT.util.Anim, YAHOO.util.Anim);

BlueprintIT.util.Anim.prototype.clipMatch = /rect\(\s*([^\s,]*)\s*,?\s*([^\s,]*)\s*,?\s*([^\s,]*)\s*,?\s*([^\s,]*)\s*\)/;

BlueprintIT.util.Anim.prototype.toString = function()
{
  var el = this.getEl();
  var id = el.id || el.tagName;
  return ("ClipAnim " + id);
};

BlueprintIT.util.Anim.prototype.getClipping = function()
{
	var clip = { top: null, right: null, bottom: null, left: null };
	var el = this.getEl();
	var val = YAHOO.util.Dom.getStyle(el, "clip");
	var results = this.clipMatch.exec(val);
	if (results)
	{
		clip.top = results[1];
		clip.right = results[2];
		clip.bottom = results[3];
		clip.left = results[4];
	}
	else
	{
		clip.top = "auto";
		clip.right = "auto";
		clip.bottom = "auto";
		clip.left = "auto";
	}
	return clip;
};

BlueprintIT.util.Anim.prototype.setClipping = function(clip)
{
	var el = this.getEl();
	var val = "rect(";
	val += clip.top+", ";
	val += clip.right+", ";
	val += clip.bottom+", ";
	val += clip.left+")";
	YAHOO.util.Dom.setStyle(el, "clip", val);
};

BlueprintIT.util.Anim.prototype.getAttribute = function(attr)
{
	if (attr.substr(0, 4) == "clip")
	{
    var el = this.getEl();
    var clip = this.getClipping();
    var side = attr.substr(4).toLowerCase();
    if (clip[side] == "auto")
    {
			var region = YAHOO.util.Dom.getRegion(el);
			switch (side)
			{
				case "top":
					return 0;
				case "right":
					return region.right-region.left;
				case "bottom":
					return region.bottom-region.top;
				case "left":
					return 0;
			}
    }
    else
    	return parseInt(clip[side]);
  }
  else
  {
  	return BlueprintIT.util.Anim.superclass.getAttribute.call(this, attr);
  }
};

BlueprintIT.util.Anim.prototype.setAttribute = function(attr, val, unit)
{
 	if (attr.substr(0, 4) == "clip")
 	{
 		var side = attr.substr(4).toLowerCase();
 		if (unit == "%")
 		{
			var region = YAHOO.util.Dom.getRegion(this.element);
			if ((side == "bottom") || (side == "top"))
			{
				var height = region.bottom-region.top;
				val = 100*val/height;
			}
			else
			{
				var width = region.right-region.left;
				val = 100*val/width;
			}
			unit = "px";
		}
    var clip = this.getClipping();
    clip[side] = val+unit;
    this.setClipping(clip);
  }
  else
  {
  	return BlueprintIT.util.Anim.superclass.setAttribute.call(this, attr, val, unit);
  }
};

BlueprintIT.menus.HORIZONTAL = 1;
BlueprintIT.menus.VERTICAL = 2;

BlueprintIT.menus.CLOSED = 0;
BlueprintIT.menus.OPENWAIT = 1;
BlueprintIT.menus.OPENING = 2;
BlueprintIT.menus.OPEN = 3;
BlueprintIT.menus.CLOSEWAIT = 4;
BlueprintIT.menus.CLOSING = 5;

BlueprintIT.menus.MenuManager = function()
{
	YAHOO.util.Event.addListener(document, 'keydown', this.keyPressEvent, this, true);
	var ua = navigator.userAgent.toLowerCase();
  if (ua.indexOf('opera') != -1) // Opera (check first in case of spoof)
	 this.browser = 'opera';
  else if (ua.indexOf('msie 7') != -1) // IE7
	 this.browser = 'ie7';
  else if (ua.indexOf('msie') != -1) // IE
	 this.browser = 'ie';
  else if (ua.indexOf('safari') != -1) // Safari (check before Gecko because it includes "like Gecko")
	 this.browser = 'safari';
  else if (ua.indexOf('gecko') != -1) // Gecko
	 this.browser = 'gecko';
  else
	 this.browser = 'unknown';
}

BlueprintIT.menus.MenuManager.prototype = {
	
	popupDelay: 200,
	hideDelay: 200,
	
	animator: "instant",
	
	browser: 'unknown',
	
	textarea: null,
	
	selected: null,
	
	log: function(text)
	{
		//console.log(text);
		return;
		if (!this.textarea)
			this.textarea=document.getElementById('log');
		if (this.textarea)
			this.textarea.value=text+"\n"+this.textarea.value;
	},
	
	findMenuItemFromMenu: function(menu, event)
	{
		var pageX = YAHOO.util.Event.getPageX(event);
		var pageY = YAHOO.util.Event.getPageY(event);
		this.log('Seeking menu item for '+pageX+' '+pageY);
		
		var i;
		for (i = 0; i<menu.menuItems.length; i++)
		{
			var region = YAHOO.util.Dom.getRegion(menu.menuItems[i].element);
			this.log('Checking '+region.left+' '+region.right+' '+region.top+' '+region.bottom);
			if ((pageX<region.left)||(pageX>region.right))
				continue;
			if ((pageY<region.top)||(pageY>region.bottom))
				continue;
			return menu.menuItems[i];
		}
		return menu.parentItem;
	},
	
	findMenuItem: function(element, event)
	{
		if (!element)
			return null;
		
		this.log('Seeking menu item for '+element.tagName+' '+element.className);
		
		try
		{
			while (element)
			{
				if (element.menuitem)
					return element.menuitem;
				/*else if (element.menu && event)
					return this.findMenuItemFromMenu(element.menu, event);*/
				else if (element.menu)
					return element.menu.parentItem;
				element = element.parentNode;
			}
		}
		catch (e)
		{
			this.log(e);
		}
					
		return null;
	},
	
	mouseOut: function(items)
	{
		items.reverse();
		for (var k=0; k<items.length; k++)
			items[k].mouseOut();
	},
	
	mouseOver: function(items)
	{
		for (var k=0; k<items.length; k++)
			items[k].mouseOver();
	},
	
	makeItemList: function(bottom)
	{
		var list = [];
		if (bottom)
		{
			list.push(bottom);
			
			while ((bottom.parentMenu!=null)&&(bottom.parentMenu.parentItem!=null))
			{
				bottom=bottom.parentMenu.parentItem;
				list.push(bottom);
			}
			list.reverse();
		}
		return list;
	},
	
	logChain: function(chain)
	{
		var line = '';
		for (var k in chain)
			line+=chain[k].element.id+' ';

		this.log(line);
	},
	
	changeSelection: function(newitem)
	{
		if (newitem!=this.selected)
		{
			var sources = this.makeItemList(this.selected);
			var dests = this.makeItemList(newitem);

			while (((dests.length>0)&&(sources.length>0))&&(dests[0]==sources[0]))
			{
				dests.shift();
				sources.shift();
			}

			this.mouseOut(sources);
			this.mouseOver(dests);
			
			if (this.selected)
				this.selected.unfocus();
			this.selected=newitem;
			if (this.selected)
				this.selected.focus();
		}
	},
	
	keyPressEvent: function(ev)
	{
		this.log("keyPressEvent "+ev.keyCode);
		if ((this.selected) && (ev.keyCode>=37) && (ev.keyCode<=40))
		{
			if (this.selected.keyPress(ev.keyCode))
				YAHOO.util.Event.preventDefault(ev);
		}
	},
	
	focusEvent: function(ev)
	{
		this.log("focusEvent");
		this.changeSelection(this.findMenuItem(YAHOO.util.Event.getTarget(ev), ev));
	},
	
	mouseEvent: function(ev)
	{
		if (ev.type=='mouseover')
		{
			var dest = this.findMenuItem(YAHOO.util.Event.getTarget(ev), ev);
			this.changeSelection(dest);
		}
		else if (ev.type=='mouseout')
		{
			var dest = this.findMenuItem(YAHOO.util.Event.getRelatedTarget(ev), ev);
			if (!dest)
				this.changeSelection(null);
		}
		else if ((ev.type=='click') && (YAHOO.util.Event.getButton(ev)==0))
		{
			this.log('Got click event - '+YAHOO.util.Event.getButton(ev));
			var dest = this.findMenuItem(YAHOO.util.Event.getTarget(ev));
			if ((dest.focusElement) && (dest.focusElement != YAHOO.util.Event.getTarget(ev)))
			{
				this.log('Event might be a missed click');
				var node = dest.focusElement.parentNode;
				while (node && node != dest.element && node != YAHOO.util.Event.getTarget(ev))
					node = node.parentNode;
				if (node == YAHOO.util.Event.getTarget(ev))
				{
					this.log('Event was between focus and menuitem, passing to focus.');
					YAHOO.util.Event.stopEvent(ev);
					document.location.href = dest.focusElement.href;
				}
			}
		}
	},
	
	loadFrom: function(element,animator,orientation,depth,parentitem,parentmenu)
	{
		if (!depth)
			depth = 0;
		if (!animator)
			animator = this.animator;
		
		if (YAHOO.util.Dom.hasClass(element,'horizmenu'))
			orientation=BlueprintIT.menus.HORIZONTAL;
		else if (YAHOO.util.Dom.hasClass(element,'vertmenu'))
			orientation=BlueprintIT.menus.VERTICAL;
		
		if (YAHOO.util.Dom.hasClass(element,'fadein'))
			animator = "fade";
		else if (YAHOO.util.Dom.hasClass(element,'slidein'))
			animator = "slide";
		else if (YAHOO.util.Dom.hasClass(element,'appearin'))
			animator = "instant";
		
		if (YAHOO.util.Dom.hasClass(element,'menuitem'))
		{
			YAHOO.util.Dom.addClass(element,'level'+depth);
			parentitem = new MenuItem(this,parentmenu,element);
			parentmenu=null;
		}
		else if (YAHOO.util.Dom.hasClass(element,'menupopup')||YAHOO.util.Dom.hasClass(element,'menu'))
		{
			depth++;
			YAHOO.util.Dom.addClass(element,'level'+depth);
			parentmenu = new Menu(this,parentitem,orientation,element,animator);
			parentitem=null;
		}
		else if ((element.tagName=='A')&&(parentitem))
		{
			YAHOO.util.Dom.addClass(element,'level'+depth);
			parentitem.setFocusElement(element);
		}
		var child = element.firstChild;
		while (child)
		{
			if (child.nodeType==1)
				this.loadFrom(child,animator,orientation,depth,parentitem,parentmenu);
			child=child.nextSibling;
		}
	},
	
	loadMenus: function()
	{
		this.log("Loading");
		this.loadFrom(document.documentElement,this.animator);
	}
}

function Menu(manager,item,orientation,element,animator)
{
	this.manager = manager;
	//this.manager.log('Created menu from '+element.tagName);
	this.animtype = animator;

	element.menu = this;
	
	this.menuItems=[];
	if (item)
	{
		this.parentItem=item;
		this.parentItem.submenu=this;
	}
	else
	{
		this.manager.log("Adding listener to "+element.id);
		YAHOO.util.Event.addListener(element,'mouseover',this.manager.mouseEvent,this.manager,true);
		YAHOO.util.Event.addListener(element,'mouseout',this.manager.mouseEvent,this.manager,true);
		YAHOO.util.Event.addListener(element,'click',this.manager.mouseEvent,this.manager,true);
	}
	this.orientation=orientation;
	this.element=element;

	if (manager.browser == 'ie') {
	 	this.iframe = document.createElement("iframe");
	 	this.iframe.className = "menuframe";
	 	this.iframe.frameBorder = "0";
	 	YAHOO.util.Dom.setStyle(this.iframe, "position", "absolute");
	 	YAHOO.util.Dom.setStyle(this.iframe, "display", "none");
	 	YAHOO.util.Dom.setStyle(this.iframe, "zIndex", "0");
	 	YAHOO.util.Dom.setStyle(this.iframe, "opacity", "0");
	 	document.body.insertBefore(this.iframe, document.body.firstChild);
	}
}

/*
   State CLOSED    - normal
                     can move to state 1 on mouse over
   State OPENWAIT  - waiting to appear
                     can move to state 0 on mouse out
                     can move to state 2/3 on timer complete
   State OPENING   - animating in
                     can move to state 5 on mouse out
                     can move to state 3 on animation complete
   State OPEN      - visible
                     can move to state 4 on mouse out
   State CLOSEWAIT - waiting to disappear
                     can move to state 3 on mouse over
                     can move to state 5/0 on timer complete
   State CLOSING   - animating out
                     can move to state 2 on mouse over
                     can move to state 0 on animation complete
*/

Menu.prototype = {
	
	parentItem: null,
	menuItems: null,
	orientation: null,
	element: null,
	timer: null,
	state: BlueprintIT.menus.CLOSED,
	animator: null,
	animtype: null,
	manager: null,
	iframe: null,
	anchor: null,
		
	setPosition: function(x,y)
	{
		YAHOO.util.Dom.setXY(this.element, [x, y]);
	},

	setVisible: function(value)
	{
	  if (value)
	  {
	  	YAHOO.util.Dom.setStyle(this.element, "display", "block");
	  	if (this.iframe)
		  	YAHOO.util.Dom.setStyle(this.iframe, "display", "block");
  	  this.parentItem.setMenuPosition();
  	}
  	else
  	{
	  	YAHOO.util.Dom.setStyle(this.element, "display", "none");
	  	if (this.iframe)
		  	YAHOO.util.Dom.setStyle(this.iframe, "display", "none");

			YAHOO.util.Dom.removeClass(this.parentItem.element, 'opened');
			if (this.parentItem.focusElement)
				YAHOO.util.Dom.removeClass(this.parentItem.focusElement, 'opened');
  	}
	},
	
	createDisplayAnimator: function(initial)
	{
		this.manager.log("createDisplayAnimator "+this.element.id);
		this.state = BlueprintIT.menus.OPENING;
		
		this.animator = new BlueprintIT.util.Anim(this.element, { }, 0.4, YAHOO.util.Easing.easeOut);
		this.setVisible(true);
		YAHOO.util.Dom.setStyle(this.element, "zIndex", 999);

		switch (this.animtype)
		{
			case "fade":
				this.animator.attributes.opacity = { to: 1 };
				if (initial)
					this.animator.setAttribute("opacity", 0, "");
				break;
			case "slide":
				var region = YAHOO.util.Dom.getRegion(this.element);
				var height = region.bottom-region.top;
				var width = region.right-region.left;
				var attr, from, to;
				switch (this.anchor)
				{
					case "top":    attr = "clipBottom"; from = 0; to = height; break;
					case "bottom": attr = "clipTop";    from = height; to = 0; break;
					case "left":   attr = "clipRight";  from = 0; to = width; break;
					case "right":  attr = "clipLeft";   from = width; to = 0; break;
				}
				this.animator.attributes[attr] = { to: to };
				if (initial)
					this.animator.setAttribute(attr, from, "px");
				break;
			default:
				this.animator = null;
				this.onAnimatorComplete(null, null, this);
				return;
		}
		
		this.animator.onComplete.subscribe(this.onAnimatorComplete, this);
		this.animator.animate();
	},
	
	createHideAnimator: function(initial)
	{
		this.manager.log("createHideAnimator "+this.element.id);
		this.state = BlueprintIT.menus.CLOSING;
		YAHOO.util.Dom.setStyle(this.element, "zIndex", null);
		
		this.animator = new BlueprintIT.util.Anim(this.element, { }, 0.4, YAHOO.util.Easing.easeOut);
		
		switch (this.animtype)
		{
			case "fade":
				this.animator.attributes.opacity = { to: 0 };
				break;
			case "slide":
				var region = YAHOO.util.Dom.getRegion(this.element);
				var height = region.bottom-region.top;
				var width = region.right-region.left;
				var attr, from, to;
				switch (this.anchor)
				{
					case "top":    attr = "clipBottom"; from = 0; to = height; break;
					case "bottom": attr = "clipTop";    from = height; to = 0; break;
					case "left":   attr = "clipRight";  from = 0; to = width; break;
					case "right":  attr = "clipLeft";   from = width; to = 0; break;
				}
				this.animator.attributes[attr] = { to: from };
				break;
			default:
				this.animator = null;
				this.onAnimatorComplete(null, null, this);
				return;
		}
		
		this.animator.onComplete.subscribe(this.onAnimatorComplete, this);
		this.animator.animate();
	},
	
	onTimer: function()
	{
		this.manager.log("onTimer "+this.element.id+" "+this.state);
		switch (this.state)
		{
			case BlueprintIT.menus.OPENWAIT:
				this.show();
				break;
			case BlueprintIT.menus.CLOSEWAIT:
				this.hide();
				break;
		}
	},
	
	onAnimatorComplete: function(type, args, menu)
	{
		menu.manager.log("onAnimatorComplete "+menu.element.id+" "+menu.state);
		switch (menu.state)
		{
			case BlueprintIT.menus.OPENING:
				menu.state = BlueprintIT.menus.OPEN;
				if (menu.animtype == "slide")
					YAHOO.util.Dom.setStyle(menu.element, "clip", "rect(auto, auto, auto, auto)");
				break;
			case BlueprintIT.menus.CLOSING:
				menu.state = BlueprintIT.menus.CLOSED;
				menu.setVisible(false);
				YAHOO.util.Dom.removeClass(menu.parentItem.element, 'opened');
				if (menu.parentItem.focusElement)
					YAHOO.util.Dom.removeClass(menu.parentItem.focusElement, 'opened');
				break;
		}
	},
	
	show: function()
	{
		switch (this.state)
		{
			case BlueprintIT.menus.CLOSED:
			case BlueprintIT.menus.OPENWAIT:
				this.createDisplayAnimator(true);
				break;
			case BlueprintIT.menus.CLOSEWAIT:
				BlueprintIT.timing.cancelTimer(this.timer);
				this.state=BlueprintIT.menus.OPEN;
				break;
			case BlueprintIT.menus.CLOSING:
				if (this.animator)
					this.animator.stop();
				this.createDisplayAnimator(false);
				break;
		}
	},
	
	hide: function()
	{
		switch (this.state)
		{
			case BlueprintIT.menus.OPENWAIT:
				BlueprintIT.timing.cancelTimer(this.timer);
				this.state=BlueprintIT.menus.CLOSED;
				break;
			case BlueprintIT.menus.OPENING:
				if (this.animator)
					this.animator.stop();
				this.createHideAnimator(false);
				break;
			case BlueprintIT.menus.OPEN:
			case BlueprintIT.menus.CLOSEWAIT:
				this.createHideAnimator(true);
				break;
		}
	},
	
	startShow: function()
	{
		this.manager.log('startShow '+this.element.id);
		switch (this.state)
		{
			case BlueprintIT.menus.CLOSED:
				this.state=BlueprintIT.menus.OPENWAIT;
				this.timer=BlueprintIT.timing.startTimer(this,this.manager.popupDelay);
				break;
			case BlueprintIT.menus.CLOSEWAIT:
				BlueprintIT.timing.cancelTimer(this.timer);
				this.state=BlueprintIT.menus.OPEN;
				break;
			case BlueprintIT.menus.CLOSING:
				if (this.animator)
					this.animator.stop();
				this.createDisplayAnimator(false);
				break;
		}
	},
	
	startHide: function()
	{
		this.manager.log('startHide '+this.element.id);
		switch (this.state)
		{
			case BlueprintIT.menus.OPENWAIT:
				BlueprintIT.timing.cancelTimer(this.timer);
				YAHOO.util.Dom.removeClass(this.parentItem.element, 'opened');
				if (this.parentItem.focusElement)
					YAHOO.util.Dom.removeClass(this.parentItem.focusElement, 'opened');
				this.state=BlueprintIT.menus.CLOSED;
				break;
			case BlueprintIT.menus.OPENING:
				if (this.animator)
					this.animator.stop();
				this.createHideAnimator(false);
				break;
			case BlueprintIT.menus.OPEN:
				this.state=BlueprintIT.menus.CLOSEWAIT;
				this.timer=BlueprintIT.timing.startTimer(this,this.manager.hideDelay);
				break;
		}
	}
}

function MenuItem(manager,parent,element)
{
	this.manager = manager;
	//this.manager.log('Created menuitem from '+element.tagName);

	element.menuitem = this;

	this.parentMenu=parent;
	this.element=element;

	this.parentMenu.menuItems.push(this);
	this.pos=this.parentMenu.menuItems.length-1;
}

MenuItem.prototype = {
	
	parentMenu: null,
	orientation: null,
	element: null,
	submenu: null,
	pos: null,
	focusElement: null,
	manager: null,
	
	setFocusElement: function(el)
	{
		this.focusElement=el;
		YAHOO.util.Event.addListener(this.focusElement, 'focus', this.focusEvent, this, true);
		YAHOO.util.Event.addListener(this.focusElement, 'blur', this.blurEvent, this, true);
	},
	
	focusEvent: function(ev)
	{
		this.manager.log("focusEvent");
		this.manager.changeSelection(this);
	},
	
	blurEvent: function(ev)
	{
		this.manager.log("blurEvent");
		this.manager.changeSelection(null);
	},
	
	focus: function()
	{
		YAHOO.util.Dom.addClass(this.element, 'focused');
		if (this.focusElement)
			YAHOO.util.Dom.addClass(this.focusElement, 'focused');
	},
	
	unfocus: function()
	{
		YAHOO.util.Dom.removeClass(this.element, 'focused');
		if (this.focusElement)
			YAHOO.util.Dom.removeClass(this.focusElement, 'focused');
	},
	
	keyPress: function(code)
	{
		var newitem = null;
		if (this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
		{
			if (code==39) // Right
			{
				var npos=(this.pos+1)%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[npos];
			}
			else if (code==37) // Left
			{
				var npos=(this.pos+(this.parentMenu.menuItems.length-1))%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[npos];
			}
			else if (code==38) // Up
			{
				if (this.parentMenu.parentItem)
				{
					newitem=this.parentMenu.parentItem;
				}
			}
			else if ((code==40)&&(this.submenu)) // Down
			{
				newitem=this.submenu.menuItems[0];
			}
		}
		else if (this.parentMenu.orientation==BlueprintIT.menus.VERTICAL)
		{
			var parentOrient = null;
			if (this.parentMenu.parentItem)
			{
				parentOrient=this.parentMenu.parentItem.parentMenu.orientation;
			}
			if (code==38) // Up
			{
				if (this.pos==0)
				{
					if (parentOrient == BlueprintIT.menus.HORIZONTAL)
						newitem=this.parentMenu.parentItem;
					else
						newitem=this.parentMenu.menuItems[this.parentMenu.menuItems.length-1];
				}
				else
				{
					newitem=this.parentMenu.menuItems[this.pos-1];
				}
			}
			else if (code==40) // Down
			{
				var newpos=(this.pos+1)%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[newpos];
			}
			else if (code==37) // Left
			{
				if (parentOrient==BlueprintIT.menus.HORIZONTAL)
				{
					var newpos=this.parentMenu.parentItem.pos-1;
					if (newpos<0)
						newpos=this.parentMenu.parentItem.parentMenu.menuItems.length-1;
					newitem=this.parentMenu.parentItem.parentMenu.menuItems[newpos];
					/*if (newitem.submenu)
						newitem = newitem.submenu.menuItems[0];*/
				}
				else
				{
					newitem=this.parentMenu.parentItem;
				}
			}
			else if (code==39) // Right
			{
				if (this.submenu)
				{
					newitem=this.submenu.menuItems[0];
				}
				else
				{
					var parent = this.parentMenu.parentItem;
					while (parent && parent.parentMenu.orientation != BlueprintIT.menus.HORIZONTAL)
						parent = parent.parentMenu.parentItem;
					if (parent)
					{
						var newpos=parent.pos+1;
						newpos=newpos%parent.parentMenu.menuItems.length;
						newitem=parent.parentMenu.menuItems[newpos];
						/*if (newitem.submenu)
							newitem = newitem.submenu.menuItems[0];*/
					}
				}
			}
		}
		if (newitem)
		{
			if (newitem.focusElement)
				newitem.focusElement.focus();
			else
				this.manager.changeSelection(newitem);
			return true;
		}
		return false;
	},
	
	mouseOver: function()
	{
		this.manager.log('mouseOver '+this.element.id);

		YAHOO.util.Dom.addClass(this.element, 'opened');
		if (this.focusElement)
			YAHOO.util.Dom.addClass(this.focusElement, 'opened');
		
		if (this.submenu)
			this.submenu.startShow();
		else if (this.parentMenu.parentItem)
			this.parentMenu.startShow();
	},
	
	mouseOut: function()
	{
		this.manager.log('mouseOut '+this.element.id);
		
		if (this.submenu)
			this.submenu.startHide();
		else
		{
			YAHOO.util.Dom.removeClass(this.element, 'opened');
			if (this.focusElement)
				YAHOO.util.Dom.removeClass(this.focusElement, 'opened');
		}
	},
	
	setMenuPosition: function()
	{
		if (this.submenu)
		{
			var region = YAHOO.util.Dom.getRegion(this.element);
			var subregion = YAHOO.util.Dom.getRegion(this.submenu.element);

			var width = subregion.right-subregion.left;
			var height = subregion.bottom-subregion.top;
		  var bwidth = YAHOO.util.Dom.getClientWidth();
		  var bheight = YAHOO.util.Dom.getClientHeight();

			if (this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
			{
			  var left = region.left
			  var top = region.bottom;
			  this.submenu.anchor = "top";

			  if (((top+height)>bwidth) && (region.top >= height))
			  {
			  	top = region.top-height;
			  	this.submenu.anchor = "bottom";
			  }
			}
			else
			{
			  var left = region.right;
			  var top = region.top;
			  this.submenu.anchor = "left";

			  if (((left+width)>bwidth) && (region.left >= width))
			  {
			    left = region.left-width;
			    this.submenu.anchor = "right";
			  }
			}
			if ((left+width)>bwidth)
				left = bwidth-width;
			if (left < 0)
				left = 0;
			if ((top+height)>bheight)
				top = bheight-height;
			if (top < 0)
				top = 0;
			this.manager.log("setMenuPosition "+left+" "+top);
			YAHOO.util.Dom.setXY(this.submenu.element, [left, top]);
			if (this.submenu.iframe) {
				this.submenu.iframe.style.width = width+"px";
				this.submenu.iframe.style.height = height+"px";
				YAHOO.util.Dom.setXY(this.submenu.iframe, [left, top]);
			}
		}
	}
}

var menuManager = new BlueprintIT.menus.MenuManager();
