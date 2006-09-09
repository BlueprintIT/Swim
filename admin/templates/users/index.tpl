{secure users="read" login="true"}
{include file='includes/frameheader.tpl' title="User management"}
<div id="mainpane">
	<div class="header">
		{secure users="write"}
		<table class="toolbar">
			<tr>
				<td>
					<div class="toolbarbutton">
						<a href="{encode method='admin' path='users/create.tpl' nestcurrent='true'}"><image src="{$CONTENT}/icons/add-user-blue.gif"/> Create new User</a>
					</div>
				</td>
			</tr>
		</table>
		{/secure}
		<h2>User Administration</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<p>Welcome to the SWIM administration interface.</p>
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
