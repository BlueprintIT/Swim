<?

/*
 * Swim
 *
 * Smarty interface functions
 *
 * Copyright Blueprint IT Ltd. 2006
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
        if ($field != null)
        {
          return $field->toString();
        }
        return '';
    }
  }
}

class ItemWrapper
{
  private $itemversion;
  
  public function __construct($itemversion)
  {
    $this->itemversion = $itemversion;
  }
  
  public function __get($name)
  {
    switch($name)
    {
      case 'modified':
        return $this->itemversion->getModified();
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
          return new ItemWrapper($parents[0]);
        return null;
        break;
      case 'url':
        $target = $this->itemversion->getLinkTarget();
        if ($target == null)
          $target = $this->itemversion;
        $req = new Request();
        $req->setMethod('view');
        $req->setPath($target->getItem()->getId());
        return $req->encode();
        break;
      default:
        $field = $this->itemversion->getField($name);
        if ($field != null)
        {
          if ($field->getType() == 'sequence')
          {
            $result = array();
            $items = $field->getItems();
            foreach ($items as $item)
            {
              $itemv = $item->getCurrentVersion(Session::getCurrentVariant());
              if ($itemv != null)
              {
                $wrapped = new ItemWrapper($itemv);
                array_push($result, $wrapped);
              }
            }
            return $result;
          }
          else if ($field->getType() == 'compound')
          {
            $rows = $field->getRows();
            foreach ($rows as $key => $row)
            {
              $rows[$key] = new RowWrapper($row);
            }
            return $rows;
          }
          else
            return $field->toString();
        }
        return '';
    }
  }
}

function item_wrap($params, &$smarty)
{
  if (isset($params['var']))
  {
    if ((!isset($params['item'])) || ($params['item'] == null))
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
    $smarty->assign_by_ref($params['var'], new ItemWrapper($item));
  }
  else
  {
    return "Not enough parameters";
  }
}

?>