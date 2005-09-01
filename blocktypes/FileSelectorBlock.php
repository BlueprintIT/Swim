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
	
	function displayFileDetails(&$request,&$resource,$description,&$delete)
	{
		$path=$resource->getPath();
		$path=substr($path,strlen($request->query['baseurl']));
?>
	<td><input type="radio" name="file" value="<?= $path ?>"></td>
<?
		parent::displayFileDetails($request,$resource,$description,$delete);
	}
	
	function displayContent(&$parser,$attrs,$text)
	{
		$request=&$parser->data['request'];
		$id=$parser->data['blockid'];
?>
<script>

function select()
{
	var inputs = document.getElementsByTagName("INPUT");
	for (var i=0; i<inputs.length; i++)
	{
		if ((inputs[i].getAttribute("type")=="radio")&&(inputs[i].checked))
		{
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
	}
}

?>