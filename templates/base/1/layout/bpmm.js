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
	
	log: function(text)
	{
		//this.textarea.value+=text+"\n";
	},
	
	init: function()
	{
		//this.textarea=document.getElementById('log');
		this.animator=this.instantAnimator;
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
		list.push(bottom);
		
		while (bottom.parentMenu!=null)
		{
			bottom=bottom.parentMenu.parentItem;
			list.push(bottom);
		}
		list.reverse();

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
	
	mouseEvent: function(event)
	{
		var ev=getDOMEvent(event);
		if (ev.type=='mouseover')
		{
			var source = menuManager.findMenuItem(ev.relatedTarget);
			if (source)
			{
				while (source.parentMenu)
					source=source.parentMenu.parentItem;
			}
			var dest = menuManager.findMenuItem(ev.target);
			if (dest)
			{
				var dests = menuManager.makeItemList(dest);
				if ((!source)||(dests[0]!=source))
					menuManager.mouseOver(dests);
			}
		}
		else if (ev.type=='mouseout')
		{
			var source = menuManager.findMenuItem(ev.target);
			
			if (source)
			{
				var sources = menuManager.makeItemList(source);

				var dest = menuManager.findMenuItem(ev.relatedTarget);
				
				if (dest)
				{
					var dests = menuManager.makeItemList(dest);

					if (dests[0]==sources[0])
					{
						//menuManager.log('Mouseout '+source.element.id+' -> '+dest.element.id);
						//menuManager.logChain(sources);
						//menuManager.logChain(dests);
						//menuManager.log('');
						
						while (((dests.length>0)&&(sources.length>0))&&(dests[0]==sources[0]))
						{
							dests.shift();
							sources.shift();
						}
						
						//menuManager.logChain(sources);
						//menuManager.logChain(dests);
						//menuManager.log('');

						menuManager.mouseOut(sources);
						menuManager.mouseOver(dests);
					}
					else
					{
						menuManager.mouseOut(sources);
					}
				}
				else
				{
					menuManager.mouseOut(sources);
				}
			}
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
			return element.className.indexOf(cls)>=0;
		}
		return false;
	},
	
	loadFrom: function(orientation,element,parentitem,parentmenu)
	{
		if (menuManager.hasClass(element,'menuitem'))
		{
			var item = new MenuItem(parentmenu,orientation,element);
			parentitem=item;
			parentmenu=null;
		}
		else
		{
			if (menuManager.hasClass(element,'menupopup'))
			{
				var menu = new Menu(parentitem,element);
				parentitem=null;
				parentmenu=menu;
			}
			
			if (menuManager.hasClass(element,'horizmenu'))
			{
				orientation=menuManager.HORIZONTAL;
			}
			else if (menuManager.hasClass(element,'vertmenu'))
			{
				orientation=menuManager.VERTICAL;
			}
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

function Menu(item,element)
{
	//menuManager.log('Created menu from '+element.tagName);

	if (!element.id)
	{
		element.id='bpmm_'+menuManager.itemcount;
	}
	menuManager.menuitems[element.id]=item;
	menuManager.itemcount++;

	this.parentItem=item;
	this.element=element;
	this.parentItem.submenu=this;

	this.posel = new ElementWrapper(this.element);
}

Menu.prototype = {
	
	parentItem: null,
	menuItems: [],
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

function MenuItem(parent,orientation,element)
{
	//menuManager.log('Created menuitem from '+element.tagName);

	if (!element.id)
	{
		element.id='bpmm_'+menuManager.itemcount;
	}
	menuManager.menuitems[element.id]=this;
	menuManager.itemcount++;

	this.parentMenu=parent;
	this.orientation=orientation;
	this.element=element;
	if (this.parentMenu)
		this.parentMenu.menuItems.push(this);

	this.posel = new ElementWrapper(element);
	
	if (!this.parentMenu)
	{
		addEvent(element,'mouseover',menuManager.mouseEvent,false);
		addEvent(element,'mouseout',menuManager.mouseEvent,false);
	}
}

MenuItem.prototype = {
	
	parentMenu: null,
	orientation: null,
	element: null,
	posel: null,
	submenu: null,
	
	focus: function()
	{
		this.posel.addClass('menufocus');
	},
	
	unfocus: function()
	{
		this.posel.removeClass('menufocus');
	},
	
	mouseOver: function()
	{
		menuManager.log('mouseOver '+this.element.id);

		this.focus();
		if (this.submenu)
		{
			this.submenu.startShow();
		}
		else if (this.parentMenu)
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
			if (this.orientation==menuManager.HORIZONTAL)
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
