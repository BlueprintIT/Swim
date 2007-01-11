
BlueprintIT.menus.InstantAnimator=function()
{}
BlueprintIT.menus.InstantAnimator.prototype={startAnimateIn:function(item)
{item.setVisible(true);YAHOO.util.Dom.removeClass(item.element,'hidden');item.state=3;},animateIn:function(item)
{},startAnimateOut:function(item)
{YAHOO.util.Dom.addClass(item.element,'hidden');item.state=5;item.timer=BlueprintIT.timing.startTimer(item,5);},animateOut:function(item)
{item.setVisible(false);item.state=0;}}
BlueprintIT.menus.SlideAnimator=function()
{}
BlueprintIT.menus.SlideAnimator.prototype={steps:15,delay:10,startAnimateIn:function(item)
{item.clippos=0;YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, '+item.clippos+'px, auto)');YAHOO.util.Dom.removeClass(item.element,'hidden');item.setVisible(true);item.state=2;item.timer=BlueprintIT.timing.startTimer(item,this.delay);},animateIn:function(item)
{var region=YAHOO.util.Dom.getRegion(item.element);var height=region.bottom-region.top;var step=height/this.steps;item.clippos+=step;if(item.clippos>=height)
{item.clippos=height;YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, auto, auto)');item.state=3;}
else
{YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, '+item.clippos+'px, auto)');BlueprintIT.timing.startTimer(item,this.delay);}},startAnimateOut:function(item)
{var region=YAHOO.util.Dom.getRegion(item.element);item.clippos=region.bottom-region.top;YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, '+item.clippos+'px, auto)');item.state=5;item.timer=BlueprintIT.timing.startTimer(item,this.delay);},animateOut:function(item)
{var region=YAHOO.util.Dom.getRegion(item.element);var height=region.bottom-region.top;var step=height/this.steps;item.clippos-=step;if(item.clippos<=0)
{if(YAHOO.util.Dom.hasClass(item.element,'hidden'))
{item.clippos=0;YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, 0px, auto)');item.setVisible(false);item.state=0;}
else
{YAHOO.util.Dom.addClass(item.element,'hidden');BlueprintIT.timing.startTimer(item,5);}}
else
{YAHOO.util.Dom.setStyle(item.element,'clip','rect(auto, auto, '+item.clippos+'px, auto)');BlueprintIT.timing.startTimer(item,this.delay);}}}
BlueprintIT.menus.FadeAnimator=function()
{}
BlueprintIT.menus.FadeAnimator.prototype={step:0.05,delay:10,startAnimateIn:function(item)
{YAHOO.util.Dom.setStyle(item.element,'opacity',0);item.setVisible(true);YAHOO.util.Dom.removeClass(item.element,'hidden');item.state=2;item.opacpos=0;item.timer=BlueprintIT.timing.startTimer(item,this.delay);},animateIn:function(item)
{var next=item.opacpos;next+=this.step;if(next>=1)
{YAHOO.util.Dom.setStyle(item.element,'opacity',1);item.opacpos=1;item.state=3;}
else
{YAHOO.util.Dom.setStyle(item.element,'opacity',next);item.opacpos=next;BlueprintIT.timing.startTimer(item,this.delay);}},startAnimateOut:function(item)
{YAHOO.util.Dom.setStyle(item.element,'opacity',1);item.state=5;item.opacpos=1;item.timer=BlueprintIT.timing.startTimer(item,this.delay);},animateOut:function(item)
{var next=item.opacpos;next-=this.step;if(next<=0)
{if(YAHOO.util.Dom.hasClass(item.element,'hidden'))
{YAHOO.util.Dom.setStyle(item.element,'opacity',0);item.opacpos=0;item.setVisible(false);item.state=0;}
else
{YAHOO.util.Dom.addClass(item.element,'hidden');BlueprintIT.timing.startTimer(item,5);}}
else
{YAHOO.util.Dom.setStyle(item.element,'opacity',next);item.opacpos=next;BlueprintIT.timing.startTimer(item,this.delay);}}}
BlueprintIT.menus.HORIZONTAL=1;BlueprintIT.menus.VERTICAL=2;BlueprintIT.menus.MenuManager=function()
{this.instantAnimator=new BlueprintIT.menus.InstantAnimator();this.slideAnimator=new BlueprintIT.menus.SlideAnimator();this.fadeAnimator=new BlueprintIT.menus.FadeAnimator();this.animator=this.instantAnimator;YAHOO.util.Event.addListener(document,'keydown',this.keyPressEvent,this,true);var ua=navigator.userAgent.toLowerCase();if(ua.indexOf('opera')!=-1)
this.browser='opera';else if(ua.indexOf('msie 7')!=-1)
this.browser='ie7';else if(ua.indexOf('msie')!=-1)
this.browser='ie';else if(ua.indexOf('safari')!=-1)
this.browser='safari';else if(ua.indexOf('gecko')!=-1)
this.browser='gecko';else
this.browser='unknown';}
BlueprintIT.menus.MenuManager.prototype={popupDelay:200,hideDelay:200,animator:null,slideAnimator:null,fadeAnimator:null,instantAnimator:null,browser:'unknown',textarea:null,selected:null,log:function(text)
{return;if(!this.textarea)
this.textarea=document.getElementById('log');if(this.textarea)
this.textarea.value=text+"\n"+this.textarea.value;},findMenuItemFromMenu:function(menu,event)
{var pageX=YAHOO.util.Event.getPageX(event);var pageY=YAHOO.util.Event.getPageY(event);this.log('Seeking menu item for '+pageX+' '+pageY);var i;for(i=0;i<menu.menuItems.length;i++)
{var region=YAHOO.util.Dom.getRegion(menu.menuItems[i].element);this.log('Checking '+region.left+' '+region.right+' '+region.top+' '+region.bottom);if((pageX<region.left)||(pageX>region.right))
continue;if((pageY<region.top)||(pageY>region.bottom))
continue;return menu.menuItems[i];}
return menu.parentItem;},findMenuItem:function(element,event)
{if(!element)
return null;this.log('Seeking menu item for '+element.tagName+' '+element.className);try
{while(element)
{if(element.menuitem)
return element.menuitem;else if(element.menu)
return element.menu.parentItem;element=element.parentNode;}}
catch(e)
{this.log(e);}
return null;},mouseOut:function(items)
{items.reverse();for(var k=0;k<items.length;k++)
items[k].mouseOut();},mouseOver:function(items)
{for(var k=0;k<items.length;k++)
items[k].mouseOver();},makeItemList:function(bottom)
{var list=[];if(bottom)
{list.push(bottom);while((bottom.parentMenu!=null)&&(bottom.parentMenu.parentItem!=null))
{bottom=bottom.parentMenu.parentItem;list.push(bottom);}
list.reverse();}
return list;},logChain:function(chain)
{var line='';for(var k in chain)
line+=chain[k].element.id+' ';this.log(line);},changeSelection:function(newitem)
{if(newitem!=this.selected)
{var sources=this.makeItemList(this.selected);var dests=this.makeItemList(newitem);while(((dests.length>0)&&(sources.length>0))&&(dests[0]==sources[0]))
{dests.shift();sources.shift();}
this.mouseOut(sources);this.mouseOver(dests);if(this.selected)
this.selected.unfocus();this.selected=newitem;if(this.selected)
this.selected.focus();}},keyPressEvent:function(ev)
{this.log("keyPressEvent "+ev.keyCode);if((this.selected)&&(ev.keyCode>=37)&&(ev.keyCode<=40))
{if(this.selected.keyPress(ev.keyCode))
YAHOO.util.Event.preventDefault(ev);}},focusEvent:function(ev)
{this.log("focusEvent");this.changeSelection(this.findMenuItem(YAHOO.util.Event.getTarget(ev),ev));},mouseEvent:function(ev)
{if(ev.type=='mouseover')
{var dest=this.findMenuItem(YAHOO.util.Event.getTarget(ev),ev);this.changeSelection(dest);}
else if(ev.type=='mouseout')
{var dest=this.findMenuItem(YAHOO.util.Event.getRelatedTarget(ev),ev);if(!dest)
this.changeSelection(null);}
else if((ev.type=='click')&&(YAHOO.util.Event.getButton(ev)==0))
{this.log('Got click event - '+YAHOO.util.Event.getButton(ev));var dest=this.findMenuItem(YAHOO.util.Event.getTarget(ev));if((dest.focusElement)&&(dest.focusElement!=YAHOO.util.Event.getTarget(ev)))
{this.log('Event might be a missed click');var node=dest.focusElement.parentNode;while(node&&node!=dest.element&&node!=YAHOO.util.Event.getTarget(ev))
node=node.parentNode;if(node==YAHOO.util.Event.getTarget(ev))
{this.log('Event was between focus and menuitem, passing to focus.');YAHOO.util.Event.stopEvent(ev);document.location.href=dest.focusElement.href;}}}},loadFrom:function(element,animator,orientation,depth,parentitem,parentmenu)
{if(!depth)
depth=0;if(!animator)
animator=this.animator;if(YAHOO.util.Dom.hasClass(element,'horizmenu'))
orientation=BlueprintIT.menus.HORIZONTAL;else if(YAHOO.util.Dom.hasClass(element,'vertmenu'))
orientation=BlueprintIT.menus.VERTICAL;if(YAHOO.util.Dom.hasClass(element,'fadein'))
animator=this.fadeAnimator;else if(YAHOO.util.Dom.hasClass(element,'slidein'))
animator=this.slideAnimator;else if(YAHOO.util.Dom.hasClass(element,'appearin'))
animator=this.instantAnimator;if(YAHOO.util.Dom.hasClass(element,'menuitem'))
{YAHOO.util.Dom.addClass(element,'level'+depth);parentitem=new MenuItem(this,parentmenu,element);parentmenu=null;}
else if(YAHOO.util.Dom.hasClass(element,'menupopup')||YAHOO.util.Dom.hasClass(element,'menu'))
{depth++;YAHOO.util.Dom.addClass(element,'level'+depth);parentmenu=new Menu(this,parentitem,orientation,element,animator);parentitem=null;}
else if((element.tagName=='A')&&(parentitem))
{YAHOO.util.Dom.addClass(element,'level'+depth);parentitem.setFocusElement(element);}
var child=element.firstChild;while(child)
{if(child.nodeType==1)
this.loadFrom(child,animator,orientation,depth,parentitem,parentmenu);child=child.nextSibling;}},loadMenus:function()
{this.log("Loading");this.loadFrom(document.documentElement,this.animator);}}
function Menu(manager,item,orientation,element,animator)
{this.manager=manager;this.animator=animator;element.menu=this;this.menuItems=[];if(item)
{this.parentItem=item;this.parentItem.submenu=this;}
else
{this.manager.log("Adding listener to "+element.id);YAHOO.util.Event.addListener(element,'mouseover',this.manager.mouseEvent,this.manager,true);YAHOO.util.Event.addListener(element,'mouseout',this.manager.mouseEvent,this.manager,true);YAHOO.util.Event.addListener(element,'click',this.manager.mouseEvent,this.manager,true);}
this.orientation=orientation;this.element=element;if(manager.browser=='ie'){this.iframe=document.createElement("iframe");this.iframe.className="menuframe";this.iframe.frameBorder="0";YAHOO.util.Dom.setStyle(this.iframe,"position","absolute");YAHOO.util.Dom.setStyle(this.iframe,"display","none");YAHOO.util.Dom.setStyle(this.iframe,"zIndex","0");YAHOO.util.Dom.setStyle(this.iframe,"opacity","0");document.body.insertBefore(this.iframe,document.body.firstChild);}}
Menu.prototype={parentItem:null,menuItems:null,orientation:null,element:null,timer:null,state:0,animator:null,manager:null,iframe:null,setPosition:function(x,y)
{YAHOO.util.Dom.setXY(this.element,[x,y]);},setVisible:function(value)
{if(value)
{YAHOO.util.Dom.setStyle(this.element,"display","block");if(this.iframe)
YAHOO.util.Dom.setStyle(this.iframe,"display","block");this.parentItem.setMenuPosition();}
else
{YAHOO.util.Dom.setStyle(this.element,"display","none");if(this.iframe)
YAHOO.util.Dom.setStyle(this.iframe,"display","none");YAHOO.util.Dom.removeClass(this.parentItem.element,'opened');if(this.parentItem.focusElement)
YAHOO.util.Dom.removeClass(this.parentItem.focusElement,'opened');}},onTimer:function()
{switch(this.state)
{case 1:this.show();break;case 2:this.animator.animateIn(this);break;case 4:this.hide();break;case 5:this.animator.animateOut(this);break;}},show:function()
{switch(this.state)
{case 0:case 1:this.animator.startAnimateIn(this);break;case 4:BlueprintIT.timing.cancelTimer(this.timer);this.state=3;break;case 5:this.state=2;break;}},hide:function()
{switch(this.state)
{case 1:BlueprintIT.timing.cancelTimer(this.timer);this.state=0;break;case 2:this.state=5;break;case 3:case 4:this.animator.startAnimateOut(this);break;}},startShow:function()
{switch(this.state)
{case 0:this.state=1;this.timer=BlueprintIT.timing.startTimer(this,this.manager.popupDelay);break;case 4:BlueprintIT.timing.cancelTimer(this.timer);this.state=3;break;case 5:this.state=2;break;}},startHide:function()
{switch(this.state)
{case 1:BlueprintIT.timing.cancelTimer(this.timer);YAHOO.util.Dom.removeClass(this.parentItem.element,'opened');if(this.parentItem.focusElement)
YAHOO.util.Dom.removeClass(this.parentItem.focusElement,'opened');this.state=0;break;case 2:this.state=5;break;case 3:this.state=4;this.timer=BlueprintIT.timing.startTimer(this,this.manager.hideDelay);break;}}}
function MenuItem(manager,parent,element)
{this.manager=manager;element.menuitem=this;this.parentMenu=parent;this.element=element;this.parentMenu.menuItems.push(this);this.pos=this.parentMenu.menuItems.length-1;}
MenuItem.prototype={parentMenu:null,orientation:null,element:null,submenu:null,pos:null,focusElement:null,manager:null,setFocusElement:function(el)
{this.focusElement=el;YAHOO.util.Event.addListener(this.focusElement,'focus',this.focusEvent,this,true);YAHOO.util.Event.addListener(this.focusElement,'blur',this.blurEvent,this,true);},focusEvent:function(ev)
{this.manager.log("focusEvent");this.manager.changeSelection(this);},blurEvent:function(ev)
{this.manager.log("blurEvent");this.manager.changeSelection(null);},focus:function()
{YAHOO.util.Dom.addClass(this.element,'focused');if(this.focusElement)
YAHOO.util.Dom.addClass(this.focusElement,'focused');},unfocus:function()
{YAHOO.util.Dom.removeClass(this.element,'focused');if(this.focusElement)
YAHOO.util.Dom.removeClass(this.focusElement,'focused');},keyPress:function(code)
{var newitem=null;if(this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
{if(code==39)
{var npos=(this.pos+1)%this.parentMenu.menuItems.length;newitem=this.parentMenu.menuItems[npos];}
else if(code==37)
{var npos=(this.pos+(this.parentMenu.menuItems.length-1))%this.parentMenu.menuItems.length;newitem=this.parentMenu.menuItems[npos];}
else if(code==38)
{if(this.parentMenu.parentItem)
{newitem=this.parentMenu.parentItem;}}
else if((code==40)&&(this.submenu))
{newitem=this.submenu.menuItems[0];}}
else if(this.parentMenu.orientation==BlueprintIT.menus.VERTICAL)
{var parentOrient=null;if(this.parentMenu.parentItem)
{parentOrient=this.parentMenu.parentItem.parentMenu.orientation;}
if(code==38)
{if(this.pos==0)
{if(parentOrient==BlueprintIT.menus.HORIZONTAL)
newitem=this.parentMenu.parentItem;else
newitem=this.parentMenu.menuItems[this.parentMenu.menuItems.length-1];}
else
{newitem=this.parentMenu.menuItems[this.pos-1];}}
else if(code==40)
{var newpos=(this.pos+1)%this.parentMenu.menuItems.length;newitem=this.parentMenu.menuItems[newpos];}
else if(code==37)
{if(parentOrient==BlueprintIT.menus.HORIZONTAL)
{var newpos=this.parentMenu.parentItem.pos-1;if(newpos<0)
newpos=this.parentMenu.parentItem.parentMenu.menuItems.length-1;newitem=this.parentMenu.parentItem.parentMenu.menuItems[newpos];}
else
{newitem=this.parentMenu.parentItem;}}
else if(code==39)
{if(this.submenu)
{newitem=this.submenu.menuItems[0];}
else
{var parent=this.parentMenu.parentItem;while(parent&&parent.parentMenu.orientation!=BlueprintIT.menus.HORIZONTAL)
parent=parent.parentMenu.parentItem;if(parent)
{var newpos=parent.pos+1;newpos=newpos%parent.parentMenu.menuItems.length;newitem=parent.parentMenu.menuItems[newpos];}}}}
if(newitem)
{if(newitem.focusElement)
newitem.focusElement.focus();else
this.manager.changeSelection(newitem);return true;}
return false;},mouseOver:function()
{this.manager.log('mouseOver '+this.element.id);YAHOO.util.Dom.addClass(this.element,'opened');if(this.focusElement)
YAHOO.util.Dom.addClass(this.focusElement,'opened');if(this.submenu)
this.submenu.startShow();else if(this.parentMenu.parentItem)
this.parentMenu.startShow();},mouseOut:function()
{this.manager.log('mouseOut '+this.element.id);if(this.submenu)
this.submenu.startHide();else
{YAHOO.util.Dom.removeClass(this.element,'opened');if(this.focusElement)
YAHOO.util.Dom.removeClass(this.focusElement,'opened');}},setMenuPosition:function()
{if(this.submenu)
{var region=YAHOO.util.Dom.getRegion(this.element);var subregion=YAHOO.util.Dom.getRegion(this.submenu.element);var width=subregion.right-subregion.left;var height=subregion.bottom-subregion.top;var bwidth=YAHOO.util.Dom.getClientWidth();var bheight=YAHOO.util.Dom.getClientHeight();if(this.parentMenu.orientation==BlueprintIT.menus.HORIZONTAL)
{var left=region.left
var top=region.bottom;if(((top+height)>bwidth)&&(region.top>=height))
{top=region.top-height;}}
else
{var left=region.right;var top=region.top;if(((left+width)>bwidth)&&(region.left>=width))
{left=region.left-width;}}
if((left+width)>bwidth)
left=bwidth-width;if(left<0)
left=0;if((top+height)>bheight)
top=bheight-height;if(top<0)
top=0;this.manager.log("setMenuPosition "+left+" "+top);YAHOO.util.Dom.setXY(this.submenu.element,[left,top]);if(this.submenu.iframe){this.submenu.iframe.style.width=width+"px";this.submenu.iframe.style.height=height+"px";YAHOO.util.Dom.setXY(this.submenu.iframe,[left,top]);}}}}
var menuManager=new BlueprintIT.menus.MenuManager();