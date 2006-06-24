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
    return $result;
  }
}

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

function get_params_request(&$params, $smarty)
{
  if (!empty($params['href']))
  {
    $request = $params['href'];
    unset($params['href']);
    if (count($params)>0)
    {
      $query = '';
      foreach ($params as $key => $value)
        $query.='&'.urlencode($key).'='.urlencode($value);
      if (strpos($request, '?')!==false)
        $request.=$query;
      else
        $query.='?'.substr($query, 0);
    }
  }
  else
  {
    $request = new Request();
    if (empty($params['method']))
      $request->setMethod('view');
    else
      $request->setMethod($params['method']);
    if (!empty($params['path']))
      $request->setPath($params['path']);
    if ((!empty($params['nestcurrent'])) && ($params['nestcurrent'] == "true"))
      $request->setNested($smarty->get_template_vars('REQUEST'));
    unset($params['method']);
    unset($params['path']);
    unset($params['nestcurrent']);
    $request->setQueryVars($params);
  }
  
  return $request;
}

function check_security($params, $content, &$smarty, &$repeat)
{
  global $_USER;
  
  $valid = false;
  if ($_USER->isLoggedIn())
  {
    $valid = true;
    foreach ($params as $section => $types)
    {
      $list = explode(',', $types);
      foreach ($list as $type)
      {
        if (!$_USER->hasPermission($section, $type))
          $valid=false;
      }
    }
  }
  
  if (($valid) && (!$repeat))
    print($content);
  else if ((!$valid) && ($repeat))
    displayAdminLogin($smarty->get_template_vars('REQUEST'));
}

function encode_url($params, &$smarty)
{
  $request = get_params_request($params, $smarty);
  if ($request instanceof Request)
    return $request->encode();
  else
    return $request;
}

function dynamic_section($params, $content, &$smarty, &$repeat)
{
  if (!$repeat)
    print($content);
}

function encode_form($params, $content, &$smarty, &$repeat)
{
  if ($repeat)
  {
    $method = "POST";
    if (!empty($params['formmethod']))
      $method = $params['formmethod'];
    unset($params['formmethod']);
    $request = get_params_request($params, $smarty);
    if ($request instanceof Request)
    {
      $path = $request->encodePath();
      $vars = $request->getFormVars();
    }
    else
    {
      $path = $request;
      $vars = '';
    }
    print('<form method="'.$method.'" action="'.$path.'"');
    foreach ($params as $key => $value)
      print(' '.$key.'="'.$value.'"');
    print('>');
    print($vars);
  }
  else
  {
    print($content);
    print('</form>');
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

function api_get($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['type'])))
  {
    if (!empty($params['id']))
    {
      $result = null;
      if ($params['type']=='user')
        $result = UserManager::getUser($params['id']);
      else if ($params['type']=='group')
        $result = UserManager::getGroup($params['id']);
      else if ($params['type']=='item')
        $result = Item::getItem($params['id']);
      $smarty->assign_by_ref($params['var'], $result);
      return "";
    }
    else
    {
      $result = null;
      if ($params['type']=='user')
        $result = UserManager::getAllUsers();
      else if ($params['type']=='group')
        $result = UserManager::getAllGroups();
      $smarty->assign_by_ref($params['var'], $result);
      return "";
    }
  }
  else
  {
    return "Not enough parameters";
  }
}

function configureSmarty($smarty, $request, $type)
{
  global $_PREFS,$_USER;

  $log = LoggerManager::getLogger('page');
  $log->debug('Creating admin smarty.');
  
  $req = array();
  $req['method'] = $request->getMethod();
  $req['path'] = $request->getPath();
  $req['query'] = $request->getQuery();
  $smarty->assign_by_ref('USER', $_USER);
  $smarty->assign_by_ref('REQUEST', $request);
  $smarty->assign_by_ref('request', $req);
  $smarty->assign_by_ref('NESTED', $request->getNested());
  $smarty->assign_by_ref('PREFS', $_PREFS);
  $smarty->assign_by_ref('LOG', $log);
  $smarty->register_resource('brand', array(
                             'brand_get_template',
                             'brand_get_timestamp',
                             'brand_get_secure',
                             'brand_get_trusted'));
  $smarty->register_function('stylesheet', 'encode_stylesheet');
  $smarty->register_function('script', 'encode_script');
  $smarty->register_function('encode', 'encode_url');
  $smarty->register_function('apiget', 'api_get');
  $smarty->register_function('dynamic', 'dynamic_section', false);
  $smarty->register_block('html_form', 'encode_form');
  $smarty->register_block('secure', 'check_security');
  $smarty->register_object('HEAD', new HtmlHeader());
  $smarty->register_outputfilter('header_outputfilter');

  if ($type == 'text/css')
  {
    $smarty->left_delimiter = '[';
    $smarty->right_delimiter = ']';
  }
}

function createAdminSmarty($request, $type)
{
  global $_PREFS,$_USER;
  
  require_once($_PREFS->getPref('storage.smarty').'/Smarty.class.php');
  $smarty = new Smarty();
  $smarty->template_dir = $_PREFS->getPref('storage.admin.templates');
  $smarty->compile_dir = $_PREFS->getPref('storage.admin.compiled');
  $smarty->config_dir = $_PREFS->getPref('storage.admin.config');
  $smarty->cache_dir = $_PREFS->getPref('storage.admin.cache');
  recursiveMkDir($smarty->compile_dir);
  recursiveMkDir($smarty->cache_dir);

  configureSmarty($smarty, $request, $type);
  $smarty->assign('CONTENT', $_PREFS->getPref('storage.admin.static'));
  $smarty->assign('BRAND', $_PREFS->getPref('storage.branding.static'));
  
  /*if (($type == 'text/css') || ($type == 'text/javascript'))
    $smarty->caching = true;*/

  return $smarty;
}

function createSmarty($request, $type)
{
  global $_PREFS,$_USER;
  
  $log = LoggerManager::getLogger('page');
  $log->debug('Creating smarty.');
  
  require_once($_PREFS->getPref('storage.smarty').'/Smarty.class.php');
  $smarty = new Smarty();
  $smarty->template_dir = $_PREFS->getPref('storage.site.templates');
  $smarty->compile_dir = $_PREFS->getPref('storage.site.compiled');
  $smarty->config_dir = $_PREFS->getPref('storage.site.config');
  $smarty->cache_dir = $_PREFS->getPref('storage.site.cache');
  recursiveMkDir($smarty->compile_dir);
  recursiveMkDir($smarty->cache_dir);

  configureSmarty($smarty, $request, $type);
  $smarty->assign('CONTENT', $_PREFS->getPref('storage.site.static'));
  
  //$smarty->caching = true;

  return $smarty;
}

?>