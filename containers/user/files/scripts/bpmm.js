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

BlueprintIT.menus.SlideAnimator = function()
{
}

BlueprintIT.menus.SlideAnimator.prototype = {
	step: 3,
	delay: 10,

	startAnimateIn: function(item)
	{
		item.clippos=0;
		YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, '+item.clippos+'px, auto)');
		item.setVisible(true);
		item.state=2;
		item.timer=BlueprintIT.timing.startTimer(item,this.delay);
	},
	
	animateIn: function(item)
	{
		item.clippos+=this.step;

		var region = YAHOO.util.Dom.getRegion(item.element);
		var height = region.bottom-region.top;
		if (item.clippos>=height)
		{
			item.clippos=height;
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, auto, auto)');
			item.state=3;
		}
		else
		{
			YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, '+item.clippos+'px, auto)');
			BlueprintIT.timing.startTimer(item,this.delay);
		}	
	},
	
	startAnimateOut: function(item)
	{
		var region = YAHOO.util.Dom.getRegion(item.element);
		item.clippos=region.bottom-region.top;
		YAHOO.util.Dom.setStyle(item.element, 'clip', 'rect(auto, auto, auto, auto)');
		item.state=5;
		item.timer=BlueprintIT.timing.startTimer(item,this.delay);
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
			BlueprintIT.timing.startTimer(item,this.delay);
		}	
	}		
}

BlueprintIT.menus.FadeAnimator = function()
{
}

BlueprintIT.menus.FadeAnimator.prototype = {
	step: 0.05,
	delay: 10,

	startAnimateIn: function(item)
	{
		YAHOO.util.Dom.setStyle(item.element, 'opacity', 0);
		item.setVisible(true);
		item.state=2;
		item.opacpos = 0;
		item.timer=BlueprintIT.timing.startTimer(item,this.delay);
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
			BlueprintIT.timing.startTimer(item,this.delay);
		}
	},
	
	startAnimateOut: function(item)
	{
		YAHOO.util.Dom.setStyle(item.element, 'opacity', 1);
		item.state=5;
		item.opacpos = 1;
		item.timer=BlueprintIT.timing.startTimer(item,this.delay);
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
			BlueprintIT.timing.startTimer(item,this.delay);
		}
	}		
}

BlueprintIT.menus.HORIZONTAL = 0;
BlueprintIT.menus.VERTICAL = 1;

BlueprintIT.menus.MenuManager = function()
{
	this.instantAnimator = new BlueprintIT.menus.InstantAnimator();
	this.slideAnimator = new BlueprintIT.menus.SlideAnimator();
	this.fadeAnimator = new BlueprintIT.menus.FadeAnimator();
	this.animator = this.instantAnimator;
	
	YAHOO.util.Event.addListener(document,'focus',this.focusEvent,this,true);
	YAHOO.util.Event.addListener(document,'keypress',this.keyPressEvent,this,true);
}

BlueprintIT.menus.MenuManager.prototype = {
	
	popupDelay: 200,
	hideDelay: 200,
	
	animator: null,
	slideAnimator: null,
	fadeAnimator: null,
	instantAnimator: null,
	
	textarea: null,
	
	selected: null,
	
	log: function(text)
	{
		return;
		if (!this.textarea)
			this.textarea=document.getElementById('log');
		if (this.textarea)
			this.textarea.value+=text+"\n";
	},
	
	findMenuItem: function(element)
	{
		if (!element)
			return null;
		
		try
		{
			if (element.menuitem)
				return element.menuitem;
			
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
			items[k].mouseOut();
	},
	
	mouseOver: function(items)
	{
		for (var k in items)
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
		if (ev.type=='keypress')
		{
			this.log("keyPressEvent");
			if ((this.selected) && (ev.keyCode>=37) && (ev.keyCode<=40))
			{
				if (this.selected.keyPress(ev.keyCode))
					ev.preventDefault();
			}
		}
	},
	
	focusEvent: function(ev)
	{
		if (ev.type=='focus')
		{
			this.log("focusEvent");
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
			animator = this.fadeAnimator;
		else if (YAHOO.util.Dom.hasClass(element,'slidein'))
			animator = this.slideAnimator;
		else if (YAHOO.util.Dom.hasClass(element,'appearin'))
			animator = this.instantAnimator;
		
		if (YAHOO.util.Dom.hasClass(element,'menuitem'))
		{
			//YAHOO.util.Dom.addClass(element,'level'+depth);
			parentitem = new MenuItem(this,parentmenu,element);
			parentmenu=null;
		}
		else if (YAHOO.util.Dom.hasClass(element,'menupopup')||YAHOO.util.Dom.hasClass(element,'menu'))
		{
			depth++;
			//YAHOO.util.Dom.addClass(element,'level'+depth);
			parentmenu = new Menu(this,parentitem,orientation,element,animator);
			parentitem=null;
		}
		else if ((element.tagName=='A')&&(parentitem))
		{
			//YAHOO.util.Dom.addClass(element,'level'+depth);
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
	this.manager.log('Created menu from '+element.tagName);
	this.animator = animator;

	this.menuItems=[];
	if (item)
	{
		element.menuitem = item;
	
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

/*
   State 0 - normal
             can move to state 1 on mouse over
   State 1 - waiting to appear
             can move to state 0 on mouse out
             can move to state 2/3 on timer complete
   State 2 - animating in
             can move to state 5 on mouse out
             can move to state 3 on animation complete
   State 3 - visible
             can move to state 4 on mouse out
   State 4 - waiting to disappear
             can move to state 3 on mouse over
             can move to state 5/0 on timer complete
   State 5 - animating out
             can move to state 2 on mouse over
             can move to state 0 on animation complete
*/

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
				this.animator.startAnimateIn(this);
				break;
			case 4:
				BlueprintIT.timing.cancelTimer(this.timer);
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
				BlueprintIT.timing.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
			case 4:
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
				this.timer=BlueprintIT.timing.startTimer(this,this.manager.popupDelay);
				break;
			case 4:
				BlueprintIT.timing.cancelTimer(this.timer);
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
				BlueprintIT.timing.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
				this.state=4;
				this.timer=BlueprintIT.timing.startTimer(this,this.manager.hideDelay);
				break;
		}
	}
}

function MenuItem(manager,parent,element)
{
	this.manager = manager;
	this.manager.log('Created menuitem from '+element.tagName);

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
	},
	
	focus: function()
	{
		if (this.parentMenu.parentItem)
			YAHOO.util.Dom.addClass(this.element, 'currentfocus');
		if (this.focusEl)
			YAHOO.util.Dom.addClass(this.focusElement, 'itemfocus');

		/*YAHOO.util.Dom.addClass(this.element, 'focused');
		if (this.focusElement)
			YAHOO.util.Dom.addClass(this.focusElement, 'focused');*/
	},
	
	unfocus: function()
	{
		if (this.parentMenu.parentItem)
			YAHOO.util.Dom.removeClass(this.element, 'currentfocus');
		if (this.focusEl)
			YAHOO.util.Dom.removeClass(this.focusElement, 'itemfocus');

		/*YAHOO.util.Dom.removeClass(this.element, 'focused');
		if (this.focusElement)
			YAHOO.util.Dom.removeClass(this.focusElement, 'focused');*/
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
				newitem.focusElement.focus();
			else
				this.changeSelection(newitem);
			return true;
		}
		return false;
	},
	
	mouseOver: function()
	{
		this.manager.log('mouseOver '+this.element.id);

		YAHOO.util.Dom.addClass(this.element, 'menufocus');
		/*YAHOO.util.Dom.addClass(this.element, 'opened');
		if (this.focusElement)
			YAHOO.util.Dom.addClass(this.focusElement, 'opened');*/

		if (this.submenu)
			this.submenu.startShow();
		else if (this.parentMenu.parentItem)
			this.parentMenu.startShow();
	},
	
	mouseOut: function()
	{
		this.manager.log('mouseOut '+this.element.id);
		
		YAHOO.util.Dom.removeClass(this.element, 'menufocus');
		/*YAHOO.util.Dom.removeClass(this.element, 'opened');
		if (this.focusElement)
			YAHOO.util.Dom.removeClass(this.focusElement, 'opened');*/

		if (this.submenu)
			this.submenu.startHide();
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
			  var left = region.right-width;
			  var top = region.bottom;

			  if (((top+height)>bwidth) && (region.top >= height))
			  {
			  	top = region.top-height;
			  }
			}
			else
			{
			  var left = region.right;
			  var top = region.top;

			  if (((left+width)>bwidth) && (region.left >= width))
			  {
			    left = region.left-width;
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
		}
	}
}

var menuManager = new BlueprintIT.menus.MenuManager();

YAHOO.util.Event.addListener(window,'load',menuManager.loadMenus,menuManager,true);
