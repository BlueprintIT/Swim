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

class SmartyResource
{
  private $source;
  
  public function __construct($source)
  {
    $this->source = $source;
  }

  function getTemplate($tpl_name, &$tpl_source, &$smarty)
  {
    $file = $this->source.'/'.$tpl_name;
    if (is_file($file))
    {
      $tpl_source = file_get_contents($file);
      return true;
    }
    else
      return false;
  }
  
  function getTimestamp($tpl_name, &$tpl_timestamp, &$smarty)
  {
    $file = $this->source.'/'.$tpl_name;
    if (is_file($file))
    {
      $tpl_timestamp = filemtime($file);
      return true;
    }
    else
      return false;
  }
  
  function getSecure($tpl_name, &$smarty)
  {
    return true;
  }
  
  function getTrusted($tpl_name, &$smarty)
  {
  }
}

?>