<?

/*
 * Swim
 *
 * Some standard fields
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class SimpleField extends Field
{
  protected $value = '';
  protected $basefield = 'base';
  protected $pos = 0;

  public function setBaseField($name)
  {
    $this->basefield = $name;
  }
  
  public function setPosition($pos)
  {
    $this->pos = $pos;
  }
    
  protected function retrieve()
  {
    global $_STORAGE;
    
    if ($this->retrieved)
      return;
      
    $results = $_STORAGE->query('SELECT '.$this->getColumn().' FROM Field WHERE itemversion='.$this->itemversion->getId().' AND basefield="'.$_STORAGE->escape($this->basefield).'" AND pos='.$this->pos.' AND field="'.$_STORAGE->escape($this->id).'";');
    if ($results->valid())
    {
      $this->exists = true;
      $this->value = $results->fetchSingle();
    }
    else
    {
      $this->value = $this->getDefaultValue();
    }
    $this->retrieved = true;
  }
  
  public function setValue($value)
  {
    global $_STORAGE;
    
    if ($this->isEditable())
    {
      $col = $this->getColumn();
      if ($_STORAGE->query('REPLACE INTO Field (itemversion,basefield,pos,field,'.$col.') VALUES ('.$this->itemversion->getId().',"'.$_STORAGE->escape($this->basefield).'",'.$this->pos.',"'.$_STORAGE->escape($this->id).'",'.$this->escapeValue($value).');'))
      {
        $this->value = $value;
        $this->exists = true;
        $this->retrieved = true;
        $this->itemversion->updateModified();
      }
    }
  }
  
  protected function getFieldName()
  {
    if ($this->basefield == 'base')
      return $this->id;
    else
      return $this->basefield.'['.$this->pos.'].'.$this->id;
  }
  
  protected function getFieldId()
  {
    if ($this->basefield == 'base')
      return 'field_'.$this->id;
    else
      return 'field_'.$this->basefield.'_'.$this->pos.'_'.$this->id;
  }
  
  public function getEditor()
  {
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    return '<input '.$state.'style="width: 100%" type="input" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" value="'.$this->toString().'">';
  }
  
  protected function getDefaultValue()
  {
    return '';
  }
  
  public function getPlainText()
  {
    return $this->toString();
  }
  
  public function toString()
  {
    $this->retrieve();
    return $this->value;
  }
  
  protected function escapeValue($value)
  {
    return $value;
  }
  
  protected function getColumn()
  {
    return "";
  }
}

class IntegerField extends SimpleField
{
  public function compareTo($b)
  {
    if ($b instanceof IntegerField)
      return $this->toString()-$b->toString();
    return 0;
  }
  
  protected function escapeValue($value)
  {
    if (is_numeric($value))
      return $value;
    $this->log->warn('Invalid value to escape: '.$value);
    return '0';
  }
  
  protected function getColumn()
  {
    return "intValue";
  }
}

class TextField extends SimpleField
{
  protected $stylesheet;
  protected $styles;
  
  public function getEditor()
  {
    global $_PREFS;
    
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    if ($this->type == 'multiline')
      return '<textarea '.$state.'style="width: 100%; height: 100px;" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'">'.htmlentities($this->toString()).'</textarea>';
    else if ($this->type == 'html')
    {
      if (!$this->isEditable())
        return '<div id="'.$this->id.'">'.$this->toString().'</div>';
      else
      {
        recursiveMkDir($this->itemversion->getStoragePath());
        include_once($_PREFS->getPref('storage.fckeditor').'/fckeditor.php');
        $editor = new FCKeditor($this->getFieldName()) ;
        $editor->BasePath = $_PREFS->getPref('url.fckeditor');
        $value = $this->toString();
        if (strlen($value)==0)
          $value = "<p><br/>\n</p>";
        $editor->Value = $value;
        $editor->Width  = '100%';
        $editor->Height = '400px';
        $editor->Config['SkinPath'] = $editor->BasePath.'editor/skins/office2003/';
        if (isset($this->styles))
          $editor->Config['StylesXmlPath'] = $_PREFS->getPref('url.site.static').'/'.$this->styles;
        /*if (isset($this->stylesheet))
        {
          $request = new Request();
          $request->setQueryVar('CONTEXT', 'body');
          $request->setMethod('layout');
          $request->setPath($this->stylesheet);
          $editor->Config['EditorAreaCSS'] = $request->encode();
        }*/
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
    else
      return parent::getEditor();
  }
  
  public function getPlainText()
  {
    $text = $this->toString();
    if ($this->type == 'html')
      $text = html_entity_decode(strip_tags($text));

    return $text;
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('stylesheet'))
      $this->stylesheet = $element->getAttribute('stylesheet');
    if ($element->hasAttribute('styles'))
      $this->styles = $element->getAttribute('styles');
  }
  
  public function copyFrom($item)
  {
    parent::copyFrom($item);
    if ($this->type == 'html')
    {
      $this->retrieve();
      $newvalue = str_replace($item->getStorageUrl(), $this->itemversion->getStorageUrl(), $this->value);
      if ($newvalue != $this->value)
        $this->setValue($newvalue);
    }
  }
  
  protected function escapeValue($value)
  {
    global $_STORAGE;
    return '"'.$_STORAGE->escape($value).'"';
  }
  
  public function compareTo($b)
  {
    if ($b instanceof TextField)
      return strcmp($this->toString(), $b->toString());
    return 0;
  }
  
  public function output(&$smarty)
  {
    if ($this->type == 'html')
    {
      if (isset($this->stylesheet))
      {
        $request = new Request();
        $request->setQueryVar('CONTEXT', 'div#field_content');
        $request->setMethod('layout');
        $request->setPath($this->stylesheet);
        $head = $smarty->get_registered_object('HEAD');
        $head->addStyleSheet($request->encode());
      }
      return '<div id="field_content" class="content">'.$this->toString().'</div>';
    }
    else
      return $this->toString();
  }
  
  public function toString()
  {
    $result = parent::toString();
    if ($this->type =='html')
    {
      $result = str_replace('<br />', '<br>', $result);
    }
    return $result;
  }
  
  protected function getColumn()
  {
    return "textValue";
  }
}

class DateField extends IntegerField
{
  public function compareTo($b)
  {
    if ($b instanceof DateField)
      return $this->toString()-$b->toString();
    return 0;
  }
  
  public function getEditor()
  {
    $text = '';
    $text.='<input type="hidden" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" value="'.$this->toString().'">';
    $text.='<div id="calendar_'.$this->getFieldId().'"></div>'."\n";
    $text.='<script type="text/javascript">var cal_'.$this->getFieldId().' = displayCalendar("'.$this->getFieldId().'",'.$this->toString().');</script>'."\n";
    return $text;
  }
  
  public function getPlainText()
  {
    return date('l Y F j n', $this->toString());
  }
  
  public function output(&$smarty)
  {
    return date('d/m/Y', $this->toString());
  }
  
  protected function getDefaultValue()
  {
    return time();
  }
  
  protected function getColumn()
  {
    return "dateValue";
  }
}

class ItemField extends IntegerField
{
  protected $item;
  
  protected function retrieve()
  {
    if ($this->retrieved)
      return;
      
    parent::retrieve();
    $this->item = Item::getItem($this->value);
  }
  
  public function isIndexed()
  {
    return false;
  }
  
  public function getItem()
  {
    $this->retrieve();
    
    return $this->item();
  }
  
  public function toString()
  {
    $this->retrieve();
    
  }
  
  public function getEditor()
  {
    $this->retrieve();
    
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('browser/filebrowser.tpl');
    $request->setQueryVar('type', 'item');

    if ($this->item !== null)
    {
      $rlvalue = $this->toString();
    }
    else
      $rlvalue = '[Nothing selected]';

    echo '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$this->value.'"> ';

    echo '<input id="fbfake-'.$this->getFieldId().'" disabled="true" type="text" value="'.$rlvalue.'"> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:showFileBrowser(\''.$this->getFieldId().'\',\''.$request->encode().'\')">Select...</a>';
    echo '</div> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:clearFileBrowser(\''.$this->getFieldId().'\')">Clear</a>';
    echo '</div> ';
  }
}

class FileField extends TextField
{
  private $filetype;
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('filetype'))
      $this->filetype = $element->getAttribute('filetype');
  }
  
  public function isIndexed()
  {
    return false;
  }
  
  public function copyFrom($item)
  {
    parent::copyFrom($item);
    $this->retrieve();
    $newvalue = str_replace($item->getStorageUrl(), $this->itemversion->getStorageUrl(), $this->value);
    if ($newvalue != $this->value)
      $this->setValue($newvalue);
  }
  
  public function getEditor()
  {
    $this->retrieve();
    
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('browser/filebrowser.tpl');
    $request->setQueryVar('item', $this->itemversion->getItem()->getId());
    $request->setQueryVar('variant', $this->itemversion->getVariant()->getVariant());
    $request->setQueryVar('version', $this->itemversion->getVersion());
    $request->setQueryVar('type', $this->filetype);

    if (strlen($this->value)>0)
    {
      $rlvalue = $this->value;
      $pos = strrpos($rlvalue, '/');
      if ($pos!==false)
        $rlvalue = substr($rlvalue, $pos+1);
    }
    else
      $rlvalue = '[Nothing selected]';

    echo '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$this->value.'"> ';

    echo '<input id="fbfake-'.$this->getFieldId().'" disabled="true" type="text" value="'.$rlvalue.'"> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:showFileBrowser(\''.$this->getFieldId().'\',\''.$request->encode().'\')">Select...</a>';
    echo '</div> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:clearFileBrowser(\''.$this->getFieldId().'\')">Clear</a>';
    echo '</div> ';
  }
}

?>