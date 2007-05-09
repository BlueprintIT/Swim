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
    else if (!empty($params['nested']))
      $request->setNested($params['nested']);
    unset($params['method']);
    unset($params['path']);
    unset($params['nested']);
    unset($params['nestcurrent']);
    $request->setQueryVars($params);
  }
  
  return $request;
}

function check_security($params, $content, &$smarty, &$repeat)
{
  $user = Session::getUser();
  $valid = false;
  if ($user->isLoggedIn())
  {
    $valid = true;
    foreach ($params as $section => $types)
    {
      if ($section != 'login')
      {
        $list = explode(',', $types);
        foreach ($list as $type)
        {
          if (!$user->hasPermission($section, $type))
            $valid=false;
        }
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
    return htmlspecialchars($request->encode());
  else
    return htmlspecialchars($request);
}

function encode_form($params, $content, &$smarty, &$repeat)
{
  if ($repeat)
  {
    $method = 'method="POST" ';
    $attrs = '';
    foreach(array_keys($params) as $key)
    {
      if (substr($key,0,4) == 'tag_')
      {
      	if ($key == 'tag_method')
      		$method = 'method="'.$params[$key].'" ';
        $attrs.=substr($key,4).'="'.$params[$key].'" ';
        unset($params[$key]);
      }
    }
    if (!empty($params['formmethod']))
      $method = 'method="'.$params['formmethod'].'" ';
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
    print('<form '.$attrs.$method.'action="'.htmlspecialchars($path).'">');
    print($vars);
  }
  else
  {
    print($content);
    print('</form>');
  }
}

function generate_request($params, &$smarty)
{
  if (!empty($params['var']))
  {
    $var = $params['var'];
    unset($params['var']);
    $request = get_params_request($params, $smarty);
    if ($request instanceof Request)
      $smarty->assign_by_ref($var, $request);
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
        $result = FieldSetManager::getSection($params['id']);
      else if ($params['type']=='class')
        $result = FieldSetManager::getClass($params['id']);
      else if ($params['type']=='view')
        $result = FieldSetManager::getView($params['id']);
      else if ($params['type']=='optionset')
        $result = FieldSetManager::getOptionSet($params['id']);
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
      else if ($params['type']=='section')
        $result = FieldSetManager::getSections();
      else if ($params['type']=='optionset')
        $result = FieldSetManager::getOptionSets();
      $smarty->assign_by_ref($params['var'], $result);
      return "";
    }
  }
  else
  {
    return "Not enough parameters";
  }
}

function search_items($params, &$smarty)
{
  if (isset($params['var']) && isset($params['query']))
  {
    $section = null;
    if (isset($params['section']))
      $section = FieldSetManager::getSection($params['section']);
    $classes = null;
    if (isset($params['classes']))
    {
      $classes = array();
      foreach (explode(',',$params['classes']) as $classname)
      {
        $class = FieldSetManager::getClass($classname);
        if ($class !== null)
          $classes[] = $class;
      }
    }
    $item = null;
    if (isset($params['item']))
      $item = $params['item'];
    $items = SearchEngine::search($params['query'], $item, $classes, $section);
    $rlitems = array();
    foreach ($items as $item)
    {
      $item = $item->getCurrentVersion(Session::getCurrentVariant());
      if ($item !== null)
        $rlitems[] = ItemWrapper::getWrapper($item);
    }

    $smarty->assign_by_ref($params['var'], $rlitems);
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
    if (is_dir($path))
    {
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
	        $fl['readablesize'] = getReadableFileSize($path.'/'.$file);
	        $fl['type'] = determineContentType($path.'/'.$file);
	        if (strpos($file, '.')!==false)
	        {
	          $fl['extension'] = substr($file, strpos($file, '.')+1);
	        }
	        else
	          $fl['extension'] = 'unknown';
	        $fl['path'] = $url.'/'.rawurlencode($file);
	        $files[$file] = $fl;
	      }
	    }
	    closedir($dir);
    }
    $smarty->assign_by_ref($params['var'], $files);
  }
}

function fetch_subitems($params, &$smarty)
{
  if (!empty($params['item']) && !empty($params['var']))
  {
    $item = $params['item'];
    if ($item instanceof ItemWrapper)
      $item = $item->item->getItem();
    
    if (isset($params['types']))
      $types = explode(',', $params['types']);
    else
      $types = null;
      
    $depth = -1;
    if (isset($params['depth']))
      $depth = $params['depth'];

    $items = $item->getSubitems($types, $depth);
    foreach ($items as $id => $subitem)
    {
      $iv = $subitem->getCurrentVersion(Session::getCurrentVariant());
      if ($iv !== null)
        $items[$id] = ItemWrapper::getWrapper($iv);
    }
    $smarty->assign_by_ref($params['var'], array_values($items));
  }
}

?>