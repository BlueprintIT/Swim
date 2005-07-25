var HoverManager = {

images: [],
target: null,
original: null,
menu: null,

mouseOver: function(event)
{
	event=getDOMEvent(event);
	var node = event.target;
	if (event.currentTarget)
	{
		node=event.currentTarget;
	}
	else
	{
		while (node)
		{
			if ((node.nodeType==1)&&(node.tagName=='TD'))
			{
				break;
			}
			node=node.parentNode;
		}
	}
	if ((node)&&(node.tagName=='TD'))
	{
		var pos=0;
		var node = node.previousSibling;
		while (node)
		{
			if ((node.nodeType==1)&&(node.tagName=='TD'))
				pos++;
			node=node.previousSibling;
		}
		HoverManager.target.src=HoverManager.images[pos].src;
	}
},

mouseOut: function(event)
{
	event=getDOMEvent(event);
	var node = event.relatedTarget;
	while ((node)&&(node!=HoverManager.menu))
	{
		node=node.parentNode;
	}
	if (!node)
	{
		HoverManager.target.src=HoverManager.original;
	}
},

init: function()
{
<?
for ($i=0; $i<6; $i++)
{
	$icon = new Request();
	$icon->method='view';
	$icon->resource='global/template/base/layout/hover'.($i+1).'.jpg';
?>	HoverManager.images[<?= $i ?>] = new Image();
	HoverManager.images[<?= $i ?>].src = '<?= $icon->encode() ?>';<?
}
?>
	HoverManager.target=document.getElementById("cogs");
	HoverManager.original=HoverManager.target.src;
	HoverManager.menu = document.getElementById("menu");
	if (HoverManager.menu)
	{
		addEvent(HoverManager.menu,'mouseout',HoverManager.mouseOut,false);
		var items = HoverManager.menu.getElementsByTagName("td");
		for (var i=0; i<items.length; i++)
		{
			addEvent(items[i],'mouseover',HoverManager.mouseOver,false);
		}
	}
}

}

addEvent(window,'load',HoverManager.init,false);
