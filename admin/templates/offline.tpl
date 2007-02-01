{include file='includes/adminheader.tpl' title="Login"}
{assign var="message" value=""}
<div id="mainpane" class="pane">
	<div class="header">
		<h2>Admin Area Unavailable</h2>
	</div>
	<div class="body" style="text-align: center">
		<h3 style="margin-bottom: 0.5em">Admin Area Offline</h3>
		
		<p style="margin-bottom: 0.5em">This site is currently undergoing upgrades and in order to ensure a smooth operation
		 the admin area has been taken offline.</p>
		<p style="margin-bottom: 0.5em">Your site administrator should have received notification of this however feel free to contact
		 Blueprint IT for further information.</p>
		<p style="margin-bottom: 0.5em">{$PREFS->getPref('admin.offline')}</p>
	</div>
</div>
{include file='includes/adminfooter.tpl'}
