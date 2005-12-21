<?

class FileBrowser extends FileSelectorBlock
{
  function getStoreResource($request)
  {
    if ((!isset($request->query['type']))||($request->query['type']!='global'))
    {
      $page = Resource::decodeResource($request);
      if ($page!==false)
        return $page->getFile('attachments');
    }
    $container = getContainer('global');
    return $container->getFile('attachments');
  }

  function displayTableHeader()
  {
    parent::displayTableHeader();
  }

  function displayTableFooter()
  {
  }

  function displayContent($parser,$attrs,$text)
  {
?>
<div class="header">
<button onclick="select()">Select</button>
<button onclick="cancel()">Cancel</button>
<h2>File Selector</h2>
</div>
<div class="body">
<?
    parent::displayContent($parser,$attrs,$text);
?>
</div>
<?
  }
}

?>
