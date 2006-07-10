function displayCalendar(id, value)
{
	var date = new Date(value);
	var datestr = date.getMonth()+"/"+date.getDate()+"/"+date.getFullYear();
	var calendar = new YAHOO.widget.Calendar("caltable_"+id, "calendar_"+id, "", datestr);
	calendar.render();
}
