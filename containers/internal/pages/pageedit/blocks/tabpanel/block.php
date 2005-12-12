<?

$admin = new Request();
$admin->method='admin';

?>
<tr>
  <td class="spacer"></td>
  <td class="selected"><a href="<?= $admin->encode() ?>">Page management</a></td>
  <td class="spacer"></td>
  <td>E-commerce</td>
  <td class="spacer"></td>
  <td>User management</td>
  <td class="remainder"></td>
</tr>
