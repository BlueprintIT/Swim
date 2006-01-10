<script>
function imageBrowser(element)
{
  window.open("<?= $browser->encode() ?>&action=document.getElementById('"+element+"').value=selected",'swimbrowser','modal=1,status=0,menubar=0,directories=0,location=0,toolbar=0,width=630,height=400');
}
</script>
<?

function block_image($id,$block,$layout)
{
?>
  <td style="vertical-align: top"><input id="<?= $id ?>" name="block:<?= $id ?>:pref:block.image.src" type="text" value="<?
if ($block->prefs->isPrefSet('block.image.src'))
{
  print($block->prefs->getPref('block.image.src'));
}
else
{
  print('[No image selected]');
}
?>"> <button onclick="imageBrowser('<?= $id ?>')">Select...</button></td>
  <td style="vertical-align: top"><?= $layout->getDescription(); ?></td>
<?
}

?>
