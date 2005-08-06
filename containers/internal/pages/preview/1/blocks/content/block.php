<?

$resource=&Resource::decodeResource($request);
if ($resource->isPage())
{
	$resource=&$resource->getReferencedBlock('content');
}
if ($resource->isBlock())
{
	$resource->display($parser,$attrs,$text);
}

?>