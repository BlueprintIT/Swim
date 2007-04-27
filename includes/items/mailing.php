<?

/*
 * Swim
 *
 * Mailing functionality.
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/includes/items/section.php $
 * $LastChangedBy: dave $
 * $Date: 2007-04-27 10:33:29 +0100 (Fri, 27 Apr 2007) $
 * $Revision: 1456 $
 */

class MailingClass extends ItemClass
{
  protected $mailing;
  
  public function __construct($id, $mailing)
  {
    parent::__construct($id, FieldSetManager::getClass('_mailing'));
    $this->mailing = $mailing;
  }
  
  public function getMailing()
  {
    return $this->mailing;
  }
}

class MailingItemSet extends XMLSerialized
{
  protected $id;
  protected $mailing;
  protected $log;
  protected $name;

  public function __construct($id, $mailing)
  {
    $this->id = $id;
    $this->mailing = $mailing;
    $this->log = LoggerManager::getLogger('swim.mailingitemset');
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
    $this->log = LoggerManager::getLogger('swim.mailingitemset');
  }
  
  public function getId()
  {
    return $this->id;
  }

  public function getName()
  {
    return $this->name;
  }
  
  public function getItems()
  {
    return array();
  }

  protected function parseElement($element)
  {
    if ($element->tagName == 'name')
    {
      $this->name = getDOMText($element);
    }
    else
      parent::parseElement($element);
  }
  
  public function load($element)
  {
    parent::load($element);

    $field = $element->ownerDocument->createElement('field');
    $field->setAttribute('id', $element->getAttribute('id'));
    $field->setAttribute('type', 'compound');
    $name = $field->ownerDocument->createElement('name');
    $field->appendChild($name);
    setDOMText($name, $this->name);

    $subfield = $field->ownerDocument->createElement('field');
    $field->appendChild($subfield);
    $subfield->setAttribute('id', 'item');
    $subfield->setAttribute('type', 'item');

    $this->mailing->getClass()->addField($field);
  }
}

class MailingSelection extends MailingItemSet
{
  protected $classes;
  protected $sections;
  protected $maxcount;
  
  protected function parseAttributes($element)
  {
    if ($element->hasAttribute('maxcount'))
      $this->maxcount = $element->getAttribute('maxcount');
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName == 'classes')
    {
      $this->classes = split(',', getDOMText($element));
    }
    else if ($element->tagName == 'sections')
    {
      $this->sections = split(',', getDOMText($element));
    }
    else
      parent::parseElement($element);
  }
}

class Mailing extends XMLSerialized
{
  protected $id;
  protected $section;
  protected $log;
  protected $name;
  protected $subject;
  protected $mailclass;
  protected $frequencycount;
  protected $frequencyperiod = 'month';
  protected $itemsets;
  protected $values;
  
  public function __construct($id, $section)
  {
    $this->id = $id;
    $this->section = $section;
    $this->log = LoggerManager::getLogger('swim.mailing');
    $this->mailclass = new MailingClass($id, $this);
    $this->itemsets = array();
  }

  public function __sleep()
  {
    $vars = get_object_vars($this);
    unset($vars['log']);
    unset($vars['values']);
    return array_keys($vars);
  }
  
  public function __wakeup()
  {
    $this->log = LoggerManager::getLogger('swim.mailing');
  }
  
  private function retrieve()
  {
    global $_STORAGE;
    
    if (isset($this->values))
      return;
      
    $results = $_STORAGE->query('SELECT * FROM Mailing WHERE id="'.$this->id.'";');
    if ($results->valid())
      $this->values = $results->fetch();
    else
    {
      $this->values = array('id' => $this->id,
                            'section' => $this->section->getId(),
                            'contacts' => $this->section->getRootContacts()->getId(),
                            'lastsent' => -1,
                            'intro' => '');
      $_STORAGE->queryExec('INSERT INTO Mailing (id, section, contacts, lastsent, intro) VALUES ' .
                           '("'.$this->id.'","'.$this->values['section'].'",'.$this->values['contacts'].',-1,"");');
    }
  }
  
  public function getId()
  {
    return $this->id;
  }

  public function getClass()
  {
    return $this->mailclass;
  }
  
  public function getName()
  {
    return $this->name;
  }

  public function getSubject()
  {
    return $this->subject;
  }
  
  public function hasFrequency()
  {
    return isset($this->frequencycount);
  }
  
  public function getFrequencyCount()
  {
    return $this->frequencycount;
  }
  
  public function getFrequencyPeriod()
  {
    return $this->frequencyperiod;
  }
  
  public function getLastSent()
  {
    $this->retrieve();
    return $this->values['lastsent'];
  }
  
  public function getContacts()
  {
    $this->retrieve();
    return Item::getItem($this->values['contacts']);
  }
  
  public function setContacts($item)
  {
    global $_STORAGE;
    
    $this->retrieve();
    $_STORAGE->queryExec('UPDATE Mailing SET contacts='.$item->getId().';');
    $this->values['contacts'] = $item->getId();
  }
  
  public function getIntro()
  {
    $this->retrieve();
    return $this->values['intro'];
  }
  
  public function setIntro($value)
  {
    global $_STORAGE;
    
    $this->retrieve();
    $_STORAGE->queryExec('UPDATE Mailing SET intro="'.$_STORAGE->escape($value).'";');
    $this->values['intro'] = $value;
  }
  
  public function getItemSets()
  {
    return $this->itemsets;
  }
  
  public function createMail()
  {
    $item = Item::createItem($this->section, $this->mailclass);
    $variant = $item->createVariant(Session::getCurrentVariant());
    $iv = $variant->createNewVersion();
    $iv->setFieldValue('name', $this->getSubject());
    $iv->setFieldValue('contacts', $this->getContacts()->getId());
    $iv->setFieldValue('sent', false);
    $iv->setFieldValue('intro', $this->getIntro());
    
    foreach ($this->itemssets as $id => $itemset)
    {
      $items = $itemset->getItems();
      $compound = $iv->getField($id);
      foreach ($items as $item)
      {
        $row = $id->appendRow();
        $row->setFieldValue('item', $item->getId());
      }
    }
    
    $parent = $this->section->getRootItem();
    $sequence = $parent->getMainSequence();
    $sequence->insertItem(0, $item);
    
    return $iv;
  }
  
  public function sendMail($itemversion)
  {
    global $_PREFS;
    
    $itemversion->setFieldValue('sent', true);
    $itemversion->setFieldValue('date', time());
    
    require_once('Mail/mime.php');
    $mail = new Mail_Mime();
    $path = $_PREFS->getPref('storage.site.templates').'/mail/'.$itemversion->getClass()->getId();
    if (is_file($path.'.html.tpl'))
    {
      $smarty = createMailSmarty('text/html');
      $smarty->assign_by_ref('item', ItemWrapper::getWrapper($itemversion));
      $mail->setHTMLBody($smarty->fetch($path.'.html.tpl', $itemversion->getItem()->getId()));
    }
    if (is_file($path.'.text.tpl'))
    {
      $smarty = createMailSmarty('text/plain');
      $smarty->assign_by_ref('item', ItemWrapper::getWrapper($itemversion));
      $mail->setTxtBody($smarty->fetch($path.'.text.tpl', $itemversion->getItem()->getId()));
    }
    
    $body = $mail->get();
    $headers = '';
    foreach ($mail->headers() as $name => $value)
      $headers .= $name.': '.$value."\r\n";
    $headers .= 'From: Swim CMS running on '.$_SERVER['HTTP_HOST'].' <swim@'.$_SERVER['HTTP_HOST'].'>';
    if ($_PREFS->getPref('mail.headernewline'))
      $headers .= "\r\n";
    
    mail('dave.townsend@blueprintit.co.uk', $itemversion->getFieldValue('subject'), $body, $headers);
    
    
    $itemversion->setComplete(true);
    $itemversion->setCurrent(true);
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName == 'name')
    {
      $this->name = getDOMText($element);
    }
    else if ($element->tagName == 'subject')
    {
      $this->subject = getDOMText($element);
    }
    else if ($element->tagName == 'frequency')
    {
      if ($element->hasAttribute('period'))
        $this->frequencyperiod = $element->getAttribute('period');
      $this->frequencycount = getDOMText($element);
    }
    else if ($element->tagName == 'selection')
    {
      $itemset = new MailingSelection($element->getAttribute('id'), $this);
      $itemset->load($element);
      $this->itemsets[$itemset->getId()] = $itemset;
    }
    else
      parent::parseElement($element);
  }
}

class MailingSection extends Section
{
  protected $contacts;
  protected $mailings;
  
  public function getType()
  {
    return 'mailing';
  }
  
  public function getIcon()
  {
    global $_PREFS;
    
    return $_PREFS->getPref('url.admin.static').'/icons/email-blue.gif';
  }
  
  public function getRootContacts()
  {
    return Item::getItem($this->contacts);
  }
  
  private function getRootClass()
  {
    return "_mailingcategory";
  }
  
  protected function findRoot()
  {
    $this->roottype = '_contactcategory';
    parent::findRoot(true);
    $this->contacts = $this->item;
    $this->roottype = '_mailingcategory';
    parent::findRoot(true);
  }

  public function getURL()
  {
    $request = new Request();
    $request->setMethod('admin');
    $request->setPath('mailing/index.tpl');
    $request->setQueryVar('section', $this->getId());
    return $request->encode();
  }
  
  public function isAvailable()
  {
    return Session::getUser()->hasPermission('contacts',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if (($request->getMethod()=='admin') && (substr($request->getPath(),0,8)=='mailing/') && ($request->hasQueryVar('section')) && ($request->getQueryVar('section')==$this->getId()))
      return true;
    return false;
  }

  public function getMailing($id)
  {
    if (isset($this->mailings[$id]))
      return $this->mailings[$id];
    return null;
  }
  
  public function getMailings()
  {
    return $this->mailings;
  }
  
  protected function parseElement($element)
  {
    if ($element->tagName == 'mailing')
    {
      $id = $element->getAttribute('id');
      $mailing = new Mailing($id, $this);
      $this->mailings[$id] = $mailing;
      $mailing->load($element);
      FieldSetManager::addClass($mailing->getClass());
    }
    else
      parent::parseElement($element);
  }
}

?>