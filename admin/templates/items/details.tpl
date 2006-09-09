{secure documents="read" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{stylesheet href="$CONTENT/styles/sitetree.css"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/connection/connection`$smarty.config.YUI`.js"}
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
			{if !$item->isArchived()}
				<table class="toolbar">
					<tr>
						{assign var="sequence" value=$itemversion->getMainSequence()}
						<td>
							{if $sequence !== null}
								{assign var="choices" value=$sequence->getVisibleClasses()}
								{assign var="found" value="false"}
								{foreach from=$choices item="choice"}
									{if strtolower($choice->getName())=='category'}
										{assign var="found" value="true"}
										<div class="toolbarbutton">
											<a href="{encode method="createitem" class=$choice->getId() targetsection=$section->getId() targetvariant=$session.variant parentitem=$item->getId() parentsequence=$sequence->getId()}">
												<img src="{$CONTENT}/icons/add-folder-blue.gif" alt="New category">
												New category
											</a>
										</div>
									{/if}
								{/foreach}
								{if $found=='false'}
									<div class="toolbarbutton disabledtoolbarbutton">
										<img src="{$CONTENT}/icons/add-folder-grey.gif" alt="New category">
										New category
									</div>
								{/if}
							{else}
								<div class="toolbarbutton disabledtoolbarbutton">
									<img src="{$CONTENT}/icons/add-folder-grey.gif" alt="New category">
									New category
								</div>
							{/if}
						</td>
						<td>
							{if $sequence !== null}
								{assign var="choices" value=$sequence->getVisibleClasses()}
							{/if}
							{if $sequence!==null && count($choices)>0}
								{html_form method="createitem" targetsection=$section->getId() targetvariant=$session.variant parentitem=$item->getId() parentsequence=$sequence->getId()}
									<p class="toolbarbutton"><a onclick="this.parentNode.parentNode.submit(); return false;" href="#">New <img src="{$CONTENT}/icons/add-page-blue.gif"></a></p>
									<div><select name="class">
									{foreach from=$choices item="choice"}
										{if strtolower($choice->getName())!='category'}
											<option value="{$choice->getId()}">{$choice->getName()}</option>
										{/if}
									{/foreach}
									</select></div>
								{/html_form}
							{else}
								<p class="toolbarbutton disabledtoolbarbutton">New <img src="{$CONTENT}/icons/add-page-grey.gif"></p>
								<div><select disabled="disabled" style="width: 100px"></select></div>
							{/if}
						</td>
						<td class="separator"></td>
						<td>
							{if $class->getVersioning()=='simple'}
								<div class="toolbarbutton">
									<a href="{encode method="copyversion" targetitem=$item->getId() targetvariant=$session.variant itemversion=$itemversion->getId()}">
										<img src="{$CONTENT}/icons/edit-page-blue.gif"/>
										Edit options
									</a>
								</div>
							{else}
								{if $itemversion->isComplete()}
									{assign var="draft" value=$itemvariant->getDraftVersion()}
									{if $draft !== null}
										<div class="toolbarbutton">
											<a href="{encode method="admin" path="items/details.tpl" item=$item->getId() version=$draft->getVersion()}">
												<img src="{$CONTENT}/icons/edit-page-blue.gif"/>
												Goto draft
											</a>
										</div>
									{else}
										<div class="toolbarbutton">
											<a href="{encode method="copyversion" targetitem=$item->getId() targetvariant=$session.variant itemversion=$itemversion->getId()}">
												<img src="{$CONTENT}/icons/add-page-red.gif"/>
												New version
											</a>
										</div>
									{/if}
								{else}
									<div class="toolbarbutton disabledtoolbarbutton">
										<img src="{$CONTENT}/icons/add-page-grey.gif"/>
										New version
									</div>
								{/if}
							{/if}
						</td>
						<td>
							{if ($class->allowsLink() && $view !== null && (!$itemversion->isComplete() || $class->getVersioning()=='simple'))}
								<div class="toolbarbutton">
									{if !$itemversion->isComplete()}
										<a href="{encode method="admin" path="items/editview.tpl" item=$item->getId() version=$itemversion->getVersion()}">
									{else}
										<a href="{encode method="copyversion" action="editview" targetitem=$item->getId() targetvariant=$session.variant itemversion=$itemversion->getId()}">
									{/if}
										<img src="{$CONTENT}/icons/view-edit.gif"/>
										Edit view
									</a>
								</div>
							{else}
								<div class="toolbarbutton disabledtoolbarbutton">
									<img src="{$CONTENT}/icons/view-edit-grey.gif"/>
									Edit view
								</div>
							{/if}
						</td>
						<td class="separator"></td>
						<td>
							{if $item !== $section->getRootItem()}
								<div class="toolbarbutton">
									<a href="{encode method="archive" item=$item->getId() archive="true" nestcurrent="true"}">
										<img src="{$CONTENT}/icons/delete-page-blue.gif"/>
										Delete
									</a>
								</div>
							{else}
								<div class="toolbarbutton disabledtoolbarbutton">
									<a href="{encode method="archive" item=$item->getId() archive="true" nestcurrent="true"}">
										<img src="{$CONTENT}/icons/delete-page-blue.gif"/>
										Delete
									</a>
								</div>
							{/if}
						</td>
					</tr>
				</table>
				{if $class->getVersioning()!='simple'}
					<table class="toolbar">
						<tr>
							{if !$itemversion->isComplete()}
								<td>
									<div class="toolbarbutton">
										<a href="{encode method="admin" path="items/edit.tpl" item=$item->getId() version=$itemversion->getVersion()}">
											<img src="{$CONTENT}/icons/draft-edit.gif"/>
											Edit draft
										</a>
									</div>
								</td>
								<td>
									<img src="{$CONTENT}/icons/arrow-blue.gif">
								</td>
								<td>
									<div class="toolbarbutton">
										<a href="{encode method="saveitem" itemversion=$itemversion->getId() complete="true"}">
											<img src="{$CONTENT}/icons/complete-grey.gif"/>
											Mark complete
										</a>
									</div>
								</td>
								<td>
									<img src="{$CONTENT}/icons/arrow-grey.gif">
								</td>
								<td>
									<div class="toolbarbutton disabledtoolbarbutton">
										<img src="{$CONTENT}/icons/complete-grey.gif"/>
										Publish
									</div>
								</td>
							{else}
								<td>
									<div class="toolbarbutton">
										<img src="{$CONTENT}/icons/draft-done.gif"/>
										Draft done
									</div>
								</td>
								<td>
									<img src="{$CONTENT}/icons/arrow-blue.gif">
								</td>
								<td>
									<div class="toolbarbutton">
										<img src="{$CONTENT}/icons/complete-done.gif"/>
										Complete
									</div>
								</td>
								<td>
									<img src="{$CONTENT}/icons/arrow-blue.gif">
								</td>
								<td>
									{if $itemversion->isCurrent()}
										<div class="toolbarbutton">
											<img src="{$CONTENT}/icons/publish-done.gif"/>
											Published
										</div>
									{else}
										<div class="toolbarbutton">
											<a href="{encode method="saveitem" itemversion=$itemversion->getId() current="true" nestcurrent="true"}">
												<img src="{$CONTENT}/icons/complete-grey.gif"/>
												Publish
											</a>
										</div>
									{/if}
								</td>
							{/if}
						</tr>
					</table>
				{/if}
			{else}
				<table class="toolbar">
					<tr>
						<td>
							<div class="toolbarbutton">
								<a href="{encode method="archive" item=$item->getId() archive="false" nestcurrent="true"}">
									<img src="{$CONTENT}/icons/delete-page-blue.gif"/>
									Restore this item
								</a>
							</div>
						</td>
					</tr>
				</table>
			{/if}
		{/secure}
		<h2>Details</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionbody">
				{assign var="namefield" value=$itemversion->getField("name")}
				{assign var="sequence" value=$itemversion->getMainSequence()}
				{if $sequence === null}
					<h2 class="site_itemcontent site_item site_icon_{$class->getId()}">{$namefield->output($REQUEST,$SMARTY)}</h2>
				{else}
					<h2 class="site_itemcontent site_container_open site_icon_{$class->getId()} site_icon_{$class->getId()}_open">{$namefield->output($REQUEST,$SMARTY)}</h2>
				{/if}
				<table class="admin">
					<tr>
					    <td class="label"><label for="title">Version control:</label></td>
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
										{elseif !$version->isComplete()}
											(Draft version)
										{/if}
										</option>
									{/foreach}
								</select>
							{/html_form}
						</td>
					</tr>
				</table>
			</div>
		</div>
		{if ($class->allowsLink() && $view !== null)}
			<div class="section first">
				<div class="sectionheader">
					<h3>View Options</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						<tr>
							<td class="label">View:</td>
							<td class="details">{$view->getName()}</td>
						</tr>
						{foreach from=$itemversion->getViewFields() item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
								<tr>
									<td class="label">{$field->getName()|escape}:</td>
									<td class="details">{$field->output($REQUEST,$SMARTY)}</td>
								</tr>
							{/if}
						{/foreach}
					</table>
				</div>
			</div>
		{/if}
		<div class="section">
			<div class="sectionheader">
				<h3>{$class->getName()} content</h3>
			</div>
			<div class="sectionbody">
				<table class="admin">
					<tr>
						<td class="label">Type:</td>
						<td class="details">{$class->getName()}</td>
					</tr>
					{foreach from=$itemversion->getClassFields() item="field"}
						{if $field->getType()!='html' && $field->getType()!='sequence' && $field->getId()!='name'}
							<tr>
								<td class="label">{$field->getName()|escape}:</td>
								<td class="details">{$field->output($REQUEST,$SMARTY)}</td>
							</tr>
						{/if}
					{/foreach}
				</table>
			</div>
		</div>
		{foreach name="fieldlist" from=$itemversion->getClassFields() item="field"}
			{if $field->getType()=='sequence' && $field != $itemversion->getMainSequence()}
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
									{assign var="itemname" value=$itemname->toString()}
									{if $itemname==''}
										{assign var="itemname" value="[Unnamed]"}
									{/if}
									<tr>
										<td class="name"><a href="{encode method="admin" path="items/details.tpl" item=$subitem->getId()}">{$itemname}</a></td>
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
						{assign var="classcount" value=0}
						{assign var="filecount" value=0}
						{foreach from=$choices item="choice"}
							{if $choice->getType()=='normal'}
								{assign var="classcount" value=$classcount+1}
							{elseif $choice->getType()=='file'}
								{assign var="filecount" value=$classcount+1}
							{/if}
						{/foreach}
						{if $classcount>0}
							{html_form method="createitem" targetsection=$section->getId() targetvariant=$session.variant parentitem=$item->getId() parentsequence=$field->getId()}
								<p>Add a new <select name="class">
								{foreach from=$choices item="choice"}
									{if $choice->getType()=='normal'}
										<option value="{$choice->getId()}">{$choice->getName()}</option>
									{/if}
								{/foreach}
								</select> <input type="submit" value="Add..."></p>
							{/html_form}
						{/if}
						{if $filecount>0}
							{html_form tag_enctype="multipart/form-data" method="uploaditem" targetsection=$section->getId() targetvariant=$session.variant parentitem=$item->getId() parentsequence=$field->getId()}
								<p>Upload a new item: <input type="file" name="file"> <input type="submit" value="Upload..."></p>
							{/html_form}
						{/if}
						{/secure}
					</div>
				</div>
			{/if}
		{/foreach}
		{foreach name="fieldlist" from=$itemversion->getFields() item="field"}
			{if $field->getType()=='html'}
				<p class="htmlfield">{$field->getName()|escape}:</p>
				{$field->output($REQUEST,$SMARTY)}
			{/if}
		{/foreach}
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
