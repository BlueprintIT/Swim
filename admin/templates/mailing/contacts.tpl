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
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="mailing/editcontact.tpl" section=$request.query.section parentitem=$item->getId()}"><img src="{$CONTENT}/icons/up-blue.gif"/> Add a Contact</a>
						</div>
					</td>
					{html_form tag_name="uploadform" tag_enctype="multipart/form-data" method="uploadcontacts" parentitem=$item->getId() nestcurrent="true"}
						<td>
							Upload new contacts: <input type="file" name="file">
						</td>
						<td>
							<div class="toolbarbutton">
								<a href="javascript:BlueprintIT.forms.submitForm('uploadform')"><img src="{$CONTENT}/icons/up-blue.gif"/> Upload</a>
							</div>
						</td>
					{/html_form}
				</tr>
			</table>
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
					{if $rlitem!==null}
						<tr>
							<td>
							</td>
							<td>{$rlitem->getFieldValue('title')}</td>
							<td>{$rlitem->getFieldValue('firstname')}</td>
							<td><a href="{encode method="admin" path="mailing/editcontact" item=$subitem->getId() section=$section->getId() parentitem=$item->getId()}">{$rlitem->getFieldValue('lastname')}</a></td>
							<td>{$rlitem->getFieldValue('company')}</td>
							<td>{$rlitem->getFieldValue('emailaddress')}</td>
							<td></td>
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
