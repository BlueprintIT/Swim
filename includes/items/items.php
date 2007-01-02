<?

/*
 * Swim
 *
 * Includes for items.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

require $_PREFS->getPref('storage.includes').'/items/item.php';
require $_PREFS->getPref('storage.includes').'/items/field.php';
require $_PREFS->getPref('storage.includes').'/items/simplefields.php';

if ($_PREFS->getPref('admin.htmleditor')=='fckeditor')
  require $_PREFS->getPref('storage.includes').'/items/fckeditor.php';
else if ($_PREFS->getPref('admin.htmleditor')=='tinymce')
  require $_PREFS->getPref('storage.includes').'/items/tinymce.php';

require $_PREFS->getPref('storage.includes').'/items/sequence.php';
require $_PREFS->getPref('storage.includes').'/items/class.php';
require $_PREFS->getPref('storage.includes').'/items/section.php';
  
class ArchiveAdminSection extends AdminSection
{
  public function getIcon()
  {
    global $_PREFS;
    
    return $_PREFS->getPref('url.admin.static').'/icons/bin.gif';
  }
  
  public function getName()
  {
    return "Recycle Bin";
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_CONTENT;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('items/archive.tpl');
    return $request->encode();
  }
  
  public function isAvailable()
  {
    global $_USER;
    
    return $_USER->hasPermission('documents',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,17)=='items/archive.tpl'))
      return true;
    return false;
  }
}

AdminManager::addSection(new ArchiveAdminSection());

?>