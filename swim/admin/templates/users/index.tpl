{secure users="read"}
{include file='includes/adminheader.tpl' title="User management"}
{include file='users/leftpane.tpl'}
<div id="mainpane" class="pane">
	<div class="header">
		<div class="toolbar">
			<div class="toolbarbutton">
				<a href="{encode method='admin' path='users/create.tpl' nextcurrent='true'}"><image src="{$CONTENT}/icons/add-user-blue.gif"/> Create new User</a>
			</div>
		</div>
		<h2>User Administration</h2>
	</div>
	<div class="body">
		<p>Welcome to the SWIM administration interface.</p>
	</div>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
