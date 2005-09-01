<?

/*
 * Swim
 *
 * Defines a block that allows file selection in a given directory.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FileSelectorBlock extends FileManagerBlock
{
	function FileSelectorBlock(&$container,$id,$version)
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
	
	function displayFileDetails(&$resource,$description,&$delete)
	{
?>
	<td><input type="radio" name="file" value="<?= $resource->getPath() ?>"></td>
<?
		parent::displayFileDetails($resource,$description,$delete);
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
?>
<script>

function select()
{
	var inputs = document.getElementsByTagName("INPUT");
	for (var i=0; i<inputs.length; i++)
	{
		if ((inputs[i].getAttribute("type")=="radio")&&(inputs[i].checked))
		{
			alert(window.targetField);
			window.opener.document.getElementById(window.targetField).value=inputs[i].value;
		}
	}
	window.close();
}

function cancel()
{
	window.close();
}

</script>
<?
		parent::displayContent($parser,$attrs,$text);
?>
<button onclick="select()">Select</button> <button onclick="cancel()">Cancel</button>
<?
	}
}

?>