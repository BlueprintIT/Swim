<?

if (substr($request->resourcePath,0,6)=='create')
{
  include 'create.php';
}
else if (substr($request->resourcePath,0,5)=='view/')
{
  include 'details.php';
}
else if (substr($request->resourcePath,0,5)=='edit/')
{
  include 'edit.php';
}
else
{
  include 'admin.php';
}

?>
