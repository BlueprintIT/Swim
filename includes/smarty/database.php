<?

/*
 * Swim
 *
 * Smarty database functions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/includes/smarty/header.php $
 * $LastChangedBy: dave $
 * $Date: 2007-05-01 11:56:16 +0100 (Tue, 01 May 2007) $
 * $Revision: 1468 $
 */

function database_exec($params, &$smarty)
{
  global $_STORAGE;
  
  if (!empty($params['query'))
  {
    $_STORAGE->queryExec($params['query']);
    if (!empty($params['var']))
      $smarty->assign($params['var'], $_STORAGE->changes());
  }
}

function database_query($params, &$smarty)
{
  global $_STORAGE;
  if ((!empty($params['var'])) && (!empty($params['query'])))
  {
    $results = $_STORAGE->arrayQuery($params['query']);
    $smarty->assign_by_ref($params['var'], $results);
  }
}

?>
