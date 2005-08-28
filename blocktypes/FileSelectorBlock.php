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
}

?>