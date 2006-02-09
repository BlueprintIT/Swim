<?

/*
 * Swim
 *
 * Defines a block that allows file selection in a given directory.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FileSelectorBlock extends FileManagerBlock
{
	function FileSelectorBlock($container,$id,$version)
	{
		$this->FileManagerBlock($container,$id,$version);
	}

	function displayTableHeader()
	{
?>
	<th>Select</th>
<?
		parent::displayTableHeader();
	}

	function displayTableFooter()
	{
		parent::displayTableFooter();
?>
<tr><td colspan="5" style="text-align: center">
<button onclick="select()">Select</button>
<button onclick="cancel()">Cancel</button>
</td></tr>
<tr><td colspan="5" style="text-align: center"><hr></td></td>
<?
	}
	
	function displayFileDetails($request,$resource,$description,$delete)
	{
    $size=getReadableFileSize($resource->getDir().'/'.$resource->id);
		$path=$resource->getPath();
?>
<td><input type="radio" onclick="moveSelection(this)" onchange="moveSelection(this)" id="<?= $path ?>" name="file" value="/<?= $path ?>"></td>
<td><anchor target="_blank" method="view" href="/<?= $resource->getPath() ?>"><?= basename($resource->id) ?></anchor></td>
<td><label style="display: block; width: 100%" for="<?= $path ?>"><?= $description ?></label></td>
<td><label style="display: block; width: 100%" for="<?= $path ?>"><?= $size ?></label></td>
<td><a href="<?= $delete->encode() ?>">Delete</a></td>
<?
	}
	
	function displayContent($parser,$attrs,$text)
	{
		$request=$parser->data['request'];
		$id=$parser->data['blockid'];
?>
<script>

var lastselected = null;

function moveSelection(input)
{
  if (lastselected)
  {
    lastselected.parentNode.parentNode.className='';
  }
  input.parentNode.parentNode.className='selected';
  lastselected=input;
}

function select()
{
  if (lastselected)
  {
    var selected=lastselected.value;
		window.opener.<?= $request->query['action'] ?>;
    window.close();
	}
  else
  {
    alert('You must select a file.');
  }
}

function cancel()
{
	window.close();
}

</script>
<?
		parent::displayContent($parser,$attrs,$text);
	}
}

?>