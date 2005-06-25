<?

require('styles.php');
setContentType('text/javascript');

?>

function setBottom(el,value)
{
	var element = document.getElementById(el);
	var posel = new ElementWrapper(element);
	var docel = posel.getContainingBlock();
	var pos = docel.getTop()+docel.getHeight()-value;
	posel.setHeight(pos-posel.getTop());
}

function setRight(el,value)
{
	var element = document.getElementById(el);
	var posel = new ElementWrapper(element);
	var docel = posel.getContainingBlock();
	var pos = docel.getLeft()+docel.getWidth()-value;
	posel.setHeight(pos-posel.getLeft());
}

function checkSizes(event)
{
	setBottom('content',<?= $footerheight+$spacing ?>);
}

//addEvent(window,'load',checkSizes,false);
addEvent(window,'resize',checkSizes,false);
