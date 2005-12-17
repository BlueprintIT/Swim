<?

$admin = new Request();
$admin->method='admin';

$users = new Request();
$users->method='users';

$page = $parser->data['page'];
$type = $page->prefs->getPref('page.admin.section');

function display_tab($type,$section,$url,$title)
{
  if ($type==$section)
  {
?>
  <td class="selected"><?= $title ?></td>
<?
  }
  else
  {
?>
  <td><a href="<?= $url ?>"><?= $title; ?></a></td>
<?
  }
}

?>
<tr>
<?
if ($_USER->hasPermission('documents',PERMISSION_READ))
{
?>
  <td class="spacer"></td>
<?
  display_tab($type,'content',$admin->encode(),'Page management');
}

if (true)
{
?>
  <td class="spacer"></td>
<?
  display_tab($type,'cart',$prefs->getPref('url.base').'/cart/admin','E-commerce');
}

if ($_USER->hasPermission('users',PERMISSION_READ))
{
?>
  <td class="spacer"></td>
<?
  display_tab($type,'users',$users->encode(),'User management');
}
?>
  <td class="remainder"></td>
</tr>