<?php

/*
 * Swim
 *
 * HTMLField using TinyMCE as the editor
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
      $head = $smarty->get_registered_object('HEAD');
      $head->addScript($_PREFS->getPref('url.tinymce').'/jscripts/tiny_mce/tiny_mce_gzip.php');
      $head->addScript($_PREFS->getPref('url.admin.static').'/scripts/tinymce.js');
      $result = '<textarea class="HTMLEditor" style="width: 100%; height: 400px;" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'">'.$this->getPassedValue($request).'</textarea>';
      $result.= "\n".'<script type="text/javascript">'."\n";
      if (isset($this->stylesheet))
      {
        $request = new Request();
        $request->setQueryVar('CONTEXT', '.'.$this->getFieldName());
        $request->setMethod('layout');
        $request->setPath($this->stylesheet);
        $result.= 'tinyMCEparams.content_css+=",'.$request->encode().'"';
      }
      $result.='</script>';
      return $result;
    }
  }
}

?>
