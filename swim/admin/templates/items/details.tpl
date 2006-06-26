{secure documents="read" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="itemvariant" value=$item->getVariant($variant)}
{if isset($request.query.version)}
	{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{elseif $itemvariant->getCurrentVersion()}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="itemversion" value=$itemvariant->getVersions()[0]}
{/if}
{assign var="class" value=$itemversion->getClass()}
{if isset($request.query.reloadtree)}
<script>
  window.top.SiteTree.loadTree();
</script>
{/if}
<div id="mainpane">
	<div class="header">
		{secure documents="write"}
			<div class="toolbar">
				{if $itemversion->isComplete()}
					<div class="toolbarbutton">
						<a href="{encode method="copyversion" targetitem=$item->getId() targetvariant=$variant itemversion=$itemversion->getId()}"><img src="{$CONTENT}/icons/edit-grey.gif"/> Create new version for editing</a>
					</div>
				{else}
					<div class="toolbarbutton">
						<a href="{encode method="saveitem" itemversion=$itemversion->getId() complete="true"}"><img src="{$CONTENT}/icons/check-grey.gif"/> Mark as complete</a>
					</div>
					<div class="toolbarbutton">
						<a href="{encode method="admin" path="items/edit.tpl" item=$item->getId() version=$itemversion->getVersion()}"><img src="{$CONTENT}/icons/edit-grey.gif"/> Edit</a>
					</div>
				{/if}
			</div>
		{/secure}
		<h2>Item Details</h2>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>Version Control</h3>
			</div>
			<div class="sectionbody">
				<table class="admin">
					<tr>
					    <td class="label"><label for="title">Version:</label></td>
					    <td class="details">
							{html_form tag_style="display: inline" method="admin" path="items/details.tpl" item=$item->getId() formmethod="GET"}
								<select name="version" onchange="this.form.submit();">
									{sort var="versions" order="descending" from=$itemvariant->getVersions()}
									{foreach from=$versions item="version"}
										{if $version == $itemversion}
												<option value="{$version->getVersion()}" selected="true">
										{else}
												<option value="{$version->getVersion()}">
										{/if}
										{$version->getVersion()} last modified {$version->getModified()|date_format}
										{if $version->isCurrent()}
											(Published version)
										{/if}
										</option>
									{/foreach}
								</select>
							{/html_form}
							{html_form tag_name="versionform" tag_style="display: inline" method="saveitem" itemversion=$itemversion->getId() current="true" nestcurrent="true"}
								{if !$itemversion->isCurrent()}
									{if $itemversion->isComplete()}
										<div class="toolbarbutton">
											<a href="javascript:document.forms.versionform.submit();">
												<img src="{$CONTENT}/icons/check-blue.gif"> Publish this version
											</a>
										</div>
									{else}
										Item must be marked complete before it can be published.
									{/if}
								{else}
									<div class="toolbarbutton disabled">
										<img src="{$CONTENT}/icons/check-grey.gif"> Publish this version
									</div>
								{/if}
							{/html_form}
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="section">
			<div class="sectionheader">
				<h3>Item Options</h3>
			</div>
			<div class="sectionbody">
				<table class="admin">
					<tr>
						<td class="label">Class:</td>
						<td class="details">{$class->getName()}</td>
	
					</tr>
					{foreach from=$class->getFields($itemversion) item="field"}
						{if $field->getType()!='html' && $field->getType()!='sequence'}
							<tr>
								<td class="label">{$field->getName()}:</td>
								<td class="details">{$field->toString()}</td>
							</tr>
						{/if}
					{/foreach}
				</table>
			</div>
		</div>
		{foreach name="fieldlist" from=$class->getFields($itemversion) item="field"}
			{if $field->getType()=='html'}
				<div class="section">
					<div class="sectionheader">
						<h3>{$field->getName()}</h3>
					</div>
					<div class="sectionbody">
						{assign var="pos" value="1"}
					<div id="field_{$field->getId()}">{$field->toString()}</div>
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
