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

class HtmlHeader
{
  private $stylesheets = array();
  private $scripts = array();
  private $headers = array();
  
  public function addStyleSheet($path)
  {
    if (!isset($this->stylesheets[$path]))
      $this->stylesheets[$path]=true;
  }
  
  public function addScript($path)
  {
    if (!isset($this->scripts[$path]))
      $this->scripts[$path]=true;
  }
  
  public function addMeta($name, $content)
  {
    array_push($this->headers, '<meta name="'.$name.'" content="'.$content.'">');
  }
  
  public function addLink($params)
  {
    if (count($params)>0)
    {
      $line = '<link';
      foreach ($params as $name => $value)
      {
        $line.=' '.$name.'="'.$value.'"';
      }
      $line.='>';
      array_push($this->headers, $line);
    }
  }
  
  public function getHtml()
  {
    $result = '';
    foreach ($this->stylesheets as $path => $val)
    {
      $result.='<link rel="stylesheet" href="'.$path.'" type="text/css">'."\n";
    }
    foreach ($this->scripts as $path => $val)
    {
      $result.='<script src="'.$path.'" type="text/javascript"></script>'."\n";
    }
    foreach ($this->headers as $line)
    {
      $result.=$line."\n";
    }
    $result.='<meta name="generator" content="SWIM 3.0">'."\n";
    return $result;
  }
}

function encode_stylesheet($params, &$smarty)
{
  $request = get_params_request($params, $smarty);
  if ($request instanceof Request)
    $path = $request->encode();
  else
    $path = $request;
  $head = $smarty->get_registered_object('HEAD');
  $head->addStyleSheet($path);
}

function encode_script($params, &$smarty)
{
  $request = get_params_request($params, $smarty);
  if ($request instanceof Request)
    $path = $request->encode();
  else
    $path = $request;
  $head = $smarty->get_registered_object('HEAD');
  $head->addScript($path);
}

function encode_meta($params, &$smarty)
{
  if (!empty($params['name']) && !empty($params['content']))
  {
    $head = $smarty->get_registered_object('HEAD');
    $head->addMeta($params['name'], $params['content']);
  }
}

function encode_link($params, &$smarty)
{
  $tparams = array();
  foreach ($params as $name => $value)
  {
    if (substr($name,0,4)=='tag_')
    {
      unset($params[$name]);
      $tparams[substr($name,4)] = $value;
    }
  }
  $request = get_params_request($params, $smarty);
  if ($request instanceof Request)
    $tparams['href'] = $request->encode();
  else
    $tparams['href'] = $request;
  $head = $smarty->get_registered_object('HEAD');
  $head->addLink($tparams);
}

function header_outputfilter($tpl_output, &$smarty)
{
  $pos = strpos($tpl_output, '</head>');
  if ($pos !== false)
  {
    $start = substr($tpl_output, 0, $pos);
    $end = substr($tpl_output, $pos);
    $head = $smarty->get_registered_object('HEAD');
    $extra = $head->getHtml();
    return $start.$extra.$end;
  }
  return $tpl_output;
}

?>