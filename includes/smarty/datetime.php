<?

/*
 * Swim
 *
 * Smarty date and time functions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL: svn://svn.blueprintit.co.uk/blueprintit/BlueprintIT/Swim/trunk/includes/smarty/header.php $
 * $LastChangedBy: dave $
 * $Date: 2007-05-01 11:56:16 +0100 (Tue, 01 May 2007) $
 * $Revision: 1468 $
 */

function datetime_mktime($params, &$smarty)
{
  if (!empty($params['var']))
  {
    if (!empty($params['hour']))
      $hour = $params['hour'];
    else
      $hour = 0;
    if (!empty($params['minute']))
      $minute = $params['minute'];
    else
      $minute = 0;
    if (!empty($params['second']))
      $second = $params['second'];
    else
      $second = 0;
    if (!empty($params['month']))
      $month = $params['month'];
    else
      $month = 1;
    if (!empty($params['day']))
      $day = $params['day'];
    else
      $day = 1;
    if (!empty($params['year']))
      $year = $params['year'];
    else
      $year = 0;
    $smarty->assign($params['var'], mktime($hour, $minute, $second, $month, $day, $year, -1));
  }
}

function datetime_sequence($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['start'])) && ((!empty($params['end'])) || (!empty($params['count']))))
  {
    $results = array();
    $start = $params['start'];
    if (empty($params['step']))
      $step = "+1 second";
    else
      $step = $params['step'];
    $count = 0;
    if (!empty($params['count']))
      $maxcount = $params['count'];
    else
      $maxcount = -1;
    if (!empty($params['end']))
      $end = $params['end'];
    else
      $end = null;
    $pos = $start;
    
    while (($count != $maxcount) && (($end === null) || ((($end<$start) || ($pos<$end)) && (($start<$end) || ($pos>$end)))))
    {
      array_push($results, $pos);
      $pos = strtotime($step, $pos);
      $count++;
    }
    $smarty->assign_by_ref($params['var'], $results);
  }
}

?>
