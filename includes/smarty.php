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

require $_PREFS->getPref('storage.includes').'/smarty/header.php';
require $_PREFS->getPref('storage.includes').'/smarty/resources.php';
require $_PREFS->getPref('storage.includes').'/smarty/swimapi.php';
require $_PREFS->getPref('storage.includes').'/smarty/wrappers.php';

function output_subtemplate($params, &$smarty)
{
  global $_PREFS;
  
  if (!empty($params['file']))
  {
    $request = new Request();
    $file = $params['file'];
    unset($params['file']);
    foreach ($params as $name => $value)
    {
      $request->setQueryVar($name, $value);
    }
    $template = findDisplayableFile($_PREFS->GetPref('storage.site.templates').'/'.$file);
    $type = determineContentType($template);
    $smarty = createSmarty($request, $type);
    $result = $smarty->display($template);
  }
}

function output_file($params, &$smarty)
{
  global $_PREFS;
  
  if (!empty($params['path']))
  {
    $path = $_PREFS->getPref('url.shared');
    if (substr($params['path'], 0, strlen($path)) == $path)
    {
      $file = $_PREFS->getPref('storage.shared').substr($params['path'], strlen($path));
      if (is_file($file))
      {
        readfile($file);
        return;
      }
    }

    $path = $_PREFS->getPref('url.site.static');
    if (substr($params['path'], 0, strlen($path)) == $path)
    {
      $file = $_PREFS->getPref('storage.site.static').substr($params['path'], strlen($path));
      if (is_file($file))
      {
        readfile($file);
        return;
      }
    }

    $path = $_PREFS->getPref('url.admin.static');
    if (substr($params['path'], 0, strlen($path)) == $path)
    {
      $file = $_PREFS->getPref('storage.admin.static').substr($params['path'], strlen($path));
      if (is_file($file))
      {
        readfile($file);
        return;
      }
    }
  }
}

function array_select($params, &$smarty)
{
  if ((!empty($params['var'])) && (!empty($params['from'])))
  {
    $source = $params['from'];
    
    if (count($source)==0)
    {
    	$source = array();
    	$smarty->assign_by_ref($params['var'], $source);
    	return;
    }
    
    $order = true;
    if ((!empty($params['order'])) && ($params['order'] == 'descending'))
      $order = false;
    
    if (!empty($params['field']))
      $field = $params['field'];
    else
      $field = null;
    
    if (!empty($params['maxcount']))
      $maxcount = $params['maxcount'];
    else
      $maxcount = null;
    
    if (!empty($params['min']))
      $min = $params['min'];
    else
      $min = null;
    
    if (!empty($params['max']))
      $max = $params['max'];
    else
      $max = null;
    
    $source = ItemSorter::selectItems($source, $field, $order, $maxcount, $min, $max);

    $smarty->assign_by_ref($params['var'], $source);
  }
}

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

function paginate($params, &$smarty)
{
	if ((!empty($params['items'])) && (!empty($params['max'])))
	{
		$pages = ceil(count($params['items'])/$params['max']);
		if (!empty($params['selected']))
			$page = min($pages,$params['selected']);
		else
			$page = 1;
		if (!empty($params['pages']))
			$smarty->assign($params['pages'], $pages);
		if (!empty($params['start']))
			$smarty->assign($params['start'], ($page-1)*$params['max']);
		if (!empty($params['page']))
			$smarty->assign($params['page'], $page);
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

function summarise_html($value, $count = 1)
{
  $pos = 0;
  while ($count > 0)
  {
    $npos = strpos($value, '</p>', $pos);
    if ($npos === false)
      break;
    $pos = $npos + 4;
    $count--;
  }
  $result = substr($value, 0, $pos);
  $opens = substr_count($result, '<div');
  $closes = substr_count($result, '</div>');
  $extra = $opens - $closes;
  if ($extra > 0)
  {
    for ($i = 0; $i < $extra; $i++)
      $result .= '</div>';
  }
  else if ($extra < 0)
  {
    for ($i = 0; $i > $extra; $i--)
      $result = '<div>'.$result;
  }
  return $result;
}

function htmltotext($value, $width = 78)
{
  $search = array('<li>');
  $replaces = array('* ');
  $blocks = array('/<\/?h[123456]>/',
                  '/<\/?(?:p|br|ol|ul)>/',
                  '/<\/?li>/');
  
  $value = preg_replace('/\s\s+/', ' ', $value);
  $value = str_replace($search, $replaces, $value);
  $value = html_entity_decode($value, ENT_QUOTES, "UTF-8");
  $value = preg_replace('/&#(\d+);/e', "chr($1)", $value);
  $value = preg_replace($blocks, "\n", $value);
  $value = preg_replace('/<\/?[^>]*>/', '', $value);
  $value = trim($value);
  
  $result = '';
  $lstart = 0;
  while ($lstart < strlen($value))
  {
    $lend = strpos($value, "\n", $lstart);
    if ($lend === false)
      $lend = strlen($value);
    
    if (($lend - $lstart) > $width)
      $result .= wordwrap(substr($value, $lstart, $lend - $lstart), $width) . "\n";
    else
      $result .= substr($value, $lstart, $lend - $lstart) . "\n";
    $lstart = $lend + 1;
  }
  
  $value = preg_replace('/\n\s+\n/', "\n\n", trim($result));
  return $value;
}

function configureSmarty($smarty, $request, $type)
{
  global $_PREFS;

  $log = LoggerManager::getLogger('page');

  if ($request !== null)
  {
    $req = array();
    $req['method'] = $request->getMethod();
    $req['path'] = $request->getPath();
    $req['query'] = $request->getQuery();
    $smarty->assign_by_ref('REQUEST', $request);
    $smarty->assign_by_ref('request', $req);
    $smarty->assign_by_ref('NESTED', $request->getNested());
  }
  $smarty->assign('SHARED', $_PREFS->getPref('url.shared'));
  $smarty->assign_by_ref('SERVER', $_SERVER);
  $smarty->assign_by_ref('PREFS', $_PREFS);
  $smarty->assign_by_ref('SECTIONS', new SectionsWrapper());
  $smarty->assign_by_ref('LOG', $log);
  $smarty->assign_by_ref('SMARTY', $smarty);
  $smarty->register_resource('brand', array(
                             new SmartyResource($_PREFS->getPref('storage.branding.templates')),
                             'getTemplate',
                             'getTimestamp',
                             'getSecure',
                             'getTrusted'));
  $smarty->register_resource('shared', array(
                             new SmartyResource($_PREFS->getPref('storage.shared.templates')),
                             'getTemplate',
                             'getTimestamp',
                             'getSecure',
                             'getTrusted'));
  $smarty->register_function('wrap', 'item_wrap');
  $smarty->register_function('getfiles', 'get_files');
  $smarty->register_function('retrieverss', 'retrieve_rss');
  $smarty->register_function('encode', 'encode_url');
  $smarty->register_function('apiget', 'api_get');
  $smarty->register_function('sort', 'sort_array');
  $smarty->register_function('select', 'array_select');
  $smarty->register_function('search', 'search_items');
  $smarty->register_function('subitems', 'fetch_subitems');
  $smarty->register_function('dynamic', 'dynamic_section', false);
  $smarty->register_function('paginate', 'paginate');
  $smarty->register_function('request', 'generate_request');
  $smarty->register_function('outputfile', 'output_file');
  $smarty->register_function('subtemplate', 'output_subtemplate');
  $smarty->register_block('html_form', 'encode_form');
  $smarty->register_block('secure', 'check_security');
  $smarty->register_modifier('summarise', 'summarise_html');
  $smarty->register_modifier('plaintext', 'htmltotext');

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
  else if ($type == 'text/html')
  {
    $header = new HtmlHeader();
    $smarty->register_object('HEAD', $header);
    $smarty->register_function('meta', array($header, 'encodeMeta'));
    $smarty->register_function('link', array($header, 'encodeLink'));
    $smarty->register_function('stylesheet', array($header, 'encodeStylesheet'));
    $smarty->register_function('script', array($header, 'encodeScript'));
    $smarty->register_block('style', array($header, 'encodeStyle'));
    $smarty->register_outputfilter(array($header, 'outputfilter'));
  }
}

function createAdminSmarty($request, $type = 'text/html')
{
  global $_PREFS;
  
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
  $smarty->assign_by_ref('USER', Session::getUser());
  $smarty->assign('CONTENT', $_PREFS->getPref('url.admin.static'));
  $smarty->assign('SITECONTENT', $_PREFS->getPref('url.site.static'));
  $smarty->assign('BRAND', $_PREFS->getPref('url.branding.static'));
  
  /*if (($type == 'text/css') || ($type == 'text/javascript'))
    $smarty->caching = true;*/

  return $smarty;
}

function createSmarty($request, $type = 'text/html')
{
  global $_PREFS;
  
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

function createMailSmarty($type = 'text/html')
{
  return createSmarty(null, $type);
}

?>