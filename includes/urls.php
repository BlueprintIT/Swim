<?

/*
 * Swim
 *
 * Url encoding and decoding functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function encodeQuery($query)
{
  if (count($query)==0)
  {
    return "";
  }
  $result="";
  foreach ($query as $name => $value)
  {
    $result.="&".urlencode($name)."=".urlencode($value);
  }
  return substr($result,1);
}

function decodeQuery($query)
{
  parse_str($query,$result);
  return $result;
}

function encodeURL($page,$query = array())
{
  if (getPref("url.pathencoding")=="path")
  {
    $url=getPref("url.base")."/".$page;
  }
  else
  {
    $url=getPref("url.base");
    if ((getPref("url.pathencoding")=="iterative")&&(count($query)>0))
    {
      $newquery['query']=encodeQuery($query);
      $query=$newquery;
    }
    $query[getPref("url.querypathvar")]=$page;
  }
  if (count($query)>0)
  {
    $url.="?".encodeQuery($query);
  }
  return $url;
}

function decodeRequest()
{
  global $page;
  
  $page="";
  
  if (getPref("url.pathencoding")=="path")
  {
    if (isset($_SERVER['PATH_INFO']))
    {
      $page=substr($_SERVER['PATH_INFO'],1);
    }
  }
  else
  {
    if (isset($_GET[getPref("url.querypathvar")]))
    {
      $page=$_GET[getPref("url.querypathvar")];
      unset($_GET[getPref("url.querypathvar")]);
      unset($_REQUEST[getPref("url.querypathvar")]);
    }
    if (getPref("url.pathencoding")=="iterative")
    {
      if (isset($_GET['query']))
      {
        unset($_REQUEST['query']);
        $_GET=decodeQuery($_GET['query']);
        foreach ($_GET as $name => $value)
        {
          $_REQUEST[$name]=$value;
        }
      }
      else
      {
        $_GET=array();
      }
    }
  }
}

?>