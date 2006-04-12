<?
function block_image($id,$page,$block,$layout)
{
?>
  <td style="vertical-align: top"><filebrowser name="block:<?= $id ?>:pref:block.image.src" page="<?= $page->getPath() ?>" version="<?= $page->version ?>" value="<?= $block->prefs->getPref('block.image.src') ?>"/></td>
  <td style="vertical-align: top"><?= $layout->getDescription(); ?></td>
<?
}
?>
