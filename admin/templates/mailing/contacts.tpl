{secure contacts="read" login="true"}
{include file='includes/frameheader.tpl' title="Contact management"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{apiget var="section" type="section" id=$request.query.section}
{assign var="item" value=$section->getRootContacts()}
{assign var="itemvariant" value=$item->getVariant('default')}
{assign var="itemversion" value=$itemvariant->getNewestVersion()}
{assign var="sequence" value=$itemversion->getMainSequence()}
{if isset($request.query.sortkey)}
	{assign var="sortkey" value=$request.query.sortkey}
{else}
	{assign var="sortkey" value="lastname"}
{/if}
<script type="text/javascript">
window.top.SiteTree.selectItem("contacts");
</script>
<div id="mainpane">
	<div class="header">
		{secure contacts="write"}
			{html_form tag_name="uploadform" tag_enctype="multipart/form-data" method="uploadcontacts" parentitem=$item->getId() nestcurrent="true"}
				<table class="toolbar">
					<tr>
						<td>
							Upload new contacts: <input type="file" name="file">
						</td>
						<td>
							<div class="toolbarbutton">
								<a href="javascript:BlueprintIT.forms.submitForm('uploadform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Upload</a>
							</div>
						</td>
					</tr>
				</table>
			{/html_form}
		{/secure}
		<h2>Contacts</h2>
	</div>
	<div class="body">
		<table width="95%" style="margin: auto">
			<thead>
				<tr>
					<th></th>
					<th>
						{if $sortkey!='title'}
							<a href="{encode method="admin" path="contacts/index.tpl" section=$section->getId() sortkey="title"}">Title</a>
						{else}
							Title
						{/if}
					</th>
					<th>
						{if $sortkey!='firstname'}
							<a href="{encode method="admin" path="contacts/index.tpl" section=$section->getId() sortkey="firstname"}">First Name</a>
						{else}
							First Name
						{/if}
					</th>
					<th>
						{if $sortkey!='lastname'}
							<a href="{encode method="admin" path="contacts/index.tpl" section=$section->getId() sortkey="lastname"}">Last Name</a>
						{else}
							Last Name
						{/if}
					</th>
					<th>
						{if $sortkey!='company'}
							<a href="{encode method="admin" path="contacts/index.tpl" section=$section->getId() sortkey="company"}">Company</a>
						{else}
							Company
						{/if}
					</th>
					<th>
						{if $sortkey!='emailaddress'}
							<a href="{encode method="admin" path="contacts/index.tpl" section=$section->getId() sortkey="emailaddress"}">E-mail</a>
						{else}
							E-mail
						{/if}
					</th>
					<th>Opted in</th>
				</tr>
			</thead>
			<tbody>
				{foreach name="itemlist" from=$sequence->getSortedItems($sortkey) item="subitem"}
					{assign var="rlitem" value=$subitem->getCurrentVersion($variant)}
					{if $rlitem===null}
						{assign var="rlitem" value=$subitem->getNewestVersion($variant)}
					{/if}
					<tr>
						<td>
						</td>
						<td>{assign var="field" value=$rlitem->getField('title')}{$field->toString()}</td>
						<td>{assign var="field" value=$rlitem->getField('firstname')}{$field->toString()}</td>
						<td>{assign var="field" value=$rlitem->getField('lastname')}{$field->toString()}</td>
						<td>{assign var="field" value=$rlitem->getField('company')}{$field->toString()}</td>
						<td>{assign var="field" value=$rlitem->getField('emailaddress')}{$field->toString()}</td>
						<td></td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
