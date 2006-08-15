function selectDate(calendar, input)
{
	var field = document.getElementById(input);
	var dates = calendar.getSelectedDates();
	var date;
	if (dates.length==0)
		date = Date.now();
	else
		date = dates[0].getTime();
	field.value = date/1000;
}

function displayCalendar(id, value)
{
	var date = new Date(value*1000);
	var datestr = (date.getMonth()+1)+"/"+date.getDate()+"/"+date.getFullYear();
	var calendar = new YAHOO.widget.Calendar("cal_"+id, "calendar_"+id, "", datestr);
	calendar.onSelect = function() { selectDate(calendar, 'field:'+id) };
	calendar.render();
	return calendar;
}
