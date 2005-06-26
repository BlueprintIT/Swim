menuManager = {
	
	popupDelay: 200,
	hideDelay: 200,
	animator: null,
	
	textarea: null,
	
	timerID: [],
	timerTarget: [],
	
	itemcount: 0,
	
	menuitems: [],
	
	HORIZONTAL: 0,
	VERTICAL: 1,
	
	selected: null,
	
	log: function(text)
	{
		//this.textarea.value+=text+"\n";
	},
	
	init: function()
	{
		//this.textarea=document.getElementById('log');
		this.animator=this.instantAnimator;
		addEvent(document,'focus',menuManager.focusEvent,false);
		addEvent(document,'keypress',menuManager.keyPressEvent,true);
	},
	
	callTimer: function(id)
	{
		var target=menuManager.timerTarget[id];
		target.onTimer()
		menuManager.removeTimer(id);
	},
	
	startTimer: function(item,timeout)
	{
		var id = menuManager.timerTarget.length;
		menuManager.timerTarget[id]=item;
		menuManager.timerID[id]=window.setTimeout('menuManager.callTimer('+id+')',timeout);
		return id+1;
	},
	
	removeTimer: function(id)
	{
		menuManager.timerTarget[id]=null;
		menuManager.timerID[id]=null;
		var pos = menuManager.timerID.length-1;
		while ((pos>=0)&&(menuManager.timerID[pos]==null))
		{
			menuManager.timerTarget.pop();
			menuManager.timerID.pop();
			pos--;
		}
	},

	cancelTimer: function(id)
	{
		id--;
		window.clearTimeout(menuManager.timerID[id]);
		menuManager.removeTimer(id);
	},
	
	findMenuItem: function(element)
	{
		if (!element)
			return null;
		
		try
		{
			if (element.id && menuManager.menuitems[element.id])
				return menuManager.menuitems[element.id];
			
			if (element.parentNode)
				return menuManager.findMenuItem(element.parentNode);
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
		menuManager.log(line);
	},
	
	changeSelection: function(newitem)
	{
		if (newitem!=menuManager.selected)
		{
			var sources = menuManager.makeItemList(menuManager.selected);
			var dests = menuManager.makeItemList(newitem);

			while (((dests.length>0)&&(sources.length>0))&&(dests[0]==sources[0]))
			{
				dests.shift();
				sources.shift();
			}

			menuManager.mouseOut(sources);
			menuManager.mouseOver(dests);
			
			menuManager.selected=newitem;
		}
	},
	
	keyPressEvent: function(event)
	{
		var ev = getDOMEvent(event);
		if (ev.type=='keypress')
		{
			if (menuManager.selected)
			{
				if ((ev.keyCode>=37)&&(ev.keyCode<=40))
				{
					if (menuManager.selected.keyPress(ev.keyCode))
					{
						ev.preventDefault();
					}
				}
			}
		}
	},
	
	focusEvent: function(event)
	{
		var ev = getDOMEvent(event);
		if (ev.type=='focus')
		{
			menuManager.changeSelection(menuManager.findMenuItem(ev.target));
		}
	},
	
	mouseEvent: function(event)
	{
		var ev=getDOMEvent(event);
		if (ev.type=='mouseover')
		{
			var dest = menuManager.findMenuItem(ev.target);
			menuManager.changeSelection(dest);
		}
		else if (ev.type=='mouseout')
		{
			var dest = menuManager.findMenuItem(ev.relatedTarget);
			if (!dest)
				menuManager.changeSelection(null);
		}
	},
	
	instantAnimator: {

		startAnimateIn: function(item)
		{
			item.posel.setDisplay('block');
			item.state=3;
		},
		
		animateIn: function(item)
		{
		},
		
		startAnimateOut: function(item)
		{
			item.posel.setDisplay('none');
			item.state=0;
		},
		
		animateOut: function(item)
		{
		}
	},
	
	slideAnimator: {
		
		step: 5,
		delay: 10,

		startAnimateIn: function(item)
		{
			item.clippos=0;
			item.posel.getAssignedStyle().clip='rect(auto, auto, '+item.clippos+'px, auto)';
			item.posel.setDisplay("block");
			item.state=2;
			item.timer=menuManager.startTimer(item,this.delay);
		},
		
		animateIn: function(item)
		{
			item.clippos+=this.step;
			
			if (item.clippos>=item.posel.getHeight())
			{
				item.clippos=item.posel.getHeight();
				item.posel.getAssignedStyle().clip='rect(auto, auto, auto, auto)';
				item.state=3;
			}
			else
			{
				item.posel.getAssignedStyle().clip='rect(auto, auto, '+item.clippos+'px, auto)';
				menuManager.startTimer(item,this.delay);
			}	
		},
		
		startAnimateOut: function(item)
		{
			item.clippos=item.posel.getHeight();
			item.posel.getAssignedStyle().clip='rect(auto, auto, auto, auto)';
			item.state=5;
			item.timer=menuManager.startTimer(item,this.delay);
		},
		
		animateOut: function(item)
		{
			item.clippos-=this.step;
			
			if (item.clippos<=0)
			{
				item.clippos=0;
				item.posel.getAssignedStyle().clip='rect(auto, auto, 0px, auto)';
				item.posel.setDisplay('none');
				item.state=0;
			}
			else
			{
				item.posel.getAssignedStyle().clip='rect(auto, auto, '+item.clippos+'px, auto)';
				menuManager.startTimer(item,this.delay);
			}	
		}		
	},
	
	fadeAnimator: {
		
		step: 0.05,
		delay: 10,

		startAnimateIn: function(item)
		{
			item.posel.setOpacity(0);
			item.posel.setDisplay("block");
			item.state=2;
			item.timer=menuManager.startTimer(item,this.delay);
		},
		
		animateIn: function(item)
		{
			var style = item.posel.getAssignedStyle();
	
			var next = item.posel.getOpacity();
							
			next+=this.step;
	
			if (next>=1)
			{
				item.posel.setOpacity(1);
				item.state=3;
			}
			else
			{
				item.posel.setOpacity(next);
				menuManager.startTimer(item,this.delay);
			}
		},
		
		startAnimateOut: function(item)
		{
			item.posel.setOpacity(1);
			item.state=5;
			item.timer=menuManager.startTimer(item,this.delay);
		},
		
		animateOut: function(item)
		{
			var style = item.posel.getAssignedStyle();
	
			var next = item.posel.getOpacity();
				
			next-=this.step;
	
			if (next<=0)
			{
				item.posel.setOpacity(0);
				item.posel.setDisplay("none");
				item.state=0;
			}
			else
			{
				item.posel.setOpacity(next);
				menuManager.startTimer(item,this.delay);
			}
		}		
	},
	
	hasClass: function(element, cls)
	{
		if (element.className)
		{
			var classes = element.className.split(' ');
			for (var k in classes)
			{
				if (classes[k]==cls)
					return true;
			}
			return false;
		}
		return false;
	},
	
	loadFrom: function(orientation,element,parentitem,parentmenu)
	{
		if (menuManager.hasClass(element,'horizmenu'))
		{
			orientation=menuManager.HORIZONTAL;
		}
		else if (menuManager.hasClass(element,'vertmenu'))
		{
			orientation=menuManager.VERTICAL;
		}
		if (menuManager.hasClass(element,'menuitem'))
		{
			parentitem = new MenuItem(parentmenu,element);
			parentmenu=null;
		}
		else if (menuManager.hasClass(element,'menupopup')||menuManager.hasClass(element,'menu'))
		{
			parentmenu = new Menu(parentitem,orientation,element);
			parentitem=null;
		}
		else if ((element.tagName=='A')&&(parentitem))
		{
			parentitem.focusElement=element;
		}
		var child = element.firstChild;
		while (child)
		{
			if (child.nodeType==1)
				menuManager.loadFrom(orientation,child,parentitem,parentmenu);
			child=child.nextSibling;
		}
	},
	
	loadMenus: function()
	{
		menuManager.loadFrom(menuManager.HORIZONTAL,document.documentElement);
	}
}

function Menu(item,orientation,element)
{
	menuManager.log('Created menu from '+element.tagName);

	this.menuItems=[];
	if (!element.id)
	{
		element.id='bpmm_'+menuManager.itemcount;
	}
	if (item)
	{
		menuManager.menuitems[element.id]=item;
		menuManager.itemcount++;
	
		this.parentItem=item;
		this.parentItem.submenu=this;
	}
	else
	{
		addEvent(element,'mouseover',menuManager.mouseEvent,false);
		addEvent(element,'mouseout',menuManager.mouseEvent,false);
	}
	this.orientation=orientation;
	this.element=element;

	this.posel = new ElementWrapper(this.element);
}

Menu.prototype = {
	
	parentItem: null,
	menuItems: null,
	orientation: null,
	element: null,
	posel: null,
	timer: null,
	state: 0,
	animator: null,
		
	setPosition: function(x,y)
	{
		this.posel.setLeft(x);
		this.posel.setTop(y);
	},

	onTimer: function()
	{
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
				this.parentItem.setMenuPosition();
				this.animator=menuManager.animator;
				this.animator.startAnimateIn(this);
				break;
			case 4:
				menuManager.cancelTimer(this.timer);
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
				menuManager.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
			case 4:
				this.animator=menuManager.animator;
				this.animator.startAnimateOut(this);
				break;
		}
	},
	
	startShow: function()
	{
		//menuManager.log('startShow '+this.element.id);
		switch (this.state)
		{
			case 0:
				this.state=1;
				this.timer=menuManager.startTimer(this,menuManager.popupDelay);
				break;
			case 4:
				menuManager.cancelTimer(this.timer);
				this.state=3;
				break;
			case 5:
				this.state=2;
				break;
		}
	},
	
	startHide: function()
	{
		//menuManager.log('startHide '+this.element.id);
		switch (this.state)
		{
			case 1:
				menuManager.cancelTimer(this.timer);
				this.state=0;
				break;
			case 2:
				this.state=5;
				break;
			case 3:
				this.state=4;
				this.timer=menuManager.startTimer(this,menuManager.hideDelay);
				break;
		}
	}
}

function MenuItem(parent,element)
{
	menuManager.log('Created menuitem from '+element.tagName);

	if (!element.id)
	{
		element.id='bpmm_'+menuManager.itemcount;
	}
	menuManager.menuitems[element.id]=this;
	menuManager.itemcount++;

	this.parentMenu=parent;
	this.element=element;

	this.parentMenu.menuItems.push(this);
	this.pos=this.parentMenu.menuItems.length-1;

	this.posel = new ElementWrapper(element);
}

MenuItem.prototype = {
	
	parentMenu: null,
	orientation: null,
	element: null,
	posel: null,
	submenu: null,
	pos: null,
	focusElement: null,
	
	focus: function()
	{
		this.posel.addClass('menufocus');
	},
	
	unfocus: function()
	{
		this.posel.removeClass('menufocus');
	},
	
	keyPress: function(code)
	{
		var newitem = null;
		if (this.parentMenu.orientation==menuManager.HORIZONTAL)
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
		else if (this.parentMenu.orientation==menuManager.VERTICAL)
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
					if (parentOrient==menuManager.HORIZONTAL)
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
				if (parientOrient==menuManager.HORIZONTAL)
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
				if (parientOrient==menuManager.HORIZONTAL)
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
				menuManager.changeSelection(newitem);
			}
			return true;
		}
		return false;
	},
	
	mouseOver: function()
	{
		menuManager.log('mouseOver '+this.element.id);

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
		menuManager.log('mouseOut '+this.element.id);
		
		this.unfocus();
		if (this.submenu)
			this.submenu.startHide();
	},
	
	setMenuPosition: function()
	{
		if (this.submenu)
		{
			if (this.parentMenu.orientation==menuManager.HORIZONTAL)
			{
				//alert(this.posel.getTop()+' '+this.posel.getHeight());
				this.submenu.setPosition(this.posel.getLeft(),this.posel.getTop()+this.posel.getHeight());
			}
			else
			{
				this.submenu.setPosition(this.posel.getLeft()+this.posel.getWidth(),this.posel.getTop());
			}
		}
	}
}

function init(event)
{
	menuManager.init();
	menuManager.loadMenus();
}

addEvent(window,'load',init,false);
