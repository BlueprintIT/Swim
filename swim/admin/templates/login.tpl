{include file='includes/adminheader.tpl' title="Login"}
{assign var="message" value=""}
<div id="mainpane" class="pane">
	<div class="header">
		<h2>Please log in</h2>
	</div>
	<div class="body" style="text-align: center">
		{if $message ne ""}
			<h3>{$message}</h3>
		{/if}
		{if $USER->isLoggedIn()}
			<p>The user you are currently logged in as does not have permission for this. You may log in
			 as a different user.</p> 
		{else}
			<p>If you were logged in then you may have been automatically logged out for being inactive.</p>
		{/if}
		{html_form method="login" nestcurrent="true"}
			{if $message ne ""}
				<input type="hidden" name="message" value="{$message}">
			{/if}
			<table align="center">
				<tr>
					<td>Username:</td><td><input type='text' name='username' value=''></td>
				</tr>
				<tr>
					<td>Password:</td><td><input type='password' name='password' value=''></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: center"><input type='submit' value="Login"></td>
				</tr>
			</table>
		{/html_form}
	</div>
</div>
{include file='includes/adminfooter.tpl'}
