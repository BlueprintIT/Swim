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

class BaseField
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
    $this->parse($metadata);
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
    $this->log->warn('Unknown element in field declaration: '.$element->tagName);
  }
  
  protected function parseAttributes($element)
  {
  }
  
  private function parse($metadata)
  {
    if ($metadata->hasAttribute('index') && ($metadata->getAttribute('index') == 'false'))
      $this->index = false;
    if ($metadata->hasAttribute('priority'))
      $this->indexPriority = $metadata->getAttribute('priority');
    $this->id = $metadata->getAttribute('id');
    $this->type = $metadata->getAttribute('type');
    $this->parseAttributes($metadata);
    $el=$metadata->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        if ($el->tagName=='name')
          $this->name=getDOMText($el);
        else if ($el->tagName=='description')
          $this->description=getDOMText($el);
        else
          $this->parseElement($el);
      }
      $el=$el->nextSibling;
    }
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
        return new TextField($el);
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
  
  public function getEditor()
  {
  }
  
  public function setValue($value)
  {
  }
  
  public function output(&$smarty)
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
  }
  
  public function getEditor()
  {
    $result = "<table>\n<thead><tr>";
    foreach ($this->fields as $field)
    {
      $result.='<th>'.$field->getName().'</th>';
    }
    $result.="</tr></thead>\n<tbody>\n";
    $rows = $this->getRows();
    foreach ($rows as $row)
    {
      $result.='<tr>';
      foreach ($this->fields as $field)
      {
        $rlfield = $row->getField($field->getId());
        $result.='<td>'.$rlfield->getEditor().'</td>';
      }
      $result.="</tr>\n";
    }
    $result.="</tbody>\n</table>\n";
    return $result;
  }
  
  public function output(&$smarty)
  {
    $result = "<table>\n<thead><tr>";
    foreach ($this->fields as $field)
    {
      $result.='<th>'.$field->getName().'</th>';
    }
    $result.="</tr></thead>\n<tbody>\n";
    $rows = $this->getRows();
    foreach ($rows as $row)
    {
      $result.='<tr>';
      foreach ($this->fields as $field)
      {
        $rlfield = $row->getField($field->getId());
        $result.='<td>'.$rlfield->output($smarty).'</td>';
      }
      $result.="</tr>\n";
    }
    $result.="</tbody>\n</table>\n";
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
  
  public function __construct($pos, $fields)
  {
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
  
  public function getField($name)
  {
    return $this->fields[$name];
  }
}

?>