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
  protected $id;
  protected $retrieved = false;
  protected $name;
  protected $description;
  protected $type;
  protected $log;
  
  public function __construct($metadata)
  {
    $this->log = LoggerManager::getLogger('swim.field.'.get_class($this));
    $this->parse($metadata);
  }
  
  public function __clone()
  {
    $this->retrieved = false;
    $this->itemversion = null;
  }
  
  public function setItemVersion($item)
  {
    $this->retrieved = false;
    $this->itemversion = $item;
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
    $this->retrieve();
    return $this->exists;
  }
  
  public function getEditor()
  {
  }
  
  public function setValue($value)
  {
  }
  
  public function toString()
  {
    return "";
  }
  
  public function compareTo($b)
  {
    return 0;
  }
  
  public function initialise()
  {
  }
  
  public function copyFrom($item)
  {
  }
  
  protected function retrieve()
  {
    $this->retreved = true;
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
      if ($type == 'file')
        return new FileField($el);
    }
    return new TextField($el);
  }
}

class SimpleField extends Field
{
  protected $value = '';
  
  protected function retrieve()
  {
    global $_STORAGE;
    
    if ($this->retrieved)
      return;
      
    $results = $_STORAGE->query('SELECT '.$this->getColumn().' FROM Field WHERE itemversion='.$this->itemversion->getId().' AND field="'.$_STORAGE->escape($this->id).'";');
    if ($results->valid())
    {
      $this->exists = true;
      $this->value = $results->fetchSingle();
    }
    $this->retrieved = true;
  }
  
  public function setValue($value)
  {
    global $_STORAGE;
    
    if ($this->isEditable())
    {
      $col = $this->getColumn();
      if ($_STORAGE->query('REPLACE INTO Field (itemversion,field,'.$col.') VALUES ('.$this->itemversion->getId().',"'.$_STORAGE->escape($this->id).'",'.$this->escapeValue($value).');'))
      {
        $this->value = $value;
        $this->exists = true;
        $this->retrieved = true;
        $this->itemversion->updateModified();
      }
    }
  }
  
  public function getEditor()
  {
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    return '<input '.$state.'style="width: 100%" type="input" id="field:'.$this->id.'" name="'.$this->id.'" value="'.$this->toString().'">';
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
  
  protected function getColumn()
  {
    return "intValue";
  }
}

class TextField extends SimpleField
{
  public function getEditor()
  {
    global $_PREFS;
    
    $state = '';
    if (!$this->isEditable())
      $state = 'disabled="true" ';
    if ($this->type == 'multiline')
      return '<textarea '.$state.'style="width: 100%; height: 50px;" id="field:'.$this->id.'" name="'.$this->id.'">'.htmlentities($this->toString()).'</textarea>';
    else if ($this->type == 'html')
    {
      if (!$this->isEditable())
        return '<div id="'.$this->id.'">'.$this->toString().'</div>';
      else
      {
        recursiveMkDir($this->itemversion->getStoragePath());
        include_once($_PREFS->getPref('storage.fckeditor').'/fckeditor.php');
        $editor = new FCKeditor($this->id) ;
        $editor->BasePath = $_PREFS->getPref('url.fckeditor');
        $editor->Value = $this->toString();
        $editor->Width  = '100%';
        $editor->Height = '400px';
        $editor->Config['SkinPath'] = $editor->BasePath.'editor/skins/office2003/';
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
  
  protected function getColumn()
  {
    return "textValue";
  }
}

class DateField extends SimpleField
{
  public function compareTo($b)
  {
    if ($b instanceof DateField)
      return $this->toString()-$b->toString();
    return 0;
  }
  
  protected function getColumn()
  {
    return "dateValue";
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
      $rlvalue = '[No file selected]';

    echo '<input id="'.$this->id.'" name="'.$this->id.'" type="hidden" value="'.$this->value.'"> ';

    echo '<input id="fbfake-'.$this->id.'" disabled="true" type="text" value="'.$rlvalue.'"> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:showFileBrowser(\''.$this->id.'\',\''.$request->encode().'\')">Select...</a>';
    echo '</div> ';
    echo '<div class="toolbarbutton">';
    echo '<a href="javascript:clearFileBrowser(\''.$this->id.'\')">Clear</a>';
    echo '</div> ';
  }
}

?>