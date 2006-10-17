{include file='includes/adminheader.tpl' title="Login"}
{assign var="message" value=""}
<div id="mainpane" class="pane">
	<div class="header">
		<h2>Admin Area Unavailable</h2>
	</div>
	<div class="body" style="text-align: center">
		<h3>Admin Area Offline</h3>
		
		<p>This site is currently undergoing upgrades and in order to ensure a smooth operation
		 the admin area has been taken offline.</p>
		<p>Your site administrator should have received notification of this however feel free to contact
		 Blueprint IT for further information.</p>
		<p>{$PREFS->getPref('admin.offline')}</p>
	</div>
</div>
{include file='includes/adminfooter.tpl'}
