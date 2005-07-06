function highlight(element)
{
	var el = document.getElementById(element);
	if (el)
	{
		var wrap = new ElementWrapper(el);
		wrap.addClass("highlight");
	}
}

function unhighlight(element)
{
	var el = document.getElementById(element);
	if (el)
	{
		var wrap = new ElementWrapper(el);
		wrap.removeClass("highlight");
	}
}
