{secure contacts="read" login="true"}
{include file='includes/frameheader.tpl' title="Contact management"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$CONTENT/scripts/fields`$smarty.config.YUI`.js"}
{apiget var="section" type="section" id=$request.query.section}
{if $request.query.item}
	{apiget var="item" type="item" id=$request.query.item}
	{assign var="variant" value="default"}
	{assign var="itemvariant" value=$item->getVariant($variant)}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
	{assign var="class" value=$item->getClass()}
{/if}
<script type="text/javascript">
window.top.SiteTree.selectItem("contacts");
</script>
{request var="contacts" method="admin" path="mailing/contacts.tpl" section=$request.query.section}
<div id="mainpane">
	{html_form tag_name="mainform" method="saveitem" variant="default" complete="true" current="true" nested=$contacts}
		{if $itemversion}
			<input type="hidden" name="item" value="{$request.query.item}">
		{else}
			<input type="hidden" name="section" value="{$section->getId()}">
			<input type="hidden" name="class" value="_contact">
			<input type="hidden" name="parentitem" value="{$request.query.parentitem}">
			<input type="hidden" name="parentsequence" value="children">
		{/if}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
						</div>
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="mailing/contacts.tpl" section=$request.query.section}"><img src="{$CONTENT}/icons/delete-page-blue.gif"/> Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Edit Contact</h2>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>Contact details</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						<tr>
							<td class="label"><label for="field_title">Title:</label></td>
							<td class="details"><input type="text" id="field_title" name="title" value="{if $itemversion}{$itemversion->getFieldValue('title')}{/if}"></td>
							<td class="description"></td>
						</tr>
						<tr>
							<td class="label"><label for="field_firstname">First Name:</label></td>
							<td class="details"><input type="text" id="field_firstname" name="firstname" value="{if $itemversion}{$itemversion->getFieldValue('firstname')}{/if}"></td>
							<td class="description"></td>
						</tr>
						<tr>
							<td class="label"><label for="field_middlename">Middle Name:</label></td>
							<td class="details"><input type="text" id="field_middlename" name="middlename" value="{if $itemversion}{$itemversion->getFieldValue('middlename')}{/if}"></td>
							<td class="description"></td>
						</tr>
						<tr>
							<td class="label"><label for="field_lastname">Last Name:</label></td>
							<td class="details"><input type="text" id="field_lastname" name="lastname" value="{if $itemversion}{$itemversion->getFieldValue('lastname')}{/if}"></td>
							<td class="description"></td>
						</tr>
						<tr>
							<td class="label"><label for="field_company">Company:</label></td>
							<td class="details"><input type="text" id="field_company" name="company" value="{if $itemversion}{$itemversion->getFieldValue('company')}{/if}"></td>
							<td class="description"></td>
						</tr>
						<tr>
							<td class="label"><label for="field_emailaddress">E-mail Address:</label></td>
							<td class="details"><input type="text" id="field_emailaddress" name="emailaddress" value="{if $itemversion}{$itemversion->getFieldValue('emailaddress')}{/if}"></td>
							<td class="description"></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
