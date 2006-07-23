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

?>