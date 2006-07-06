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

class ItemWrapper
{
  private $itemversion;
  
  public function __construct($itemversion)
  {
    $this->itemversion = $itemversion;
  }
  
  public function __get($name)
  {
    switch($name)
    {
      case 'modified':
        return $this->itemversion->getModified();
        break;
      case 'item':
        return $this->itemversion;
        break;
      case 'section':
        return $this->itemversion->getItem()->getSection()->getId();
        break;
      case 'class':
        return $this->itemversion->getClass()->getId();
        break;
      case 'view':
        return $this->itemversion->getView()->getId();
        break;
      case 'author':
        return $this->itemversion->getOwner()->getName();
        break;
      case 'version':
        return $this->itemversion->getVersion();
        break;
      case 'parent':
        $parents = $this->itemversion->getItem()->getMainParents();
        if (count($parents)>0)
          return new ItemWrapper($parents[0]);
        return null;
        break;
      case 'url':
        $target = $this->itemversion->getLinkTarget();
        if ($target == null)
          $target = $this->itemversion;
        $req = new Request();
        $req->setMethod('view');
        $req->setPath($target->getItem()->getId());
        return $req->encode();
        break;
      default:
        $field = $this->itemversion->getField($name);
        if ($field != null)
        {
          if ($field->getType() == 'sequence')
          {
            $result = array();
            $items = $field->getItems();
            foreach ($items as $item)
            {
              $itemv = $item->getCurrentVersion(Session::getCurrentVariant());
              if ($itemv != null)
              {
                $wrapped = new ItemWrapper($itemv);
                array_push($result, $wrapped);
              }
            }
            return $result;
          }
          else
            return $field->toString();
        }
        return '';
    }
  }
}

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
    $result.='<meta name="generator" content="SWIM 3.0">'."\n";
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

function sort_array($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['from'])))
  {
    $source = $params['from'];
    $order = true;
    if ((!empty($params['order'])) && ($params['order'] == 'descending'))
      $order = false;
      
    $index = true;
    if ((!empty($params['index'])) && ($params['index'] == 'key'))
    {
      if ($order)
        ksort($source);
      else
        krsort($source);
    }
    else
    {
      if ($order)
        asort($source);
      else
        arsort($source);
    }
  
    $smarty->assign($params['var'], $source);
  }
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
  else if ((!$valid) && ($repeat) && (isset($params['login'])) && ($params['login'] == 'true'))
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
    $attrs = '';
    foreach(array_keys($params) as $key)
    {
      if (substr($key,0,4) == 'tag_')
      {
        $attrs.=substr($key,4).'="'.$params[$key].'" ';
        unset($params[$key]);
      }
    }
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
    print('<form '.$attrs.'method="'.$method.'" action="'.$path.'"');
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

function retrieve_rss($params, &$smarty)
{
  global $_PREFS;
  
  if ((!empty($params['items'])) && (!empty($params['src'])))
  {
    $source = '';
    $filename = $_PREFS->getPref('storage.cache').'/'.urlencode($params['src']);
    
    $handle = @fopen($params['src'], 'r');
    if ($handle !== FALSE)
    {
      $source = stream_get_contents($handle);
      fclose($handle);
      file_put_contents($filename, $source);
    }
    else if (is_readable($filename))
      $source = file_get_contents($filename);
    $xml = new DOMDocument('1.0', 'UTF8');
    $xml->loadXML($source);
    $items = array();
    $meta = array();
    $meta['type'] = 'unknown';
    if ($xml->documentElement->tagName == 'rss')
    {
      if ($xml->documentElement->getAttribute('version') == '2.0')
      {
        $meta['type'] = 'rss2';
        $channel = $xml->documentElement->firstChild;
        while ($channel !== null)
        {
          if (($channel->nodeType == 1) && ($channel->tagName == 'channel'))
          {
            $data = $channel->firstChild;
            while ($data !== null)
            {
              if ($data->nodeType == 1)
              {
                switch ($data->tagName)
                {
                  case 'item':
                    $itemdata = array();
                    $item = $data->firstChild;
                    while ($item !== null)
                    {
                      if ($item->nodeType == 1)
                      {
                        switch ($item->tagName)
                        {
                          case 'title':
                          case 'link':
                          case 'description':
                            $itemdata[$item->tagName] = getDOMText($item);
                            break;
                          case 'pubDate':
                            $itemdata['date'] = getDOMText($item);
                            break;
                        }
                      }
                      $item = $item->nextSibling;
                    }
                    array_push($items, $itemdata);
                    break;
                  case 'title':
                  case 'description':
                  case 'copyright':
                  case 'ttl':
                  case 'language':
                    $meta[$data->tagName] = getDOMText($data);
                    break;
                }
              }
              $data = $data->nextSibling;
            }
          }
          $channel = $channel->nextSibling;
        }
      }
    }
    $smarty->assign_by_ref($params['items'], $items);
    if (isset($params['metadata']))
      $smarty->assign_by_ref($params['metadata'], $meta);
  }
  else
  {
    return "Not enough parameters";
  }
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
      else if ($params['type']=='section')
        $result = SectionManager::getSection($params['id']);
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

function item_wrap($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['item'])))
  {
    $item = $params['item'];
    if ($item instanceof Item)
      $item = $item->getCurrentVersion(Session::getCurrentVariant());
    else if ($item instanceof ItemVariant)
      $item = $item->getCurrentVersion();
    else if (!($item instanceof ItemVersion))
      return "Invalid item specified";
    $smarty->assign_by_ref($params['var'], new ItemWrapper($item));
  }
  else
  {
    return "Not enough parameters";
  }
}

function get_files($params, &$smarty)
{
  global $_STORAGE,$_PREFS;
  
  if (isset($params['var']))
  {
    if (isset($params['itemversion']))
    {
      $item = Item::getItemVersion($params['itemversion']);
      $iv = $params['itemversion'];
      $path = $item->getStoragePath();
      $url = $item->getStorageUrl();
    }
    else
    {
      $iv = -1;
      $path = $_PREFS->getPref('storage.site.attachments');
      $url = $_PREFS->getPref('url.site.attachments');
    }
    $files = array();
    $dir = opendir($path);
    while (($file = readdir($dir)) !== false)
    {
      if (is_file($path.'/'.$file))
      {
        $results = $_STORAGE->query('SELECT * FROM File WHERE itemversion='.$iv.' AND file="'.$_STORAGE->escape($file).'";');
        if ($results->valid())
        {
          $fl = $results->fetch();
          $fl['name'] = $file;
          unset($fl['itemversion']);
          unset($fl['file']);
        }
        else
        {
          $fl = array('name' => $file);
          $fl['description'] = '';
        }
        $fl['size'] = filesize($path.'/'.$file);
        $fl['type'] = determineContentType($path.'/'.$file);
        if (strpos($file, '.')!==false)
        {
          $fl['extension'] = substr($file, strpos($file, '.')+1);
        }
        else
          $fl['extension'] = 'unknown';
        $fl['path'] = $url.'/'.$file;
        $files[$file] = $fl;
      }
    }
    closedir($dir);
    $smarty->assign_by_ref($params['var'], $files);
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
  $smarty->assign('session', $_SESSION['data']);
  $smarty->assign('SHARED', $_PREFS->getPref('url.shared'));
  $smarty->assign_by_ref('SERVER', $_SERVER);
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
  $smarty->register_function('wrap', 'item_wrap');
  $smarty->register_function('getfiles', 'get_files');
  $smarty->register_function('retrieverss', 'retrieve_rss');
  $smarty->register_function('stylesheet', 'encode_stylesheet');
  $smarty->register_function('script', 'encode_script');
  $smarty->register_function('encode', 'encode_url');
  $smarty->register_function('apiget', 'api_get');
  $smarty->register_function('sort', 'sort_array');
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
  else if ($type == 'text/javascript')
  {
    $smarty->left_delimiter = '{[';
    $smarty->right_delimiter = ']}';
  }
}

function createAdminSmarty($request, $type = 'text/html')
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
  $smarty->assign('CONTENT', $_PREFS->getPref('url.admin.static'));
  $smarty->assign('BRAND', $_PREFS->getPref('storage.branding.static'));
  
  /*if (($type == 'text/css') || ($type == 'text/javascript'))
    $smarty->caching = true;*/

  return $smarty;
}

function createSmarty($request, $type = 'text/html')
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
  $smarty->assign('CONTENT', $_PREFS->getPref('url.site.static'));
  
  //$smarty->caching = true;

  return $smarty;
}

?>