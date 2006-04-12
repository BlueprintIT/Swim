function showFileBrowser(url) {
  window.open(url,'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=630,height=400');
}

function fileBrowserCallback(id, selected) {
	var field = document.getElementById(id);
	field.value = selected;
	
	var pos = selected.lastIndexOf("/");
	if (pos>=0)
		selected = selected.substring(pos+1);
		
	var fake = document.getElementById("fbfake-"+id);
	fake.value = selected;
}
