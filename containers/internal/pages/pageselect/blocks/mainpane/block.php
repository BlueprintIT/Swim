<script>
function submit()
{
  window.opener.tinyMCE.insertLink('/<?= $request->query['page'] ?>');
  window.close();
}
</script>
<div class="header">
<button onclick="submit()">Select</button>
<button onclick="window.close()">Cancel</button>
<h2>Preview</h2>
</div>
<block id="" class="body" src="<?= $request->query['page'] ?>/block/content"/>
