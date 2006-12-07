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
    if (is_numeric($b))
    	return $this->toString()-$b;
    $this->log->errortrace('Unable to compare to value: '.$b);
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
    if (is_string($b))
    	return strcmp($this->toString(), $b);
    $this->log->errortrace('Unable to compare to value: '.$b);
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
    parent::parseAttributes($element);
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

class OptionField extends IntegerField
{
  protected $option;
  
  public function getClientAttributes()
  {
    $attrs = parent::getClientAttributes();
    $result = array();
    $optionset = FieldSetManager::getOptionSet($this->id);
    $options = $optionset->getOptions();
    if (count($options)>0)
    {
      foreach ($options as $id => $option)
      {
        $result[$id] = $option->getValue();
      }
    }
    $attrs['options'] = $result;
    return $attrs;
  }
  
  public function getEditor(&$request, &$smarty)
  {
    $this->retrieve();
    $text = '';
    $text.= '<select id="'.$this->getFieldId().'" name="'.$this->getFieldName().'">';
    $optionset = FieldSetManager::getOptionSet($this->id);
    $options = $optionset->getOptions();
    foreach ($options as $id => $option)
    {
      $text.='  <option value="'.$id.'"';
      if ($id == $this->value)
        $text.=' selected="selected"';
      $text.='>'.addslashes($option->getValue()).'</option>';
    }
    $text.= '</select>';
    return $text;
  }

  protected function getDefaultValue()
  {
    $optionset = FieldSetManager::getOptionSet($this->id);
    $options = $optionset->getOptions();
    $option = reset($options);
    return $option->getId();
  }
  
  public function setValue($value)
  {
    $option = null;
    $optionset = FieldSetManager::getOptionSet($this->id);
    if ($value != -1)
      $option = $optionset->getOption($value);

    if ($option === null)
    {
      $options = $optionset->getOptions();
      if (count($options)>0)
      {
        $option = reset($options);
        $value = $option->getId();
      }
      else
        $value = -1;
    }
    parent::setValue($value);
    $this->option = $option;
  }
  
  protected function retrieve()
  {
    parent::retrieve();
    $optionset = FieldSetManager::getOptionSet($this->id);
    if ($this->value != -1)
      $this->option = $optionset->getOption($this->value);
    if ($this->option === null)
      $this->setValue($this->value);
  }
  
  public function getOption()
  {
    $this->retrieve();
    return $this->option;
  }
  
  public function toString()
  {
    $this->retrieve();
    if ($this->option !== null)
      return $this->option->getValue();
    else
      return '';
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

		$result = '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$value.'"> ';

    $result.='<input id="fbfake-'.$this->getFieldId().'" disabled="true" type="text" value="'.$rlvalue.'"> ';
    $result.='<div class="toolbarbutton">';
    $result.='<a href="javascript:showFileBrowser(\''.$this->getFieldId().'\',\''.$request->encode().'\')">Select...</a>';
    $result.='</div> ';
    $result.='<div class="toolbarbutton">';
    $result.='<a href="javascript:clearFileBrowser(\''.$this->getFieldId().'\')">Clear</a>';
    $result.='</div> ';
    return $result;
  }
}

class FileField extends TextField
{
  private $filetype;
  private $filename;
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('filetype'))
      $this->filetype = $element->getAttribute('filetype');
    parent::parseAttributes($element);
  }
  
  public function isIndexed()
  {
    return false;
  }
  
  protected function retrieve()
  {
  	global $_PREFS;

    if ($this->retrieved)
      return;
      
    parent::retrieve();
    if (substr($this->value,0,7)=='global:')
    {
    	$this->filename = $_PREFS->getPref('storage.site.attachments').'/'.substr($this->value,7);
    	$this->value = $_PREFS->getPref('url.site.attachments').'/'.substr($this->value,7);
    }
    else if (substr($this->value,0,5)=='item:')
    {
    	$this->filename = $this->itemversion->getItem()->getStoragePath().'/'.substr($this->value,5);
    	$this->value = $this->itemversion->getItem()->getStorageUrl().'/'.substr($this->value,5);
    }
    else if (substr($this->value,0,8)=='version:')
   	{
    	$this->filename = $this->itemversion->getStoragePath().'/'.substr($this->value,8);
    	$this->value = $this->itemversion->getStorageUrl().'/'.substr($this->value,8);
   	}
  }
  
  public function getFilename()
  {
  	$this->retrieve();
  	return $this->filename;
  }
  
  public function setValue($value)
  {
  	global $_PREFS;

  	$path = $this->itemversion->getStorageUrl().'/';
  	if (substr($value,0,strlen($path))==$path)
  	{
  		$value = 'version:'.substr($value,strlen($path));
  		parent::setValue($value);
  		return;
  	}
  	$path = $this->itemversion->getItem()->getStorageUrl().'/';
  	if (substr($value,0,strlen($path))==$path)
  	{
  		$value = 'item:'.substr($value,strlen($path));
  		parent::setValue($value);
  		return;
  	}
  	$path = $_PREFS->getPref('url.site.attachments').'/';
  	if (substr($value,0,strlen($path))==$path)
  	{
  		$value = 'global:'.substr($value,strlen($path));
  		parent::setValue($value);
  		return;
  	}
  	parent::setValue($value);
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
    $request->setQueryVar('api', 'filefield');
    
    $attrs['request'] = $request->encode();
    
    return $attrs;
  }
  
  public function output(&$request, &$smarty)
  {
  	$this->retrieve();
  	return basename($this->value);
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
    $request->setQueryVar('api', 'filefield');

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

    $result = '<input id="'.$this->getFieldId().'" name="'.$this->getFieldName().'" type="hidden" value="'.$value.'"> ';

    $result.='<input id="fbfake-'.$this->getFieldId().'" disabled="true" type="text" value="'.$rlvalue.'"> ';
    $result.='<div class="toolbarbutton">';
    $result.='<a href="javascript:showFileBrowser(\''.$this->getFieldId().'\',\''.$request->encode().'\')">Select...</a>';
    $result.='</div> ';
    $result.='<div class="toolbarbutton">';
    $result.='<a href="javascript:clearFileBrowser(\''.$this->getFieldId().'\')">Clear</a>';
    $result.='</div> ';
    return $result;
  }
}

?>