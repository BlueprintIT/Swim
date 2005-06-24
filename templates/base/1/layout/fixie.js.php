<?

require('styles.php');
setContentType('text/javascript');

?>
function checkSize(el,delw,delh)
{
	var element = document.getElementById(el);
	var posel = new ElementWrapper(element);
	var docel = new ElementWrapper(document.documentElement);
	if (delw)
	{
		var rldelw=docel.getWidth()-posel.getWidth();
		if (Math.abs(rldelw-delw)>=5)
		{
			posel.setWidth(docel.getWidth()-delw);
		}
	}
	if (delh)
	{
		var rldelh=docel.getHeight()-posel.getHeight();
		if (Math.abs(rldelh-delh)>=5)
		{
			posel.setHeight(docel.getHeight()-delh);
		}
	}
}

function checkSizes(event)
{
	checkSize('content',null,<?= $footerheight+$spacing+$headerheight+$spacing+$menuheight+$spacing?>);
}

//addEvent(window,'load',checkSizes,false);
addEvent(window,'resize',checkSizes,false);
