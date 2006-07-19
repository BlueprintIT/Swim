{secure documents="read"}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Swim Resources Browser</title>
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
