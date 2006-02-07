<?
$page = new Request($request);
$page->query['type']='page';
$global = new Request($request);
$global->query['type']='global';
$resource = Resource::decodeResource($request);
?>
<tr>
<?
if ($resource!==false)
{
?>
  <td class="spacer"></td>
  <td<?
if ((!isset($request->query['type']))||($request->query['type']!='global'))
{
  print(' class="selected"');
}
?>><a href="<?= $page->encode() ?>">Page Files</a></td>
<?
}
?>
  <td class="spacer"></td>
  <td<?
if (($resource===false)||(isset($request->query['type']))&&($request->query['type']=='global'))
{
  print(' class="selected"');
}
?>><a href="<?= $global->encode() ?>">Global Files</a></td>
  <td class="remainder"></td>
</tr>
