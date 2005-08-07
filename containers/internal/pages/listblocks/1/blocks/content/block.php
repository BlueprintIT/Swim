<?
	$setblock = new Request();
	$setblock->method='setblock';
	$setblock->resource=$request->nested->resource;
	if (isset($request->nested->query['version']))
	{
		$setblock->query['version']=$request->nested->query['version'];
	}
	$setblock->nested=&$request->nested;
	$page=&Resource::decodeResource($request->nested);
	if (isset($request->query['format']))
	{
		$format=$request->query['format'];
	}
	$blocks=array();
	$allblocks=&getAllBlocks();
	$current=$page->getReferencedBlock($request->query['reference']);
	foreach(array_keys($allblocks) as $id)
	{
		$block=&$allblocks[$id];
		$title=$block->prefs->getPref('block.title');
		if ((isset($format))&&($format!=$block->prefs->getPref('block.format')))
			continue;
		$blocks[$title]=&$block;
	}
	ksort($blocks);
?>
<script>
var blockurl=[];
<?
	$pos=0;
	foreach(array_keys($blocks) as $title)
	{
		$block=&$blocks[$title];
		$req = new Request();
		$req->method='preview';
		$req->resource=$block->getPath();
		print('blockurl['.$pos.']="'.$req->encode().'";'."\n");
		$pos++;
	}
?>
function displaypreview()
{
	var block = document.getElementById("block");
	var preview = document.getElementById("preview");
	if (block.selectedIndex>=0)
	{
		preview.src=blockurl[block.selectedIndex];
	}
	else
	{
		preview.src="";
	}
}

function blockselect()
{
	displaypreview();
}

addEvent(window,"load",displaypreview,false);

</script>
<form action="<?= $setblock->encodePath() ?>" method="POST">
<?= $setblock->getFormVars() ?>
<input type="hidden" name="reference" value="<?= $request->query['reference'] ?>">
<table>
	<tr>
		<th><label for="block">Section</label></th>
		<th>Preview</th>
	</tr>
	<tr>
		<td style="vertical-align: top">
			<select name="block" id="block" size="10" onchange="blockselect()">
<?
	foreach(array_keys($blocks) as $title)
	{
		$block=&$blocks[$title];
		$form=$block->prefs->getPref('block.format');
?>
				<option value="<?= $block->getPath() ?>"<?
		if (($current!==false)&&($block->getPath()==$current->getPath()))
		{
			print(' selected="selected"');
		}
?>><?= $title ?> (<?= $form ?>)</option>
<?
	}
?>
			</select>
		</td>
		<td rowspan="2" style="vertical-align: top">
			<iframe id="preview" width="500" height="300"></iframe>
		</td>
	</tr>
	<tr>
		<td style="text-align: center"><input type="submit" value="Use this section"/></td>
	</tr>
</table>
</form>
<hr>
<?

if (isset($format))
{
	$create = new Request();
	$create->method='docreate';
	$create->resource='global/block';
	$create->nested=&$request;

?>
<form method="POST" action="<?= $create->encodePath() ?>">
<?= $create->getFormVars() ?>
<input type="hidden" name="layout" value="<?= $format ?>">
<p>Create a new section:</p>
<p>Title: <input type="text" name="block.title"> <input type="submit" value="Create"></p>
</form>
<?
}
?>
