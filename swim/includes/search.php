<?

/*
 * Swim
 *
 * SWIM search code
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class SearchEngine
{
  public static function extractKeywords($itemversion, &$words)
  {
    $fields = $itemversion->getFields();
    foreach ($fields as $field)
    {
      if ($field->isIndexed())
      {
        $text = $field->getPlainText();
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $list = explode(' ', $text);
        foreach ($list as $word)
        {
          if (strlen($word)>3)
          {
            if (isset($words[$word]))
              $words[$word] += $field->getIndexPriority();
            else
              $words[$word] = $field->getIndexPriority();
          }
        }
      }
    }
  }
  
  public static function buildIndex()
  {
    global $_STORAGE,$_PREFS;
    
    $log = LoggerManager::getLogger('swim.search');
    
    $_STORAGE->queryExec('TRUNCATE Keywords;');
    
    $sections = SectionManager::getSections();
    foreach ($sections as $section)
    {
      $log->info('Scanning '.$section->getName());
      $items = $section->getItems();
      foreach ($items as $item)
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

?>