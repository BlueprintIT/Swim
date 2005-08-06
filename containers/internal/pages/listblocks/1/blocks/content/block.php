<?
	$blocks=array();
	$allblocks=&getAllBlocks();
	foreach(array_keys($allblocks) as $id)
	{
		$block=&$allblocks[$id];
		$title=$block->prefs->getPref('block.title');
		$format=$block->prefs->getPref('block.format');
		$blocks[$title]=&$block;
	}
	ksort($blocks);
?>
<script>
function displaypreview()
{
	var block = document.getElementById("block");
	var version = document.getElementById("version");
	var preview = document.getElementById("preview");
	preview.src='/Swim/swim.php/preview/'+block.value;
}

function blockselect()
{
	var version = document.getElementById("version");
	version.disabled=false;
	displaypreview();
}

function versionselect()
{
	displaypreview();
}
</script>
<table>
	<tr>
		<th><label for="block">Section</label></th>
		<th><label for="version">Version</label></th>
		<th>Preview</th>
	</tr>
	<tr>
		<td style="vertical-align: top">
			<select name="block" id="block" size="10" onchange="blockselect()">
<?
	foreach(array_keys($blocks) as $title)
	{
		$block=&$blocks[$title];
		$format=$block->prefs->getPref('block.format');
?>
				<option value="<?= $block->getPath() ?>"><?= $title ?> (<?= $format ?>)</option>
<?
	}
?>
			</select>
		</td>
		<td style="vertical-align: top">
			<select name="version" id="version" size="10" disabled="true" onchange="versionselect()">
			</select>
		</td>
		<td style="vertical-align: top">
			<iframe id="preview" width="500" height="300"></iframe>
		</td>
	</tr>
</table>
