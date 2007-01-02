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

function brand_get_template ($tpl_name, &$tpl_source, &$smarty)
{
  global $_PREFS;
  
  $file = $_PREFS->getPref('storage.branding.templates').'/'.$tpl_name;
  if (is_file($file))
  {
    $tpl_source = file_get_contents($file);
    return true;
  }
  else
    return false;
}

function brand_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
  global $_PREFS;
  
  $file = $_PREFS->getPref('storage.branding.templates').'/'.$tpl_name;
  if (is_file($file))
  {
    $tpl_timestamp = filemtime($file);
    return true;
  }
  else
    return false;
}

function brand_get_secure($tpl_name, &$smarty)
{
  return true;
}

function brand_get_trusted($tpl_name, &$smarty)
{
}


function shared_get_template ($tpl_name, &$tpl_source, &$smarty)
{
  global $_PREFS;
  
  $file = $_PREFS->getPref('storage.shared.templates').'/'.$tpl_name;
  if (is_file($file))
  {
    $tpl_source = file_get_contents($file);
    return true;
  }
  else
    return false;
}

function shared_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
  global $_PREFS;
  
  $file = $_PREFS->getPref('storage.shared.templates').'/'.$tpl_name;
  if (is_file($file))
  {
    $tpl_timestamp = filemtime($file);
    return true;
  }
  else
    return false;
}

function shared_get_secure($tpl_name, &$smarty)
{
  return true;
}

function shared_get_trusted($tpl_name, &$smarty)
{
}

?>