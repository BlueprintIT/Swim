{secure users="read" login="true"}
{include file='includes/adminheader.tpl' title="User management"}
{include file='users/leftpane.tpl'}
<div id="mainpane" class="pane">
	<div class="header">
		{secure users="write"}
		<div class="toolbar">
			<div class="toolbarbutton">
				<a href="{encode method='admin' path='users/create.tpl' nestcurrent='true'}"><image src="{$CONTENT}/icons/add-user-blue.gif"/> Create new User</a>
			</div>
		</div>
		{/secure}
		<h2>User Administration</h2>
	</div>
	<div class="body">
		<p>Welcome to the SWIM administration interface.</p>
	</div>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
