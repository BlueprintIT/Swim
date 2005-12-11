<?
$page = &Resource::decodeResource($request);
?>
<script>
var selectedpage = '';

function select(link)
{
  var els = document.getElementsByTagName("a");
  for (var i=0; i<els.length; i++)
  {
    if (els[i].getAttribute('path')==selectedpage)
    {
      els[i].parentNode.className=null;
    }
  }
  selectedpage=link.getAttribute('path');
  els = document.getElementsByTagName("a");
  for (var i=0; i<els.length; i++)
  {
    if (els[i].getAttribute('path')==selectedpage)
    {
      els[i].parentNode.className='selected';
    }
  }
  return true;
}

function submit()
{
  if (selectedpage)
  {
    window.opener.tinyMCE.insertLink('/'+selectedpage,"");
    window.close();
  }
  else
  {
    alert("You must select a page first.");
  }
}
</script>
<div class="header">
<button onclick="submit()">Select</button>
<button onclick="window.close()">Cancel</button>
<h2>Preview</h2>
</div>
<iframe class="body" name="preview" src=""></iframe>
