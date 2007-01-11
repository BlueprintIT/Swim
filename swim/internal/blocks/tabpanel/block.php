<?

function display_tab($title,$url,$selected)
{
  if ($selected)
  {
?>
  <td class="selected"><?= $title ?></td>
<?
  }
  else
  {
?>
  <td><a href="<?= $url ?>"><?= $title ?></a></td>
<?
  }
}

?>
<tr>
<?
foreach (AdminManager::$sections as $section)
{
  if ($section->isAvailable())
  {
?>
  <td class="spacer"></td>
<?
    $url = $section->getURL();
    display_tab($section->getName(), $url, $section->isSelected($request));
  }
}
?>
  <td class="remainder"></td>
</tr>
