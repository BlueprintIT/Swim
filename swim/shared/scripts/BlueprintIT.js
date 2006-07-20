var BlueprintIT = {
	menus: {},
	widget: {},
	timing: {},
	validation: {}
}

BlueprintIT.timing.startTimer = function(item,timeout,data)
{
	function timerCallback() {
		item.onTimer(data);
	}
	return window.setTimeout(timerCallback, timeout);
};
	
BlueprintIT.timing.cancelTimer = function(id)
{
	window.clearTimeout(id);
};
