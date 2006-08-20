{secure users="read" login="true"}
{include file='includes/adminheader.tpl' title="User management"}
{include file='users/leftpane.tpl'}
<div id="mainpane" class="pane">
	{apiget var="selected" type="user" id=$request.query.user}
	<div class="header">
		<table class="toolbar">
			<tr>
				<td>
					<div class="toolbarbutton">
						<a href="{encode method="admin" path="users/create.tpl" nestcurrent="true"}"><image src="{$CONTENT}/icons/add-user-blue.gif"/> Create new User</a>
					</div>
				</td>
				{if $selected->getUserName() ne 'blueprintit'}
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="users/edit.tpl" user=$selected->getUsername() nestcurrent="true"}"><image src="{$CONTENT}/icons/edit-grey.gif"/> Edit this User</a>
						</div>
					</td>
					{if $selected->getUsername() ne $USER->getUsername()}
						<td>
							<div class="toolbarbutton">
								<a href="{encode method="deleteuser" user=$selected->getUsername()}">Delete this User</a>
							</div>
						</td>
					{/if}
				{/if}
			</tr>
		</table>
		<h2>User Details</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>User Details</h3>
			</div>
			<div class="sectionbody">
				<table class="admin">
					<tr>
						<td class="label">Username:</td>
						<td class="details">{$selected->getUsername()}</td>
					</tr>
					<tr>
						<td class="label">Full Name:</td>
						<td class="details">{$selected->getName()}</td>
					</tr>
					{foreach name="grouplist" from=$selected->getGroups() item="group"}
						<tr>
							{if $smarty.foreach.grouplist.first}
								<td class="label" rowspan="{$smarty.foreach.grouplist.total}">Groups:</td>
							{/if}
							<td class="details">{$group->getName()}</td>
						</tr>
					{/foreach}
				</table>
			</div>
		</div>
	</div>
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
