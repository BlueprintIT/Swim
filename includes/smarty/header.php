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

class HtmlHeader
{
  private $stylesheets = array();
  private $scripts = array();
  private $headers = array();
  private $styles = array();
  
  public function addStyleSheet($path, $media = null)
  {
    if (!isset($this->stylesheets[$path]))
      $this->stylesheets[$path]=$media;
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
  
  public function addStyle($content)
  {
  	array_push($this->styles, $content);
  }
  
  private function getHtml()
  {
    $result = '';
    if (false)   // Need to fix media handling
    {
	    if (count($this->stylesheets)>0)
	    {
	    	$stylereq = new Request();
	    	$stylereq->setMethod('combine');
	    	$paths = array();
		    foreach ($this->stylesheets as $path => $val)
		    {
		    	array_push($paths, $path);
		    }
		    $stylereq->setQueryVar('type', 'text/css');
		    $stylereq->setQueryVar('paths', $paths);
	      $result.='<link rel="stylesheet" href="'.$stylereq->encode().'" type="text/css">'."\n";
	    }
    }
    else
    {
	    foreach ($this->stylesheets as $path => $val)
	    {
        if ($val !== null)
          $media = ' media="'.$val.'"';
        else
          $media = '';
	      $result.='<link rel="stylesheet" href="'.$path.'"'.$media.' type="text/css">'."\n";
	    }
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
    if (count($this->styles)>0)
    {
    	$result.="<style type=\"text/css\">\n";
	    foreach ($this->styles as $style)
	    	$result.=$style;
	    $result.="</style>\n";
    }
    return $result;
  }

  public function encodeStylesheet($params, &$smarty)
  {
    if (isset($params['tag_media']))
      $media = $params['tag_media'];
    else
      $media = null;
    unset($params['tag_media']);
    $request = get_params_request($params, $smarty);
    if ($request instanceof Request)
      $path = $request->encode();
    else
      $path = $request;
    $this->addStyleSheet($path,$media);
  }
  
  public function encodeScript($params, &$smarty)
  {
    $request = get_params_request($params, $smarty);
    if ($request instanceof Request)
      $path = $request->encode();
    else
      $path = $request;
    $this->addScript($path);
  }
  
  public function encodeMeta($params, &$smarty)
  {
    if (!empty($params['name']) && !empty($params['content']))
      $this->addMeta($params['name'], $params['content']);
  }
  
  public function encodeLink($params, &$smarty)
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
    $this->addLink($tparams);
  }
  
  public function encodeStyle($params, $content, &$smarty, &$repeat)
  {
    if (!$repeat)
      $this->addStyle($content);
  }
  
  public function outputfilter($tpl_output, &$smarty)
  {
    $pos = strpos($tpl_output, '</head>');
    if ($pos !== false)
    {
      $start = substr($tpl_output, 0, $pos);
      $end = substr($tpl_output, $pos);
      $extra = $this->getHtml();
      return $start.$extra.$end;
    }
    return $tpl_output;
  }
}

?>