<?
$path = $_SERVER['REQUEST_URI'];
$pos=strpos($path,'?');
if ($pos!==false)
{
  $target=substr($path,0,$pos).'/'.substr($path,$pos);
}
else
{
  $target=$path.'/';
}

if ((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
  $protocol='https';
  if ((isset($_SERVER["SERVER_PORT"]))&&($_SERVER["SERVER_PORT"]!=443))
    $port=':'.$_SERVER["SERVER_PORT"];
}
else
{
  $protocol='http';
  if ((isset($_SERVER["SERVER_PORT"]))&&($_SERVER["SERVER_PORT"]!=80))
    $port=':'.$_SERVER["SERVER_PORT"];
}
$port='';

header('Location: '.$protocol.'://'.$_SERVER['HTTP_HOST'].$port.$target);
?>