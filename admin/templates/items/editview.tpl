{secure documents="write" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/calendar/calendar`$smarty.config.YUI`.js"}
{script href="$CONTENT/scripts/fields.js`$smarty.config.YUI`"}
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
{if isset($request.query.view)}
	{apiget var="view" type="view" id=$request.query.view}
{else}
	{assign var="view" value=$itemversion->getView()}
{/if}
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
	{html_form tag_name="mainform" method="saveitem" item=$item->getId() version=$itemversion->getVersion() itemversion=$itemversion->getId()}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:submitForm('mainform')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
						</div>
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="items/details.tpl" item=$item->getId() version=$itemversion->getVersion()}"><img src="{$CONTENT}/icons/check-grey.gif"/> Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Item Editor</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionbody">
					<table>
						<tr>
						    <td class="label"><label for="title">View:</label></td>
						    <td class="details">
								<select name="view" onchange="this.form.action='{encode method="admin" path="items/editview.tpl"}'; this.form.submit();">
									{foreach from=$class->getViews() item="nview"}
										{if $nview === $view}
												<option value="{$nview->getId()}" selected="true">
										{else}
												<option value="{$nview->getId()}">
										{/if}
											{$nview->getName()}
										</option>
									{/foreach}
								</select>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="section first">
				<div class="sectionheader">
					<h3>View Options</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						{foreach from=$view->getFields() item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
								{assign var="field" value=$view->getField($itemversion, $field->getId())}
								<tr>
									<td class="label"><label for="field:{$field->getId()}">{$field->getName()|escape}:</label></td>
									<td class="details">{$field->getEditor($REQUEST,$SMARTY)}</td>
									<td class="description">{$field->getDescription()|escape}</td>
								</tr>
							{/if}
						{/foreach}
					</table>
				</div>
			</div>
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
