{secure users="write" login="true"}
{include file='includes/adminheader.tpl' title="User management"}
{include file='users/leftpane.tpl'}
<div id="mainpane" class="pane">
<script>{literal}
function checkForm(form)
{
  if (form.elements['username'].value.length==0)
  {
    alert('You must enter a username.');
    return false;
  }
  if (form.elements['password'].value.length==0)
  {
    alert('You must enter a password.');
    return false;
  }
  return true;
}

function submitForm(form)
{
  if (checkForm(document.forms[form]))
    document.forms[form].submit();
}
{/literal}</script>
	{html_form tag_name="mainform" method="saveuser" nestcurrent="true"}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:submitForm('mainform')">Create</a>
						</div>
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="{$NESTED->encode()}">Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Create User</h2>
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
							<td class="label"><label for="username">Username:</label></td>
							<td class="details"><input type="input" maxlength="20" name="username" id="username" value=""></td>
							<td class="description">The username is used to log in to the administration area.</td>
						</tr>
						<tr>
							<td class="label"><label for="password">Password:</label></td>
							<td class="details"><input type="text" name="password" id="password" value=""></td>
							<td class="description">Enter a password for the user.</td>
						</tr>
						<tr>
							<td class="label"><label for="name">Full Name:</label></td>
							<td class="details"><input type="text" name="name" id="name" value=""></td>
							<td class="description">The full name is used to display the user's name in the administrative area.</td>
						</tr>
						{apiget var="groups" type="group"}
						{foreach name="grouplist" from=$groups item="group"}
							<tr>
								{if $smarty.foreach.grouplist.first}
									<td class="label" rowspan="{$smarty.foreach.grouplist.total}">Groups:</td>
								{/if}
								<td class="details"><input type="radio" name="group" id="group-{$group->getID()}" value="{$group->getID()}"><label for="group-{$group->getID()}">{$group->getName()}</label></td>
								<td class="description">{$group->getDescription()}</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	{/html_form}
</div>
{include file='includes/adminfooter.tpl'}
{/secure}
