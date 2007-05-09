<?

/*
 * Swim
 *
 * Smarty interface functions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class RowWrapper
{
  private $row;
  
  public function __construct($row)
  {
    $this->row = $row;
  }

  public function __get($name)
  {
    switch($name)
    {
      default:
        $field = $this->row->getField($name);
        return ItemFieldWrapper::getFieldWrapper($field);
    }
  }
}

class OptionWrapper
{
  private $option;
  
  public function __construct($option)
  {
    $this->option = $option;
  }
  
  public function __get($name)
  {
    if ($this->option === null)
      return '';

    switch ($name)
    {
      case 'name':
        return $this->option->getName();
        break;
      case 'value':
        return $this->option->getValue();
        break;
    }
    return '';
  }
}

class SectionWrapper
{
	private $section;
	
	public function __construct($section)
	{
		$this->section = $section;
	}

	public function __get($name)
	{
    switch($name)
    {
      case 'root':
      	$iv = $this->section->getRootItem()->getCurrentVersion(Session::getCurrentVariant());
      	if ($iv !== null)
	        return ItemWrapper::getWrapper($iv);
        break;
      case 'contacts':
        if ($this->section->getType() == 'mailing')
        {
          $iv = $this->section->getRootContacts()->getCurrentVersion(Session::getCurrentVariant());
          if ($iv !== null)
            return ItemWrapper::getWrapper($iv);
        }
        break;
      case 'name':
      	return $this->section->getName();
      	break;
    }
    return null;
	}
}

class SectionsWrapper
{
	public function __get($name)
	{
    switch($name)
    {
      default:
      	$section = FieldSetManager::getSection($name);
      	if ($section !== null)
      		return new SectionWrapper($section);
      	break;
    }
    return null;
	}
}

class FileWrapper
{
	private $field;
	
	public function __construct($field)
	{
		$this->field = $field;
	}
	
	public function __get($name)
	{
    switch($name)
    {
    	case 'url':
    		return $this->field->toString();
    		break;
    	case 'file':
    		return $this->field->getFilename();
    		break;
    	case 'width':
    		$size = @getimagesize($this->field->getFilename());
    		if ($size !== FALSE)
    			return $size[0];
    		return 0;
    		break;
    	case 'height':
    		$size = @getimagesize($this->field->getFilename());
    		if ($size !== FALSE)
    			return $size[1];
    		return 0;
    		break;
    }
    return null;
	}
	
	public function __toString()
	{
		return $this->field->toString();
	}
}

class ItemFieldWrapper
{
	private $itemversion;
	private $log;
	private $results;

  public function __construct($itemversion)
  {
    $this->itemversion = $itemversion;
    $this->log = LoggerManager::getLogger('swim.itemfieldwrapper');
    $this->results = array();
  }
  
  public static function getFieldWrapper($field)
  {
    if ($field !== null)
    {
      if ($field->getType() == 'sequence')
      {
        $result = array();
        $items = $field->getItems();
        foreach ($items as $item)
        {
          $itemv = $item->getCurrentVersion(Session::getCurrentVariant());
          if ($itemv !== null)
          {
            $wrapped = ItemWrapper::getWrapper($itemv);
            array_push($result, $wrapped);
          }
        }
      }
      else if ($field->getType() == 'compound')
      {
        $result = $field->getRows();
        foreach ($result as $key => $row)
        {
          $result[$key] = new RowWrapper($row);
        }
      }
      else if ($field->getType() == 'optionset')
      {
        $option = $field->getOption();
        $result = new OptionWrapper($option);
      }
      else if ($field->getType() == 'file')
      {
        if ($field->toString())
          $result = new FileWrapper($field);
      }
      else if ($field->getType() == 'item')
      {
        $item = $field->getItem();
        if ($item)
          $item = $item->getCurrentVersion(Session::getCurrentVariant());
        if ($item)
          $result = ItemWrapper::getWrapper($item);
      }
      else
        $result = $field->toString();
    }
    
    if ((!isset($result)) || ($result === null))
      $result = '';
      
    return $result;
  }
  
  public function __get($name)
  {
  	if (isset($this->results[$name]))
  		return $this->results[$name];
  	
  	$result = null;
  	
    $field = $this->itemversion->getField($name);
    $result = self::getFieldWrapper($field);
    $this->results[$name] = $result;
    
    return $result;
  }
}

class ItemWrapper
{
  private $itemversion;
  private $fieldwrapper;
  private $log;
  
  public function __construct($itemversion)
  {
    $this->itemversion = $itemversion;
    $this->log = LoggerManager::getLogger('swim.itemwrapper');
    $this->fieldwrapper = new ItemFieldWrapper($itemversion);
  }
  
  public static function getWrapper($itemversion)
  {
    $wrapper = ObjectCache::getItem('itemwrapper', $itemversion->getId());
    if ($wrapper === null)
    {
    	$wrapper = new ItemWrapper($itemversion);
    	ObjectCache::setItem('itemwrapper', $itemversion->getId(), $wrapper);
    }
    return $wrapper;
  }
  
  public function getUrl($extra = '')
  {
  	global $_PREFS;
  	
    $target = $this->itemversion->getLinkTarget();
    if ($target === null)
      $target = $this->itemversion;
    $path = $target->getItem()->getPath();
  	if ($extra !== '')
  	{
  		if (substr($extra,0,1)!='/')
  			$extra = '/'.$extra;
  	}
    if ($path !== null)
    {
    	return $_PREFS->getPref('url.pagegen').$path.$extra;
    }
    else if (($extra === '') && ($_PREFS->getPref('url.defaultmethod')=='view') && ($_PREFS->getPref('url.defaultpath')==$target->getItem()->getId()))
    {
    	return $_PREFS->getPref('url.pagegen').'/';
    }
    else
    {
      $req = new Request();
      $req->setMethod('view');
      $req->setPath($target->getItem()->getId().$extra);
      return $req->encode();
    }
  }
  
  public function __get($name)
  {
    switch($name)
    {
      case 'allowsLink':
        return $this->itemversion->getClass()->allowsLink();
        break;
      case 'modified':
        return $this->itemversion->getModified();
        break;
      case 'published':
        return $this->itemversion->getPublished();
        break;
      case 'id':
        return $this->itemversion->getItem()->getId();
        break;
      case 'item':
        return $this->itemversion;
        break;
      case 'section':
        return $this->itemversion->getItem()->getSection()->getId();
        break;
      case 'class':
        return $this->itemversion->getClass()->getId();
        break;
      case 'view':
        return $this->itemversion->getView()->getId();
        break;
      case 'author':
        return $this->itemversion->getOwner()->getName();
        break;
      case 'version':
        return $this->itemversion->getVersion();
        break;
      case 'parent':
        $parents = $this->itemversion->getItem()->getMainParents();
        if (count($parents)>0)
          return ItemWrapper::getWrapper($parents[0]);
        return null;
        break;
      case 'parentPath':
        $parents = $this->itemversion->getItem()->getParentPath();
        if ($parents !== null)
        {
          for ($i=0; $i<count($parents); $i++)
          {
            $itemv = $parents[$i]->getCurrentVersion(Session::getCurrentVariant());
            if ($itemv !== null)
              $parents[$i] = ItemWrapper::getWrapper($itemv);
            else
              $parents[$i] = null;
          }
          return $parents;
        }
        return null;
        break;
      case 'url':
      	return $this->getUrl();
        break;
      case 'mainsequence':
      	$field = $this->itemversion->getMainSequence();
      	if ($field !== null)
      	{
      		$result = array();
          $items = $field->getItems();
          foreach ($items as $item)
          {
            $itemv = $item->getCurrentVersion(Session::getCurrentVariant());
            if ($itemv !== null)
            {
              $wrapped = ItemWrapper::getWrapper($itemv);
              array_push($result, $wrapped);
            }
          }
          return $result;
      	}
      	else
      		return array();
      	break;
      case 'fields':
      	return $this->fieldwrapper;
      default:
      	return $this->fieldwrapper->__get($name);
    }
  }
}

function item_wrap($params, &$smarty)
{
  if (isset($params['var']))
  {
    if ((!isset($params['item'])) || ($params['item'] === null))
    {
      $smarty->assign($params['var'], null);
      return;
    }
    $item = $params['item'];
    
    if ($item instanceof Item)
      $item = $item->getCurrentVersion(Session::getCurrentVariant());
    else if ($item instanceof ItemVariant)
      $item = $item->getCurrentVersion();
    else if (!($item instanceof ItemVersion))
      return "Invalid item specified";
    $smarty->assign_by_ref($params['var'], ItemWrapper::getWrapper($item));
  }
  else
  {
    return "Not enough parameters";
  }
}

?>
