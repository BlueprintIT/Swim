// Generic field creators

function create_simple_field(basefield, position, field, container)
{
	var input = document.createElement("input");
	input.setAttribute("type", "text");
	input.setAttribute("id", "field_"+basefield+"_"+position+"_"+field);
	input.setAttribute("name", basefield+"["+position+"]."+field);
	container.appendChild(input);
}

function create_date_field(basefield, position, field, container)
{
}

function create_item_field(basefield, position, field, container)
{
}

function create_file_field(basefield, position, field, container)
{
}

function createCompoundField(type, basefield, position, field, container)
{
	switch (type)
	{
		case 'file':
			create_file_field(basefield, position, field, container);
			break;
		case 'item':
			create_item_field(basefield, position, field, container);
			break;
		case 'date':
			create_date_field(basefield, position, field, container);
			break;
		default:
			create_simple_field(basefield, position, field, container);
	}
}

// Generic field switchers

function switch_simple_field(basefield, field, pos1, pos2)
{
	var field1 = document.getElementById("field_"+basefield+"_"+pos1+"_"+field);
	var field2 = document.getElementById("field_"+basefield+"_"+pos2+"_"+field);
	var temp = field1.value;
	field1.value = field2.value;
	field2.value = temp;
}

function switch_date_field(basefield, field, pos1, pos2)
{
}

function switch_item_field(basefield, field, pos1, pos2)
{
}

function switch_file_field(basefield, field, pos1, pos2)
{
}

function switchCompoundField(type, basefield, field, pos1, pos2)
{
	switch (type)
	{
		case 'file':
			switch_file_field(basefield, field, pos1, pos2);
			break;
		case 'item':
			switch_item_field(basefield, field, pos1, pos2);
			break;
		case 'date':
			switch_date_field(basefield, field, pos1, pos2);
			break;
		default:
			switch_simple_field(basefield, field, pos1, pos2);
	}
}

// Row manipulation

function createCompoundRow(compound)
{
  var body = document.getElementById('tbody_'+compound.id);
  var rowcount = body.rows.length;
  var row = document.createElement('tr');
  body.appendChild(row);
  var cell;
  for (var i in compound.fields)
  {
    cell = document.createElement('td');
    row.appendChild(cell);
    createCompoundField(compound.fields[i], compound.id, rowcount, i, cell);
  }
  cell = document.createElement('td');
  row.appendChild(cell);
  
  buttons = "<a href=\"#\" onclick=\"moveCompoundRow(compound_"+compound.id+", this.parentNode.parentNode, true); return false\">";
  buttons+= "<img alt=\"Move up\" title=\"Move up\" src=\""+CONTENT+"/icons/up-purple.gif\">";
  buttons+= "</a>";
  buttons+= "<a href=\"#\" onclick=\"moveCompoundRow(compound_"+compound.id+", this.parentNode.parentNode, false); return false\">";
  buttons+= "<img alt=\"Move down\" title=\"Move down\" src=\""+CONTENT+"/icons/down-purple.gif\">";
  buttons+= "</a>";
  buttons+= "<a href=\"#\" onclick=\"deleteCompoundRow(compound_"+compound.id+", this.parentNode.parentNode); return false\">";
  buttons+= "<img alt=\"Delete row\" title=\"Delete row\" src=\""+CONTENT+"/icons/delete-page-purple.gif\">";
  buttons+= "</a>";
	
	cell.innerHTML = buttons;
}

function switchCompoundRows(compound, row1, row2)
{
  for (var i in compound.fields)
  {
    switchCompoundField(compound.fields[i], compound.id, i, row1, row2);
  }
}

function deleteCompoundRow(compound, row)
{
	var body = row.parentNode;
	var pos = row.sectionRowIndex;
	var rows = body.rows.length;
	if (pos != (rows-1))
		switchCompoundRows(compound, pos, rows-1);
	
	row = body.rows[rows-1];
	body.removeChild(row);
}

function moveCompoundRow(compound, row, moveup)
{
	var body = row.parentNode;
	var pos1 = row.sectionRowIndex;
	var pos2;
	if (moveup)
		pos2 = pos1-1;
	else
		pos2 = pos1+1;
	
	if ((pos2>=0) && (pos2<body.rows.length))
	{
		switchCompoundRows(compound, pos1, pos2);
	}
}

// Calendar specific code

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
	calendar.onSelect = function() { selectDate(calendar, id) };
	calendar.render();
	return calendar;
}

// Filebrowser specific code

function showFileBrowser(id, url) {
	window.SetUrl = function(uri) { fileBrowserSetUrl(id, uri); };
  window.open(url,'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=630,height=400');
}

function fileBrowserSetUrl(id, url) {
	var field = document.getElementById(id);
	field.value = url;
	
	var pos = url.lastIndexOf("/");
	if (pos>=0)
		url = url.substring(pos+1);
		
	var fake = document.getElementById("fbfake-"+id);
	fake.value = url;
}

function clearFileBrowser(id) {
	var field = document.getElementById(id);
	field.value = "";
	
	var fake = document.getElementById("fbfake-"+id);
	fake.value = "[Nothing selected]";
}
