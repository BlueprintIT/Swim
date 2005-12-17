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
  <td class="spacer"></td>
<? display_tab($type,'content',$admin->encode(),'Page management'); ?>
  <td class="spacer"></td>
<? display_tab($type,'cart',$prefs->getPref('url.base').'/cart/admin','E-commerce'); ?>
  <td class="spacer"></td>
<? display_tab($type,'users',$users->encode(),'User management'); ?>
  <td class="remainder"></td>
</tr>
