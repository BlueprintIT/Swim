<?
$page = new Request($request);
$page->query['type']='page';
$global = new Request($request);
$global->query['type']='global';
$resource = $request->resource;
?>
<tr>
<?
if ($resource!==null)
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
if (($resource===null)||(isset($request->query['type']))&&($request->query['type']=='global'))
{
  print(' class="selected"');
}
?>><a href="<?= $global->encode() ?>">Global Files</a></td>
  <td class="remainder"></td>
</tr>