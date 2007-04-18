<?

/*
 * Swim
 *
 * SWIM search code
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class SearchEngine
{
  private function getSubitems($item, &$items)
  {
    $sequence = $item->getMainSequence();
    if ($sequence !== null)
    {
      foreach ($sequence->getItems() as $subitem)
      {
        if (!isset($items[$subitem->getId()]))
        {
          $items[$subitem->getId()] = $subitem;
          self::getSubitems($subitem, $items);
        }
      }
    }
  }
  
  public static function search($searchstring, $item = null, $classes = array(), $section = null)
  {
    global $_STORAGE;
    
    $words = explode(' ', $searchstring);
    $sums = '';
    $counts = '';
    $tables = 'Item';
    $where = '';
    $pos = 1;
    foreach ($words as $word)
    {
      if (strlen($word)>1)
      {
        $need = true;
        if (substr($word,0,1)=='+')
          $word = substr($word,1);
        else if (substr($word,0,1)=='-')
        {
          $word = substr($word,1);
          $need = false;
        }
        $word = strtolower($word);
        if ($pos>1)
        {
          $counts.='+';
          $sums.='+';
        }
        if ($need)
          $counts.='IF(ISNULL(Word'.$pos.'.weight),0,1)';
        else
          $counts.='IF(ISNULL(Word'.$pos.'.weight),1,0)';
        $sums.='IFNULL(Word'.$pos.'.weight,0)';
        $tables='('.$tables.' LEFT JOIN Keywords as Word'.$pos.' ON Item.id=Word'.$pos.'.item AND Word'.$pos.'.word="'.$_STORAGE->escape($word).'")';
        $pos++;
      }
    }
    if ($pos==1)
    {
      $counts = '1';
      $sums = '1';
    }
    $where = array();
    if ($section !== null)
      $where[] = 'Item.section="'.$_STORAGE->escape($section->getId()).'"';
    if ($classes !== null)
    {
      $classpart = '(';
      $first = true;
      foreach ($classes as $class)
      {
        if (!$first)
          $classpart.=' OR ';
        $classpart.='Item.class="'.$_STORAGE->escape($class->getId()).'"';
        $first = false;
      }
      $where[] = $classpart.')';
    }
    if ($item !== null)
    {
      $items = array($item->getId() => $item);
      self::getSubitems($item, $items);
      $first = true;
      $ids = '';
      foreach ($items as $it)
      {
        if (!$first)
          $ids.=',';
        $ids.=$it->getId();
        $first = false;
      }
      $where[] = 'Item.id IN ('.$ids.')';
    }

    $items = array();

    $query = $_STORAGE->buildQuery(array('Item.id as item', $counts.' AS count', $sums.' AS sum'), $tables, $where, array('count DESC','sum DESC'));
    $results = $_STORAGE->query($query);
    while ($results->valid())
    {
      $details = $results->fetch();
      if ($details['count']==0)
        break;
      $items[] = Item::getItem($details['item']);
    }
    return $items;
  }
  
  private static function addKeyword($word, $weight, &$words)
  {
    if ((strlen($word)>1) && (strlen($word)<=30))
    {
      $thisweight = $weight*5;
      for ($start = 0; $start<strlen($word)-1; $start++)
      {
        for ($length = strlen($word)-$start; $length>1; $length--)
        {
          $part = substr($word, $start, $length);
          if (isset($words[$part]))
            $words[$part] += $thisweight;
          else
            $words[$part] = $thisweight;
          $thisweight = $weight;
        }
      }
    }
  }
  
  private static function extractKeywords($itemversion, &$words)
  {
    $fields = $itemversion->getFields();
    foreach ($fields as $field)
    {
      if ($field->isIndexed())
      {
        $text = $field->getPlainText();
        $text = strtolower($text);
        $text = str_replace('\'', '', $text);
        $text = str_replace('-', '', $text);
        $text = str_replace(' & ', ' and ', $text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $list = explode(' ', $text);
        foreach ($list as $word)
        {
          self::addKeyword($word, $field->getIndexPriority(), $words);
        }
      }
    }
  }
  
  public static function buildIndex()
  {
    global $_STORAGE,$_PREFS;
    
    $log = LoggerManager::getLogger('swim.search');
    
    $_STORAGE->queryExec('TRUNCATE Keywords;');
    
    $sections = FieldSetManager::getSections();
    foreach ($sections as $section)
    {
      if ($section->getType()=='contacts')
        continue;
      $log->info('Scanning '.$section->getName());
      $items = $section->getItems();
      foreach ($items as $item)
      {
        if (!$item->isArchived() && $item->getClass()->allowsLink())
        {
          $versioncount = 0;
          $words = array();
          $variants = $item->getVariants();
          foreach ($variants as $variant)
          {
            if (false)
            {
              $versions = $variant->getVersions();
              foreach ($versions as $version)
              {
                self::extractKeywords($version, $words);
                $versioncount++;
              }
            }
            else
            {
              $version = $variant->getCurrentVersion();
              if ($version !== null)
              {
                self::extractKeywords($version, $words);
                $versioncount++;
              }
            }
          }
          $log->info('Item '.$item->getId().' has '.$versioncount.' valid versions.');
          if ($versioncount>0)
          {
            foreach ($words as $word => $count)
            {
              $count = $count/$versioncount;
              $_STORAGE->queryExec('INSERT INTO Keywords (word,item,weight) VALUES ("'.$_STORAGE->escape($word).'",'.$item->getId().','.$count.');');
            }
          }
        }
      }
    }
  }
}

?>