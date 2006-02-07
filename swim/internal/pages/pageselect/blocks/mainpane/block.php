<?
if (isset($request->query['page']))
{
  $page=$request->query['page'];
}
else
{
  $page=$_PREFS->getPref('method.view.defaultresource');
}
?>
<script>
function submit()
{
  window.opener.tinyMCE.insertLink('/<?= $page ?>');
  window.close();
}
</script>
<div class="header">
<button onclick="submit()">Select</button>
<button onclick="window.close()">Cancel</button>
<h2>Preview</h2>
</div>
<block id="" class="body" src="<?= $page ?>/block/content"/>
