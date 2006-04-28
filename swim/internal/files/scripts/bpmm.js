/*
 * Blueprint Menu Manager
 *
 * Copyright Blueprint IT Ltd.
 *
 */

BlueprintIT.menus.InstantAnimator = function()
{
}

BlueprintIT.menus.InstantAnimator.prototype = {
	startAnimateIn: function(item)
	{
		item.setVisible(true);
		item.state=3;
	},
	
	animateIn: function(item)
	{
	},
	
	startAnimateOut: function(item)
	{
		item.setVisible(false);
		item.state=0;
	},
	
	animateOut: function(item)
	{
	}
}

BlueprintIT.menus.SlideAnimator = function(manager)
{
	this.manager = manager;
}

BlueprintIT.menus.SlideAnimator.prototype = {
	manager: null,
	step: 5,
	delay: 10,

	startAnimateIn: function(item)
	{
		item.clippos=0;
		YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, '+item.clippos+'px, auto)');
		item.setVisible(true);
		item.state=2;
		item.timer=this.manager.startTimer(item,this.delay);
	},
	
	animateIn: function(item)
	{
		item.clippos+=this.step;

		var region = YAHOO.util.Dom.getRegion(item.element);
		var height = region.top-region.bottom;
		if (item.clippos>=height)
		{
			item.clippos=height;
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, auto, auto)');
			item.state=3;
		}
		else
		{
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, '+item.clippos+'px, auto)');
			this.manager.startTimer(item,this.delay);
		}	
	},
	
	startAnimateOut: function(item)
	{
		var region = YAHOO.util.Dom.getRegion(item.element);
		item.clippos=region.top-region.bottom;
		YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, auto, auto)');
		item.state=5;
		item.timer=this.manager.startTimer(item,this.delay);
	},
	
	animateOut: function(item)
	{
		item.clippos-=this.step;
		
		if (item.clippos<=0)
		{
			item.clippos=0;
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, 0px, auto)');
			item.setVisible(false);
			item.state=0;
		}
		else
		{
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, '+item.clippos+'px, auto)');
			this.manager.startTimer(item,this.delay);
		}	
	}		
}

BlueprintIT.menus.FadeAnimator = function(manager)
{
	this.manager = manager;
}

BlueprintIT.menus.FadeAnimator.prototype = {
	manager: null,
	step: 0.05,
	delay: 10,

	startAnimateIn: function(item)
	{
		YAHOO.util.Dom.setStyle(item.element, 'opacity', 0);
		item.setVisible(true);
		item.state=2;
		item.opacpos = 0;
		item.timer=this.manager.startTimer(item,this.delay);
	},
	
	animateIn: function(item)
	{
		var next = item.opacpos;
						
		next+=this.step;

		if (next>=1)
		{
			YAHOO.util.Dom.setStyle(item.element, 'opacity', 1);
			item.opacpos = 1;
			item.state=3;
		}
		else
		{
			YAHOO.util.Dom.setStyle(item.element, 'opacity', next);
			item.opacpos = next;
			this.manager.startTimer(item,this.delay);
		}
	},
	
	startAnimateOut: function(item)
	{
		YAHOO.util.Dom.setStyle(item.element, 'opacity', 1);
		item.state=5;
		item.opacpos = 1;
		item.timer=this.manager.startTimer(item,this.delay);
	},
	
	animateOut: function(item)
	{
		var next = item.opacpos;
			
		next-=this.step;

		if (next<=0)
		{
			YAHOO.util.Dom.setStyle(item.element, 'opacity', 0);
			item.opacpos = 0;
			item.setVisible(false);
			item.state=0;
		}
		else
		{
			YAHOO.util.Dom.setStyle(item.element, 'opacity', next);
			item.opacpos = next;
			this.manager.startTimer(item,this.delay);
		}
	}		
}

BlueprintIT.menus.HORIZONTAL = 0;
BlueprintIT.menus.VERTICAL = 1;

BlueprintIT.menus.MenuManager = function()
{
	this.menuitems = [];

	this.textarea=document.getElementById('log');
	this.animator = new BlueprintIT.menus.InstantAnimator(this);
	YAHOO.util.Event.addListener(document,'focus',this.focusEvent,this,true);
	YAHOO.util.Event.addListener(document,'keypress',this.keyPressEvent,this,true);
}

BlueprintIT.menus.MenuManager.prototype = {
	
	popupDelay: 200,
	hideDelay: 200,
	animator: null,
	
	textarea: null,
	
	itemcount: 0,
	
	menuitems: null,
	
	selected: null,
	
	log: function(text)
	{
		if (this.textarea)
			this.textarea.value+=text+"\n";
	},
	
	startTimer: function(item,timeout)
	{
		function timerCallback() {
			item.onTimer();
		}
		return window.setTimeout(timerCallback, timeout);
	},
	
	cancelTimer: function(id)
	{
		window.clearTimeout(id);
	},
	
	findMenuItem: function(element)
	{
		if (!element)
			return null;
		
		try
		{
			if (element.id && this.menuitems[element.id])
				return this.menuitems[element.id];
			
			if (element.parentNode)
				return this.findMenuItem(element.parentNode);
		}
		catch (e) { }
					
		return null;
	},
	
	mouseOut: function(items)
	{
		items.reverse();
		for (var k in items)
		{
			items[k].mouseOut();
		}
	},
	
	mouseOver: function(items)
	{
		for (var k in items)
		{
			items[k].mouseOver();
		}
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
		{
			line+=chain[k].element.id+' ';
		}
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
			{
				this.selected.unfocusCurrent();
			}
			this.selected=newitem;
			if (this.selected)
			{
				this.selected.focusCurrent();
			}
		}
	},
	
	keyPressEvent: function(ev)
	{
		if (ev.type=='keypress')
		{
			if (this.selected)
			{
				if ((ev.keyCode>=37)&&(ev.keyCode<=40))
				{
					if (this.selected.keyPress(ev.keyCode))
					{
						ev.preventDefault();
					}
				}
			}
		}
	},
	
	focusEvent: function(ev)
	{
		if (ev.type=='focus')
		{
			this.changeSelection(this.findMenuItem(ev.target));
		}
	},
	
	mouseEvent: function(ev)
	{
		if (ev.type=='mouseover')
		{
			var dest = this.findMenuItem(ev.target);
			this.changeSelection(dest);
		}
		else if (ev.type=='mouseout')
		{
			var dest = this.findMenuItem(ev.relatedTarget);
			if (!dest)
				this.changeSelection(null);
		}
	},
	
	loadFrom: function(orientation,element,parentitem,parentmenu)
	{
		if (YAHOO.util.Dom.hasClass(element,'horizmenu'))
		{
			orientation=BlueprintIT.menus.HORIZONTAL;
		}
		else if (YAHOO.util.Dom.hasClass(element,'vertmenu'))
		{
			orientation=BlueprintIT.menus.VERTICAL;
		}
		if (YAHOO.util.Dom.hasClass(element,'menuitem'))
		{
			parentitem = new MenuItem(this,parentmenu,element);
			parentmenu=null;
		}
		else if (YAHOO.util.Dom.hasClass(element,'menupopup')||YAHOO.util.Dom.hasClass(element,'menu'))
		{
			parentmenu = new Menu(this,parentitem,orientation,element);
			parentitem=null;
		}
		else if ((element.tagName=='A')&&(parentitem))
		{
			parentitem.setFocusElement(element);
		}
		var child = element.firstChild;
		while (child)
		{
			if (child.nodeType==1)
				this.loadFrom(orientation,child,parentitem,parentmenu);
			child=child.nextSibling;
		}
	},
	
	loadMenus: function()
	{
		this.log("Loading");
		this.loadFrom(BlueprintIT.menus.HORIZONTAL,document.documentElement);
	}
}

function Menu(manager,item,orientation,element)
{
	this.manager = manager;
	this.manager.log('Created menu from '+element.tagName);

	this.menuItems=[];
	if (!element.id)
	{
		element.id='bpmm_'+this.manager.itemcount;
	}
	if (item)
	{
		this.manager.menuitems[element.id]=item;
		this.manager.itemcount++;
	
		this.parentItem=item;
		this.parentItem.submenu=this;
	}
	else
	{
		this.manager.log("Adding listener to "+element.id);
		YAHOO.util.Event.addListener(element,'mouseover',this.manager.mouseEvent,this.manager,true);
		YAHOO.util.Event.addListener(element,'mouseout',this.manager.mouseEvent,this.manager,true);
	}
	this.orientation=orientation;
	this.element=element;
}

Menu.prototype = {
	
	parentItem: null,
	menuItems: null,
	orientation: null,
	element: null,
	timer: null,
	state: 0,
	animator: null,
	manager: null,
		
	setPosition: function(x,y)
	{
		YAHOO.util.Dom.setXY(this.element, [x, y]);
	},

	setVisible: function(value)
	{
	  if (value)
	  {
	  	YAHOO.util.Dom.setStyle(this.element, "display", "block");
  	  this.parentItem.setMenuPosition();
  	}
  	else
  	{
	  	YAHOO.util.Dom.setStyle(this.element, "display", "none");
  	}
	},
	
	onTimer: function()
	{
		this.manager.log("onTimer "+this.element.id+" "+this.state);
		switch (this.state)
		{
			case 1:
				this.show();
				break;
			case 2:
				this.animator.animateIn(this);
				break;
			case 4:
				this.hide();
				break;
			case 5:
				this.animator.animateOut(this);
				break;
		}
	},
	
	show: function()
	{
		switch (this.state)
		{
			case 0:
			case 1:
				//this.parentItem.setMenuPosition();
				this.animator=this.manager.animator;
				this.animator.startAnimateIn(this);
				break;
			case 4:
				this.cancelTimer(this.timer);
				this.state=3;
				break;
			case 5:
				this.state=2;
				break;
		}
	},
	
	hide: function()
	{
		switch (this.state)
		{
			case 1:
				this.manager.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
			case 4:
				this.animator=this.manager.animator;
				this.animator.startAnimateOut(this);
				break;
		}
	},
	
	startShow: function()
	{
		//this.log('startShow '+this.element.id);
		switch (this.state)
		{
			case 0:
				this.state=1;
				this.timer=this.manager.startTimer(this,this.manager.popupDelay);
				break;
			case 4:
				this.manager.cancelTimer(this.timer);
				this.state=3;
				break;
			case 5:
				this.state=2;
				break;
		}
	},
	
	startHide: function()
	{
		//this.log('startHide '+this.element.id);
		switch (this.state)
		{
			case 1:
				this.manager.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
				this.state=4;
				this.timer=this.manager.startTimer(this,this.manager.hideDelay);
				break;
		}
	}
}

function MenuItem(manager,parent,element)
{
	this.manager = manager;
	this.manager.log('Created menuitem from '+element.tagName);

	if (!element.id)
	{
		element.id='bpmm_'+this.manager.itemcount;
	}
	this.manager.menuitems[element.id]=this;
	this.manager.itemcount++;

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
	},
	
	focusCurrent: function()
	{
		if (this.parentMenu.parentItem)
			YAHOO.util.Dom.addClass(this.element, 'currentfocus');
		if (this.focusElement)
			YAHOO.util.Dom.addClass(this.focusElement, 'itemfocus');
	},
	
	unfocusCurrent: function()
	{
		if (this.parentMenu.parentItem)
			YAHOO.util.Dom.removeClass(this.element, 'currentfocus');
		if (this.focusElement)
			YAHOO.util.Dom.removeClass(this.focusElement, 'itemfocus');
	},
	
	focus: function()
	{
		YAHOO.util.Dom.addClass(this.element, 'menufocus');
	},
	
	unfocus: function()
	{
		YAHOO.util.Dom.removeClass(this.element, 'menufocus');
	},
	
	keyPress: function(code)
	{
		var newitem = null;
		if (this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
		{
			if (code==39)
			{
				var npos=(this.pos+1)%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[npos];
			}
			else if (code==37)
			{
				var npos=(this.pos+(this.parentMenu.menuItems.length-1))%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[npos];
			}
			else if (code==38)
			{
				if (this.parentMenu.parentItem)
				{
					newitem=this.parentMenu.parentItem;
				}
			}
			else if ((code==40)&&(this.submenu))
			{
				newitem=this.submenu.menuItems[0];
			}
		}
		else if (this.parentMenu.orientation==BlueprintIT.menus.VERTICAL)
		{
			var parentOrient = null;
			if (this.parentMenu.parentItem)
			{
				parientOrient=this.parentMenu.parentItem.parentMenu.orientation;
			}
			if (code==38)
			{
				if (this.pos==0)
				{
					if (parentOrient==BlueprintIT.menus.HORIZONTAL)
						newitem=this.parentMenu.parentItem;
					else
						newitem=this.parentMenu.menuItems[this.parentMenu.menuItems.length-1];
				}
				else
				{
					newitem=this.parentMenu.menuItems[this.pos-1];
				}
			}
			else if (code==40)
			{
				var newpos=(this.pos+1)%this.parentMenu.menuItems.length;
				newitem=this.parentMenu.menuItems[newpos];
			}
			else if (code==37)
			{
				if (parientOrient==BlueprintIT.menus.HORIZONTAL)
				{
					var newpos=this.parentMenu.parentItem.pos-1;
					if (newpos<0)
						newpos=this.parentMenu.parentItem.parentMenu.menuItems.length-1;
					newitem=this.parentMenu.parentItem.parentMenu.menuItems[newpos];
				}
				else
				{
					newitem=this.parentMenu.parentItem;
				}
			}
			else if (code==39)
			{
				if (parientOrient==BlueprintIT.menus.HORIZONTAL)
				{
					var newpos=this.parentMenu.parentItem.pos+1;
					newpos=newpos%this.parentMenu.parentItem.parentMenu.menuItems.length;
					newitem=this.parentMenu.parentItem.parentMenu.menuItems[newpos];
				}
			}
		}
		if (newitem)
		{
			if (newitem.focusElement)
			{
				newitem.focusElement.focus();
			}
			else
			{
				this.changeSelection(newitem);
			}
			return true;
		}
		return false;
	},
	
	mouseOver: function()
	{
		this.manager.log('mouseOver '+this.element.id);

		this.focus();
		if (this.submenu)
		{
			this.submenu.startShow();
		}
		else if (this.parentMenu.parentItem)
		{
			this.parentMenu.startShow();
		}
	},
	
	mouseOut: function()
	{
		this.manager.log('mouseOut '+this.element.id);
		
		this.unfocus();
		if (this.submenu)
			this.submenu.startHide();
	},
	
	setMenuPosition: function()
	{
		if (this.submenu)
		{
			var region = YAHOO.util.Dom.getRegion(this.element);
			var subregion = YAHOO.util.Dom.getRegion(this.submenu.element);

		  var bwidth = YAHOO.util.Dom.getClientWidth();
		  var bheight = YAHOO.util.Dom.getClientHeight();
			if (this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
			{
			  var left = region.left
			  var top = region.bottom;
			  var width = subregion.right-subregion.left;

			  if ((left+width)>bwidth)
			  {
			    left -= width;
			    if (left < 0)
			    	left = 0;
			  }
			}
			else
			{
			  var left = region.right;
			  var top = region.top;
			  var width = subregion.right-subregion.left;
			  
			  if ((left+width)>bwidth)
			  {
			    left = region.left-width;
			    if (left < 0)
			    	left = 0;
			  }
			}
			this.manager.log("setMenuPosition "+left+" "+top);
			YAHOO.util.Dom.setXY(this.submenu.element, [left, top]);
		}
	}
}

function init()
{
	var menuManager = new BlueprintIT.menus.MenuManager();
	menuManager.loadMenus();
}

YAHOO.util.Event.addListener(window, "load", init);
