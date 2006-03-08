<?

$resource = Resource::decodeResource($request);
if ($resource===null)
{
  include 'admin.php';
}
else if ($resource->isPage())
{
  include 'details.php';
}
else
{
  include 'admin.php';
}

?>
