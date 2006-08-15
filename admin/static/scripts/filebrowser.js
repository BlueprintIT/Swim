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
