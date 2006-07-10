{secure documents="write" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{script href="$SHARED/yui/yahoo/yahoo-min.js"}
{script href="$SHARED/yui/dom/dom-min.js"}
{script href="$SHARED/yui/event/event-min.js"}
{script href="$SHARED/yui/calendar/calendar-min.js"}
{script href="$CONTENT/scripts/filebrowser.js"}
{script href="$CONTENT/scripts/calendar.js"}
{stylesheet href="$SHARED/yui/calendar/assets/calendar.css"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="itemvariant" value=$item->getVariant($session.variant)}
{if isset($request.query.version)}
	{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{elseif $itemvariant->getCurrentVersion()}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="itemversion" value=$itemvariant->getVersions()[0]}
{/if}
{assign var="class" value=$itemversion->getClass()}
{assign var="view" value=$itemversion->getView()}
<script>
{if isset($request.query.reloadtree)}
  window.top.SiteTree.loadTree();
{/if}
{literal}
function submitForm(form)
{
  document.forms[form].submit();
}
{/literal}</script>
<div id="mainpane">
	{html_form tag_name="mainform" method="saveitem" itemversion=$itemversion->getId()}
		{if $class->getVersioning()=='simple'}
			<input type="hidden" name="complete" value="true">
			<input type="hidden" name="current" value="true">
		{/if}
		<div class="header">
			<div class="toolbar">
				<div class="toolbarbutton">
					<a href="javascript:submitForm('mainform')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
				</div>
				<div class="toolbarbutton">
					<a href="{encode method="admin" path="items/details.tpl" item=$item->getId() version=$itemversion->getVersion()}"><img src="{$CONTENT}/icons/check-grey.gif"/> Cancel</a>
				</div>
			</div>
			<h2>Item Editor</h2>
		</div>
		<div class="body">
			<div class="section first">
			{if $class->allowsLink()}
					<div class="sectionheader">
						<h3>View Options</h3>
					</div>
					<div class="sectionbody">
						<table class="admin">
							{foreach from=$view->getFields($itemversion) item="field"}
								{if $field->getType()!='html' && $field->getType()!='sequence'}
									<tr>
										<td class="label"><label for="field:{$field->getId()}">{$field->getName()|escape}:</label></td>
										<td class="details">{$field->getEditor()}</td>
										<td class="description">{$field->getDescription()|escape}</td>
									</tr>
								{/if}
							{/foreach}
						</table>
					</div>
				</div>
				<div class="section">
			{/if}
				<div class="sectionheader">
					<h3>Item Options</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						{foreach from=$class->getFields($itemversion) item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
								<tr>
									<td class="label"><label for="field:{$field->getId()}">{$field->getName()|escape}:</label></td>
									<td class="details">{$field->getEditor()}</td>
									<td class="description">{$field->getDescription()|escape}</td>
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
							<h3>{$field->getName()|escape}</h3>
						</div>
						<div class="sectionbody">
							<div id="field_{$field->getId()}">{$field->getEditor()}</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
