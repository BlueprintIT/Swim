<?

/*
 * Swim
 *
 * Tests for resource caches
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function logTest($id,$desc,$comparison)
{
  global $log;
  
  if ($comparison)
  {
    $log->debug('Passed test '.$id.': '.$desc);
  }
  else
  {
    $log->error('Failed test '.$id.': '.$desc);
  }
}

$container=getContainer('global');
$cont2=getContainer('global');

logTest('1','Duplicate comparison',$container===$cont2);

$pages=$container->getResources('page');
foreach($pages as $page)
{
  $page2=Resource::decodeResource($page->getPath());
  logTest('2','Page cache and foreach',$page2===$page);
  break;
}

?>
