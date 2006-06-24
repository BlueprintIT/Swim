<?

/*
 * Swim
 *
 * The basic field and some standard fields
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Field
{
  protected $exists = false;
  protected $itemversion = null;
  protected $name;
  protected $metadata;
  
  public function __construct($metadata, $item, $name)
  {
    $this->itemversion = $item;
    $this->name = $name;
    $this->metadata = $metadata;
  }
  
  public function getItemVersion()
  {
    return $this->itemversion;
  }
  
  public function isEditable()
  {
    return !$this->itemversion->isComplete();
  }
  
  public function exists()
  {
    return $this->exists;
  }
  
  public function toString()
  {
    return "";
  }
  
  public static function getField($el, $item, $name)
  {
    if (($el != null) && ($el->hasAttribute('type')))
    {
      $type = $el->getAttribute('type');
      if ($type == 'text')
        return new TextField($el, $item, $name);
      else if ($type == 'integer')
        return new IntegerField($el, $item, $name);
      else if ($type == 'date')
        return new DateField($el, $item, $name);
      else if ($type == 'sequence')
        return new Sequence($el, $item, $name);
    }
    else
    {
      return new TextField($el, $item, $name);
    }
  }
}

class SimpleField extends Field
{
  protected $value;
  
  public function __construct($metadata, $item, $name)
  {
    global $_STORAGE;
    
    parent::__construct($metadata, $item, $name);
    $results = $_STORAGE->query('SELECT '.$this->getColumn().' FROM Field WHERE itemversion='.$this->itemversion->getId().';');
    if ($results->valid())
    {
      $this->exists = true;
      $this->value = $results->fetchSingle();
    }
  }
  
  public function toString()
  {
    return $this->value;
  }
  
  protected function getColumn()
  {
    return "";
  }
}

class IntegerField extends SimpleField
{
  protected function getColumn()
  {
    return "intValue";
  }
}

class TextField extends SimpleField
{
  protected function getColumn()
  {
    return "textValue";
  }
}

class DateField extends SimpleField
{
  protected function getColumn()
  {
    return "dateValue";
  }
}

?>