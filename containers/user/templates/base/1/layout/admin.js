function findAdminPanel(event)
{
	event=getDOMEvent(event);
	var element = event.target;
	while ((element)&&(element.className!="adminpanel"))
	{
		element=element.parentNode;
	}
	return element;
}

function highlight(event)
{
	element=findAdminPanel(event);
	if (element)
	{
		var el = document.getElementById(element.id.substring(0,element.id.length-5));
		if (el)
		{
			var wrap = new ElementWrapper(el);
			wrap.addClass("highlight");
		}
	}
}

function unhighlight(event)
{
	element=findAdminPanel(event);
	if (element)
	{
		var el = document.getElementById(element.id.substring(0,element.id.length-5));
		if (el)
		{
			var wrap = new ElementWrapper(el);
			wrap.removeClass("highlight");
		}
	}
}

function adminInit(event)
{
	var divs = document.getElementsByTagName("div");
	for (var i=0; i<divs.length; i++)
	{
		if (divs[i].className=="adminpanel")
		{
			addEvent(divs[i],"mouseover",highlight,false);
			addEvent(divs[i],"mouseout",unhighlight,false);
		}
	}
}

addEvent(window,"load",adminInit,false);
