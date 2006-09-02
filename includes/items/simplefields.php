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
  
  public function getClientAttributes()
  {
    return array('type' => $this->type);
  }

  protected function getPassedValue($request)
  {
    if ($this->basefield == 'base')
    {
      if ($request->hasQueryVar($this->id))
        return $request->getQueryVar($this->id);
    }
    else
    {
      if ($request->hasQueryVar($this->basefield))
      {
        $passed = $request->getQueryVar($this->basefield);
        if ((is_array($passed)) && (isset($passed[$this->pos])))
        {
          $passed = $passed[$this->pos];
          if ((is_array($passed)) && (isset($passed[$this->id])))
            return $passed[$this->id];
        }
      }
    }
    return $this->toString();
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
  
  public function getEditor(&$request, &$smarty)
  {
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    return '<input '.$state.'type="text" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" value="'.$this->getPassedValue($request).'">';
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
  public function getEditor(&$request, &$smarty)
  {
    global $_PREFS;
    
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    if ($this->type == 'multiline')
      return '<textarea '.$state.'style="width: 100%; height: 100px;" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'">'.htmlentities($this->getPassedValue($request)).'</textarea>';
    else
      return parent::getEditor($request, $smarty);
  }
  
  public function getPlainText()
  {
    return $this->toString();
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
  
  public function output(&$request, &$smarty)
  {
    return $this->toString();
  }
  
  protected function getColumn()
  {
    return "textValue";
  }
}

class BaseHTMLField extends TextField
{
  protected $stylesheet;
  protected $styles;
  
  public function setValue($value)
  {
    $value = str_replace('href="http://'.$_SERVER['HTTP_HOST'].'/', 'href="/', $value);
    $value = str_replace('src="http://'.$_SERVER['HTTP_HOST'].'/', 'src="/', $value);
    parent::setValue($value);
  }
  
  public function getPlainText()
  {
    $text = $this->toString();
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
    $this->retrieve();
    $newvalue = str_replace($item->getStorageUrl(), $this->itemversion->getStorageUrl(), $this->value);
    if ($newvalue != $this->value)
      $this->setValue($newvalue);
  }
  
  public function output(&$request, &$smarty)
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
  
  public function toString()
  {
    $result = parent::toString();
    $result = str_replace('<br />', '<br>', $result);
    return $result;
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
  
  public function getEditor(&$request, &$smarty)
  {
    $text = '';
    $text.='<input type="hidden" id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" value="'.$this->getPassedValue($request).'">';
    $text.='<div id="calendar_'.$this->getFieldId().'"></div>'."\n";
    $text.='<script type="text/javascript">var cal_'.$this->getFieldId().' = displayCalendar("'.$this->getFieldId().'",'.$this->getPassedValue($request).');</script>'."\n";
    return $text;
  }
  
  public function getPlainText()
  {
    return date('l Y F j n', $this->toString());
  }
  
  public function output(&$request, &$smarty)
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
    if ($this->value == -1)
      $this->item = null;
    else
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
    if ($this->item !== null)
      return $this->item->getId();
    else
      return -1;
  }
  
  public function getEditor(&$request, &$smarty)
  {
    $this->retrieve();
    
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('browser/filebrowser.tpl');
    $request->setQueryVar('type', 'item');

    $value = $this->getPassedValue($request);
    if ($value>=0)
    {
      $rlvalue = $value;
    }
    else
      $rlvalue = '[Nothing selected]';

    echo '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$value.'"> ';

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
  
  public function getClientAttributes()
  {
    $attrs = parent::getClientAttributes();
    
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('browser/filebrowser.tpl');
    $request->setQueryVar('item', $this->itemversion->getItem()->getId());
    $request->setQueryVar('variant', $this->itemversion->getVariant()->getVariant());
    $request->setQueryVar('version', $this->itemversion->getVersion());
    $request->setQueryVar('type', $this->filetype);
    
    $attrs['request'] = $request->encode();
    
    return $attrs;
  }
  
  public function getEditor(&$request, &$smarty)
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('browser/filebrowser.tpl');
    $request->setQueryVar('item', $this->itemversion->getItem()->getId());
    $request->setQueryVar('variant', $this->itemversion->getVariant()->getVariant());
    $request->setQueryVar('version', $this->itemversion->getVersion());
    $request->setQueryVar('type', $this->filetype);

    $value = $this->getPassedValue($request);
    if (strlen($value)>0)
    {
      $rlvalue = $value;
      $pos = strrpos($rlvalue, '/');
      if ($pos!==false)
        $rlvalue = substr($rlvalue, $pos+1);
    }
    else
      $rlvalue = '[Nothing selected]';

    echo '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$value.'"> ';

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