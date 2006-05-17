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
  display_tab($type,'cart',$prefs->getPref('url.base').'/onlinestore/admin/index.php','E-commerce');
}

if (true)
{
?>
  <td class="spacer"></td>
<?
  display_tab($type,'cart','https://ukvps.protx.com/vspadmin/','Protx VSP Admin');
}

if ($_USER->hasPermission('users',PERMISSION_READ))
{
?>
  <td class="spacer"></td>
<?
  display_tab($type,'users',$users->encode(),'User management');
}
?>
  <td class="spacer"></td>
<?
  display_tab($type,'stats','/stats','Website statistics');
?>
  <td class="remainder"></td>
</tr>
