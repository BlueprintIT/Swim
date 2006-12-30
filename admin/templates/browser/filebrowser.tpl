{secure documents="read"}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Swim Resources Browser</title>
<script type="text/javascript">
{if (($request.query.api=='fckeditor') || ($request.query.api=='filefield'))}
{literal}
function setItem(url, path, name)
{
	window.opener.SetUrl(url);
}
{/literal}
{elseif $request.query.api=='tinymce'}
var field = "{$request.query.field}";
{literal}
function setItem(url, path, name)
{
	window.opener.document.forms[0].elements[field].value = url;
}
{/literal}
{elseif $request.query.api=='itemfield'}
{literal}
function setItem(url, path, name)
{
	window.opener.SetItem(path, name);
}
{/literal}
{/if}
</script>
	</head>
	<frameset cols="*" frameborder="0">
{if $request.query.type=='link' || $request.query.type=='item'}
		<frame src="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/items.tpl"}" scrolling="no" frameborder="0">
{else}
		<frame src="{encode method="admin" type=$request.query.type item=$request.query.item variant=$request.query.variant version=$request.query.version path="browser/attachments.tpl"}" scrolling="no" frameborder="0">
{/if}
	</frameset>
</html>
{/secure}
