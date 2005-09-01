<script src="/global/file/scripts/cbdom.js"/>
<script>
var pageurl=[];
var pagepreview=[];
<?

$pageset=array();
$pages=&getAllPages();
foreach (array_keys($pages) as $key)
{
	$page=&$pages[$key];
	$title=$page->prefs->getPref('page.variables.title');
	$pageset[$key]=$title;
	$req = new Request();
	$req->method='preview';
	$req->resource=$page->getPath();
	print("pagepreview[".$key."]='".$req->encode()."';\n");
	print("pageurl[".$key."]='/".$page->getPath()."';\n");
}
natsort($pageset);
?>

function submit()
{
	var select = document.getElementById("page");
	if (select.selectedIndex>=0)
	{
		window.opener.tinyMCE.insertLink(pageurl[select.value],"");
	}
	window.close();
}

function pageselect()
{
	var select = document.getElementById("page");
	var preview = document.getElementById("preview");
	if (select.selectedIndex>=0)
	{
		preview.src=pagepreview[select.value];
	}
	else
	{
		preview.src="";
	}
}

addEvent(window,"load",pageselect,false);
</script>
<table style="width: 100%; height: 100%">
<tr>
<th>Page</th>
<th>Preview</th>
</tr>
<tr>
<td style="width: 25%; vertical-align: top">
<select id="page" name="page" multiple="multiple" style="width: 100%; height: 100%" onchange="pageselect()">
<?
foreach ($pageset as $key => $title)
{
?>
<option value="<?= $key ?>"><?= $title ?></option>
<?
}

?>
</select>
</td>
<td style="vertical-align: top">
<iframe id="preview" height="100%" width="100%" style="border: 1px solid black; width: 100%; height: 100%"></iframe>
</td>
</tr>
<tr>
<td colspan="2" style="text-align: center">
<button onclick="submit()">Select</button> <button onclick="window.close()">Cancel</button>
</td>
</tr>
</table>
