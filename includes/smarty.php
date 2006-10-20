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

require $_PREFS->getPref('storage.includes').'/smarty/header.php';
require $_PREFS->getPref('storage.includes').'/smarty/resources.php';
require $_PREFS->getPref('storage.includes').'/smarty/swimapi.php';
require $_PREFS->getPref('storage.includes').'/smarty/wrappers.php';

function sort_array($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['from'])))
  {
    $source = $params['from'];
    $order = true;
    if ((!empty($params['order'])) && ($params['order'] == 'descending'))
      $order = false;
    
    if (!empty($params['field']))
    {
      $source = ItemSorter::sortItems($source, $params['field'], $order);
    }
    else
    {
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
    }
  
    $smarty->assign_by_ref($params['var'], $source);
  }
}

function dynamic_section($params, $content, &$smarty, &$repeat)
{
  if (!$repeat)
    print($content);
}

function retrieve_rss($params, &$smarty)
{
  global $_PREFS;
  
  if ((!empty($params['items'])) && (!empty($params['src'])))
  {
    $source = '';
    $filename = $_PREFS->getPref('storage.sitecache').'/'.urlencode($params['src']);
    
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

function configureSmarty($smarty, $request, $type)
{
  global $_PREFS,$_USER;

  $log = LoggerManager::getLogger('page');

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
  $smarty->assign_by_ref('SECTIONS', new SectionManagerWrapper());
  $smarty->assign_by_ref('LOG', $log);
  $smarty->assign_by_ref('SMARTY', $smarty);
  $smarty->register_resource('brand', array(
                             'brand_get_template',
                             'brand_get_timestamp',
                             'brand_get_secure',
                             'brand_get_trusted'));
  $smarty->register_resource('shared', array(
                             'shared_get_template',
                             'shared_get_timestamp',
                             'shared_get_secure',
                             'shared_get_trusted'));
  $smarty->register_function('wrap', 'item_wrap');
  $smarty->register_function('getfiles', 'get_files');
  $smarty->register_function('meta', 'encode_meta');
  $smarty->register_function('link', 'encode_link');
  $smarty->register_function('retrieverss', 'retrieve_rss');
  $smarty->register_function('stylesheet', 'encode_stylesheet');
  $smarty->register_function('script', 'encode_script');
  $smarty->register_function('encode', 'encode_url');
  $smarty->register_function('apiget', 'api_get');
  $smarty->register_function('sort', 'sort_array');
  $smarty->register_function('search', 'search_items');
  $smarty->register_function('subitems', 'fetch_subitems');
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
  
  $log = LoggerManager::getLogger('page');
  $log->debug('Creating admin smarty.');
  
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
  $smarty->assign('SITECONTENT', $_PREFS->getPref('url.site.static'));
  $smarty->assign('BRAND', $_PREFS->getPref('url.branding.static'));
  
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