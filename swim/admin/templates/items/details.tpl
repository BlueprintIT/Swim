{secure documents="read" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{script href="$SHARED/yui/yahoo/yahoo-min.js"}
{script href="$SHARED/yui/event/event-min.js"}
{script href="$SHARED/yui/connection/connection-min.js"}
{script method="admin" path="scripts/request.js"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="section" value=$item->getSection()}
{assign var="itemvariant" value=$item->getVariant($session.variant)}
{if isset($request.query.version)}
	{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{elseif $itemvariant->getCurrentVersion()}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="itemversion" value=$itemvariant->getNewestVersion()}
{/if}
{assign var="class" value=$itemversion->getClass()}
{assign var="view" value=$itemversion->getView()}
<script>
{if isset($request.query.reloadtree)}
  window.top.SiteTree.loadTree();
{/if}
  window.top.SiteTree.selectItem({$item->getId()});
{literal}
function getRow(element)
{
}

function moveUpComplete(req) {
	window.top.SiteTree.loadTree();
	var row = req.argument.row;
	row.parentNode.insertBefore(row, row.parentNode.rows[row.rowIndex-1]);
}

function moveUp(item, field, link) {
	var row = link.parentNode.parentNode;
	var index = row.rowIndex;
	if (index>0) {
		var callback = {
			success: moveUpComplete,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				row: row
			}
		};
		var request = new Request();
		request.setMethod('mutatesequence');
		request.setQueryVar('item', item);
		request.setQueryVar('field', field);
		request.setQueryVar('action', 'moveup');
		request.setQueryVar('index', index);
		YAHOO.util.Connect.asyncRequest("GET", request.encode(), callback, null);
	}
}

function moveDownComplete(req) {
	window.top.SiteTree.loadTree();
	var row = req.argument.row;
	row.parentNode.insertBefore(row, row.parentNode.rows[row.rowIndex+2]);
}

function moveDown(item, field, link) {
	var row = link.parentNode.parentNode;
	var index = row.rowIndex;
	var rows = row.parentNode.rows.length;
	if (index<(rows-1)) {
		var callback = {
			success: moveDownComplete,
			failure: function(obj) {
				alert("There was an error performing this action.");
			},
			argument: {
				row: row
			}
		};
		var request = new Request();
		request.setMethod('mutatesequence');
		request.setQueryVar('item', item);
		request.setQueryVar('field', field);
		request.setQueryVar('action', 'movedown');
		request.setQueryVar('index', index);
		YAHOO.util.Connect.asyncRequest("GET", request.encode(), callback, null);
	}
}
{/literal}
</script>
<div id="mainpane">
	<div class="header">
		{secure documents="write"}
			<div class="toolbar">
				{if $itemversion->isComplete()}
					<div class="toolbarbutton">
						<a href="{encode method="copyversion" targetitem=$item->getId() targetvariant=$session.variant itemversion=$itemversion->getId()}"><img src="{$CONTENT}/icons/edit-grey.gif"/> Create new version for editing</a>
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
		{if $class->allowsLink()}
			<div class="section">
				<div class="sectionheader">
					<h3>View Options</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						<tr>
							<td class="label">View:</td>
							{if $itemversion->isComplete()}
								<td class="details">{$view->getName()}</td>
							{else}
								<td class="details">
									{html_form tag_name="viewform" tag_style="display: inline" method="saveitem" itemversion=$itemversion->getId() formmethod="POST"}
										<select name="view">
											{foreach from=$class->getViews() item="nview"}
												{if $nview === $itemversion->getView()}
														<option value="{$nview->getId()}" selected="true">
												{else}
														<option value="{$nview->getId()}">
												{/if}
													{$nview->getName()}
												</option>
											{/foreach}
										</select>
										<div class="toolbarbutton">
											<a href="javascript:document.forms.viewform.submit();">
												<img src="{$CONTENT}/icons/check-blue.gif"> Change View
											</a>
										</div>
									{/html_form}
								</td>
							{/if}
						</tr>
						{foreach from=$view->getFields($itemversion) item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
								<tr>
									<td class="label">{$field->getName()|escape}:</td>
									<td class="details">{$field->toString()|escape}</td>
								</tr>
							{/if}
						{/foreach}
					</table>
				</div>
			</div>
		{/if}
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
								<td class="label">{$field->getName()|escape}:</td>
								<td class="details">{$field->toString()|escape}</td>
							</tr>
						{/if}
					{/foreach}
				</table>
			</div>
		</div>
		{foreach name="fieldlist" from=$class->getFields($itemversion) item="field"}
			{if $field->getType()=='sequence'}
				<div class="section">
					<div class="sectionheader">
						<h3>{$field->getName()|escape}</h3>
					</div>
					<div class="sectionbody">
						{if !$field->isSorted()}
							<table class="sequencelist">
								{foreach name="itemlist" from=$field->getItems() item="subitem"}
									{assign var="rlitem" value=$subitem->getCurrentVersion($session.variant)}
									{if $rlitem==null}
										{assign var="rlitem" value=$subitem->getNewestVersion($session.variant)}
									{/if}
									{assign var="itemclass" value=$rlitem->getClass()}
									{assign var="itemname" value=$rlitem->getField('name')}
									<tr>
										<td class="name"><a href="{encode method="admin" path="items/details.tpl" item=$subitem->getId()}">{$itemname->toString()}</a></td>
										<td class="options">
										{secure documents="write"}
											<a class="option" href="#" onclick="moveUp({$item->getId()}, '{$field->getId()}', this)"><img alt="Move up" title="Move up" src="{$CONTENT}/icons/up-purple.gif"></a>
											<a class="option" href="#" onclick="moveDown({$item->getId()}, '{$field->getId()}', this)"><img alt="Move down" title="Move down" src="{$CONTENT}/icons/down-purple.gif"></a>
										{/secure}
										</td>
									</tr>
								{/foreach}
							</table>
						{/if}
						{secure documents="write"}
						{assign var="choices" value=$field->getVisibleClasses()}
						{if count($choices)>0}
							{html_form method="createitem"  targetsection=$section->getId() targetvariant=$session.variant parentitem=$item->getId() parentsequence=$field->getId()}
								<p>Add a new <select name="class">
								{foreach from=$choices item="choice"}
									<option value="{$choice->getId()}">{$choice->getName()}</option>
								{/foreach}
								</select> <input type="submit" value="Add..."></p>
							{/html_form}
						{/if}
						{/secure}
					</div>
				</div>
			{/if}
		{/foreach}
		{foreach name="fieldlist" from=$class->getFields($itemversion) item="field"}
			{if $field->getType()=='html'}
				<div class="section">
					<div class="sectionheader">
						<h3>{$field->getName()|escape}</h3>
					</div>
					<div class="sectionbody">
						<div id="field_{$field->getId()}" class="content">{$field->toString()}</div>
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
