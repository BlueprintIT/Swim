<?
function block_image($id,$page,$block,$layout)
{
?>
  <td class="details"><filebrowser name="block:<?= $id ?>:pref:block.image.src" page="<?= $page->getPath() ?>" version="<?= $page->version ?>" value="<?= $block->prefs->getPref('block.image.src') ?>"/></td>
  <td class="description"><?= $layout->getDescription(); ?></td>
<?
}
?>
