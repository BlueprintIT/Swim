<?php

/*
 * Swim
 *
 * HTMLField using FCKeditor as the editor
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class HTMLField extends BaseHTMLField
{
  public function getEditor(&$request, &$smarty)
  {
    global $_PREFS;
    
    if (!$this->isEditable())
      return '<div id="'.$this->id.'">'.$this->getPassedValue($request).'</div>';
    else
    {
      recursiveMkDir($this->itemversion->getStoragePath());
      include_once($_PREFS->getPref('storage.fckeditor').'/fckeditor.php');
      $editor = new FCKeditor($this->getFieldName()) ;
      $editor->BasePath = $_PREFS->getPref('url.fckeditor');
      $value = $this->getPassedValue($request);
      if (strlen($value)==0)
        $value = "<p><br/>\n</p>";
      $editor->Value = $value;
      $editor->Width  = '100%';
      $editor->Height = '400px';
      $editor->Config['SkinPath'] = $editor->BasePath.'editor/skins/office2003/';
      if (isset($this->styles))
        $editor->Config['StylesXmlPath'] = $_PREFS->getPref('url.site.static').'/'.$this->styles;
      if (isset($this->stylesheet))
      {
        $request = new Request();
        $request->setQueryVar('CONTEXT', 'body');
        $request->setMethod('layout');
        $request->setPath($this->stylesheet);
        $editor->Config['EditorAreaCSS'] = $request->encode();
      }
      $request = new Request();
      $request->setMethod('admin');
      $request->setPath('browser/filebrowser.tpl');
      $request->setQueryVar('item', $this->itemversion->getItem()->getId());
      $request->setQueryVar('variant', $this->itemversion->getVariant()->getVariant());
      $request->setQueryVar('version', $this->itemversion->getVersion());
      $request->setQueryVar('type', 'link');
      $editor->Config['LinkBrowserURL'] = $request->encode();
      $request->setQueryVar('type', 'image');
      $editor->Config['ImageBrowserURL'] = $request->encode();
      $request->setQueryVar('type', 'flash');
      $editor->Config['FlashBrowserURL'] = $request->encode();
      $editor->Config['CustomConfigurationsPath'] = $_PREFS->getPref('url.admin.static').'/scripts/fckeditor.js';
      $editor->ToolbarSet = 'Swim';
      return $editor->CreateHtml();
    }
  }
}

?>
