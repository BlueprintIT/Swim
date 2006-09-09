<?

/*
 * Swim
 *
 * The basic field types
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class BaseField extends XMLSerialized
{
  protected $id;
  protected $name;
  protected $description;
  protected $type;
  protected $log;
  protected $index = true;
  protected $indexPriority = 1;

  public function __construct($metadata)
  {
    $this->log = LoggerManager::getLogger('swim.field.'.get_class($this));
    $this->load($metadata);
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
    $this->log = LoggerManager::getLogger('swim.field.'.get_class($this));
  }
  
  public function initialise()
  {
  }
  
  public function copyFrom($item)
  {
  }
  
  public function getId()
  {
    return $this->id;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getType()
  {
    return $this->type;
  }
  
  public function getDescription()
  {
    return $this->description;
  }
  
  public function isIndexed()
  {
    return $this->index && $this->indexPriority>0;
  }
  
  public function getIndexPriority()
  {
    return $this->indexPriority;
  }
  
  public function getPlainText()
  {
    return "";
  }

  protected function parseElement($element)
  {
    if ($element->tagName=='name')
      $this->name=getDOMText($element);
    else if ($element->tagName=='description')
      $this->description=getDOMText($element);
    else
      parent::parseElement($element);
  }
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('index') && ($element->getAttribute('index') == 'false'))
      $this->index = false;
    if ($element->hasAttribute('priority'))
      $this->indexPriority = $element->getAttribute('priority');
    $this->id = $element->getAttribute('id');
    $this->type = $element->getAttribute('type');
  }
  
  public static function getField($el)
  {
    if (($el != null) && ($el->hasAttribute('type')))
    {
      $type = $el->getAttribute('type');
      if ($type == 'text')
        return new TextField($el);
      if ($type == 'multiline')
        return new TextField($el);
      if ($type == 'html')
        return new HTMLField($el);
      if ($type == 'integer')
        return new IntegerField($el);
      if ($type == 'date')
        return new DateField($el);
      if ($type == 'sequence')
        return new Sequence($el);
      if ($type == 'item')
        return new ItemField($el);
      if ($type == 'file')
        return new FileField($el);
      if ($type == 'optionset')
        return new OptionField($el);
      if ($type == 'compound')
        return new CompoundField($el);
    }
    return new TextField($el);
  }
}

class VersionField extends BaseField
{
  protected $itemversion = null;
  
  public function __construct($metadata)
  {
    parent::__construct($metadata);
  }
  
  public function __clone()
  {
    $this->itemversion = null;
  }
  
  public function isEditable()
  {
    return !$this->itemversion->isComplete();
  }
  
  public function setItemVersion($item)
  {
    $this->retrieved = false;
    $this->itemversion = $item;
  }
  
  public function getItemVersion()
  {
    return $this->itemversion;
  }
}

class ClassField extends BaseField
{
  protected $item = null;
  
  public function __construct($metadata)
  {
    parent::__construct($metadata);
  }
  
  public function __clone()
  {
    $this->item = null;
  }
  
  public function setItem($item)
  {
    $this->item = $item;
  }
  
  public function getItem()
  {
    return $this->item;
  }
}

class Field extends VersionField
{
  protected $exists = false;
  protected $retrieved = false;
    
  public function __construct($metadata)
  {
    parent::__construct($metadata);
  }
  
  public function setItemVersion($item)
  {
    $this->retrieved = false;
    parent::setItemVersion($item);
  }
  
  public function __clone()
  {
    $this->retrieved = false;
    parent::__clone();
  }
  
  public function exists()
  {
    $this->retrieve();
    return $this->exists;
  }
  
  public function getEditor(&$request, &$smarty)
  {
  }
  
  public function setValue($value)
  {
  }
  
  public function output(&$request, &$smarty)
  {
    return $this->toString();
  }
  
  public function toString()
  {
    return "";
  }
  
  public function compareTo($b)
  {
    return 0;
  }
  
  protected function retrieve()
  {
    $this->retreved = true;
  }
}

class CompoundField extends Field
{
  private $fields = array();
  private $rows = array();
  
  public function __construct($metadata)
  {
    parent::__construct($metadata);
    $this->exists = true;
  }

  public function setItemVersion($item)
  {
    $this->rows = array();
    parent::setItemVersion($item);
    foreach ($this->fields as $field)
    {
      $field->setItemVersion($item);
    }
  }
  
  public function getJSVar($value)
  {
    if (is_array($value))
    {
      $result = '{';
      if (count($value)>0)
      {
        foreach ($value as $key => $val)
        {
          $result.=$key.':'.$this->getJSVar($val).',';
        }
        $result = substr($result,0,-1);
      }
      return $result.'}';
    }
    else
      return '"'.addslashes($value).'"';
  }
  
  public function getEditor(&$request, &$smarty)
  {
    global $_PREFS;
    
    $rowcount = $this->count();
    if ($request->hasQueryVar($this->getId()))
    {
      $passed = $request->getQueryVar($this->getId());
      if (is_array($passed))
        $rowcount = count($passed);
    }
    
    $result = "<script type=\"text/javascript\">\n<!--\n";
    $result.= "var compound_".$this->getId()." = { id: '".$this->getId()."', fields: {";
    foreach ($this->fields as $field)
    {
      $result.=$field->getId().": {";
      $attrs = $field->getClientAttributes();
      foreach ($attrs as $name => $value)
      {
        $result.=" ".$name.": ".$this->getJSVar($value).",";
      }
      $result = substr($result,0,-1);
      $result.=" },";
    }
    $result = substr($result,0,-1);
    $result.="} };";
    $result.= "\n-->\n</script>\n";
    $result.= "<table class=\"compound\">\n";
    if (count($this->fields)>1)
    {
      $result.= "<thead><tr>";
      foreach ($this->fields as $field)
      {
        $result.='<th>'.$field->getName().'</th>';
      }
      $result.="<th></th></tr></thead>\n";
    }
    $result.="<tbody id=\"tbody_".$this->id."\">\n";
    for ($pos = 0; $pos<$rowcount; $pos++)
    {
      $row = $this->getRow($pos);
      $result.='<tr>';
      foreach ($this->fields as $field)
      {
        $rlfield = $row->getField($field->getId());
        $result.='<td>'.$rlfield->getEditor($request, $smarty).'</td>';
      }
      $result.="<td class=\"options\">";
      $result.="<a class=\"option\" href=\"#\" onclick=\"moveCompoundRow(compound_".$this->id.", this.parentNode.parentNode, true); return false\">";
      $result.="<img alt=\"Move up\" title=\"Move up\" src=\"".$_PREFS->getPref('url.admin.static')."/icons/up-purple.gif\">";
      $result.="</a>";
      $result.="<a class=\"option\" href=\"#\" onclick=\"moveCompoundRow(compound_".$this->id.", this.parentNode.parentNode, false); return false\">";
      $result.="<img alt=\"Move down\" title=\"Move down\" src=\"".$_PREFS->getPref('url.admin.static')."/icons/down-purple.gif\">";
      $result.="</a>";
      $result.="<a class=\"option\" href=\"#\" onclick=\"deleteCompoundRow(compound_".$this->id.", this.parentNode.parentNode); return false\">";
      $result.="<img alt=\"Delete row\" title=\"Delete row\" src=\"".$_PREFS->getPref('url.admin.static')."/icons/delete-page-purple.gif\">";
      $result.="</a>";
      $result.="</td></tr>\n";
    }
    $result.="</tbody>\n<tfoot>\n<tr>";
    $result.="<td colspan=\"".count($this->fields)."\"></td><td class=\"options\">";
    $result.="<a class=\"option\" onclick=\"createCompoundRow(compound_".$this->id."); return false\" href=\"#\">";
    $result.="<img alt=\"Add row\" title=\"Add row\" src=\"".$_PREFS->getPref('url.admin.static')."/icons/add-page-purple.gif\">";
    $result.="</a></td>";
    $result.="</tr>\n</tfoot>\n</table>\n";
    return $result;
  }
  
  public function output(&$request, &$smarty)
  {
    $rows = $this->getRows();
    if (count($rows)>0)
    {
      $result = "<table class=\"compound\">\n";
      if (count($this->fields)>1)
      {
        $result.= "<thead><tr>";
        foreach ($this->fields as $field)
        {
          $result.='<th>'.$field->getName().'</th>';
        }
        $result.="<th></th></tr></thead>\n";
      }
      $result.="<tbody>\n";
      foreach ($rows as $row)
      {
        $result.='<tr>';
        foreach ($this->fields as $field)
        {
          $rlfield = $row->getField($field->getId());
          $result.='<td>'.$rlfield->output($request, $smarty).'</td>';
        }
        $result.="</tr>\n";
      }
      $result.="</tbody>\n</table>\n";
    }
    else
    {
      $result = "None";
    }
    return $result;
  }

  public function getPlainText()
  {
    $result = '';
    $rows = $this->getRows();
    foreach ($rows as $row)
    {
      foreach ($this->fields as $field)
      {
        $rlfield = $row->getField($field->getId());
        $result.=$rlfield->getPlainText().' ';
      }
    }
    return $result;
  }

  public function setValue($value)
  {
    global $_STORAGE;
    
    if ($this->isEditable() && is_array($value))
    {
      $_STORAGE->queryExec('DELETE FROM Field WHERE itemversion='.$this->itemversion->getId().' AND basefield="'.$_STORAGE->escape($this->id).'";');
      $this->rows = array();
      foreach ($value as $key => $val)
      {
        $row = $this->getRow($key);
        $row->setValue($val);
      }
    }
  }
  
  public function getRows()
  {
    $count = $this->count();
    $result = array();
    for ($i=0; $i<$count; $i++)
    {
      $result[$i] = $this->getRow($i);
    }
    return $result;
  }
  
  public function getRow($index)
  {
    if (!isset($this->rows[$index]))
    {
      $fields = array();
      foreach ($this->fields as $name => $field)
      {
        $field = clone $field;
        $field->setItemVersion($this->itemversion);
        $field->setPosition($index);
        $fields[$name] = $field;
      }
      $this->rows[$index] = new CompoundRow($index, $fields);
    }
    return $this->rows[$index];
  }
  
  public function count()
  {
    global $_STORAGE;
    
    $result = $_STORAGE->query('SELECT MAX(pos)+1 FROM Field WHERE itemversion='.$this->itemversion->getId().' AND basefield="'.$_STORAGE->escape($this->getId()).'";');
    $result = $result->fetchSingle();
    if ($result === false)
      return 0;
    else
      return $result;
  }
  
  public function appendRow()
  {
    if ($this->isEditable())
      return $this->getRow($this->count());
  }
  
  public function removeRow($index)
  {
    global $_STORAGE;
    
    if ($this->isEditable())
    {
      $count = $this->count();
      $_STORAGE->queryExec('DELETE FROM Field WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos='.$index.';');
      if (isset($this->rows[$index]))
        unset($this->rows[$index]);
      for ($i=$index+1; $i<$count; $i++)
      {
        $_STORAGE->queryExec('UPDATE Field SET pos='.($i-1).' WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos='.$i.';');
        if (isset($this->rows[$i]))
        {
          $this->rows[$i]->setPosition($i-1);
          $this->rows[$i-1] = $this->rows[$i];
          unset($this->rows[$i]);
        }
      }
    }
  }
  
  public function moveRow($from, $to)
  {
    if ($this->isEditable())
    {
      $_STORAGE->queryExec('UPDATE Field SET pos=-1 WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos='.$from.';');
      if (isset($this->rows[$from]))
      {
        $row = $this->rows[$from];
        unset($this->rows[$from]);
      }
      if ($from<$to)
      {
        for ($i=$from+1; $i<=$to; $i++)
        {
          $_STORAGE->queryExec('UPDATE Field SET pos='.($i-1).' WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos='.$i.';');
          if (isset($this->rows[$i]))
          {
            $this->rows[$i]->setPosition($i-1);
            $this->rows[$i-1] = $this->rows[$i];
            unset($this->rows[$i]);
          }
        }
      }
      else
      {
        for ($i=$from-1; $i>=$to; $i--)
        {
          $_STORAGE->queryExec('UPDATE Field SET pos='.($i+1).' WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos='.$i.';');
          if (isset($this->rows[$i]))
          {
            $this->rows[$i]->setPosition($i+1);
            $this->rows[$i+1] = $this->rows[$i];
            unset($this->rows[$i]);
          }
        }
      }
      $_STORAGE->queryExec('UPDATE Field SET pos='.$to.' WHERE itemversion='.$this->itemversion.' AND basefield="'.$_STORAGE->escape($this->getId()).'" AND pos=-1;');
      if (isset($row))
      {
        $row->setPosition($to);
        $this->rows[$to] = $row;
      }
    }
  }

  protected function parseElement($element)
  {
    if ($element->tagName=='field')
    {
      $field = BaseField::getField($element);
      if ($field instanceof SimpleField)
      {
        $field->setBaseField($this->getId());
        $this->fields[$field->getId()] = $field;
      }
      else
        $this->log->error('Invalid field type specified as part of compound field '.$this->getId());
    }
    else
      parent::parseElement($element);
  }
}

class CompoundRow
{
  private $fields = array();
  private $position = 0;
  private $log;
  
  public function __construct($pos, $fields)
  {
    $this->log = LoggerManager::getLogger('swim.field.'.get_class($this));
    $this->fields = $fields;
    $this->position = $pos;
  }
  
  public function setPosition($pos)
  {
    $this->position = $pos;
    foreach ($this->fields as $field)
    {
      $field->setPosition($pos);
    }
  }
  
  public function setValue($value)
  {
    foreach ($value as $field => $val)
    {
      if (!isset($this->fields[$field]))
        $this->log->error('Attempt to set unknown field '.$field);
      else
        $this->fields[$field]->setValue($val);
    }
  }
  
  public function getField($name)
  {
    return $this->fields[$name];
  }
}

?>