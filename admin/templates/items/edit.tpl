{secure documents="write" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{stylesheet href="$SHARED/yui/calendar/assets/calendar.css"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/calendar/calendar`$smarty.config.YUI`.js"}
{script href="$CONTENT/scripts/fields`$smarty.config.YUI`.js"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="variant" value="default"}
{assign var="itemvariant" value=$item->getVariant($variant)}
{if isset($request.query.version)}
	{assign var="itemversion" value=$itemvariant->getVersion($request.query.version)}
{elseif $itemvariant->getCurrentVersion()}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="itemversion" value=$itemvariant->getVersions()[0]}
{/if}
{assign var="class" value=$itemversion->getClass()}
{assign var="view" value=$itemversion->getView()}
{if $itemvariant->getCurrentVersion()}
	{assign var="bestversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="bestversion" value=$itemvariant->getNewestVersion()}
{/if}
{assign var="bestname" value=$bestversion->getField("name")}
<script>
{if isset($request.query.reloadtree)}
  window.top.SiteTree.loadTree();
{else}
  {if $item->isArchived()}
  var righttree =  window.top.SiteTree.id == 'archive';
  {else}
  var righttree =  window.top.SiteTree.id != 'archive';
  {/if}
  if (righttree) {ldelim}
    var items = window.top.SiteTree.getItems({$item->getId()});
    if (items) {ldelim}
      for (var i=0; i<items.length; i++) {ldelim}
        items[i].setPublished({if $bestversion->isCurrent()}true{else}false{/if});
        items[i].setLabel("{$bestname->toString()}");
      {rdelim}
    {rdelim} else {ldelim}
      var parent;
      {assign var="sequence" value=$item->getMainSequence()}
      {if $sequence}
      var contents = "{foreach name="contentlist" item="subclass" from=$sequence->getVisibleClasses()}{$subclass->getId()}{if !$smarty.foreach.contentlist.last},{/if}{/foreach}";
      {else}
      var contents = "";
      {/if}
      {foreach from=$item->getParents() item="parent"}
      parent = {$parent.item->getId()};
      items = window.top.SiteTree.getItems(parent);
      if (items) {ldelim}
        for (var i=0; i<items.length; i++) {ldelim}
          window.top.SiteTree.createNode({$item->getId()}, "{$bestname->toString()}", "{$class->getId()}", {if $bestversion->isCurrent()}true{else}false{/if}, contents, items[i]);
          items[i].redrawChildren();
        {rdelim}
      {rdelim}
      {/foreach}
    {rdelim}
    window.top.SiteTree.selectItem({$item->getId()});
  {rdelim} else
    window.top.SiteTree.removeAllNodes({$item->getId()});
{/if}
var item = {ldelim}
	item: {$item->getId()},
	variant: "{$itemvariant->getVariant()}",
	version: {$itemversion->getVersion()}
{rdelim};
</script>
{request var="detailsreq" method="admin" path="items/details.tpl" item=$item->getId() version=$itemversion->getVersion()}
<div id="mainpane">
	{html_form tag_name="mainform" method="saveitem" itemversion=$itemversion->getId() nested=$detailsreq}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
						</div>
					</td>
					{if $class->getVersioning()=='simple'}
						<td>
							<div class="toolbarbutton">
								<a href="javascript:BlueprintIT.forms.submitForm('mainform', 'current', 'true')"><img src="{$CONTENT}/icons/complete-grey.gif"/> Save and Publish</a>
							</div>
						</td>
					{/if}
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="items/details.tpl" item=$item->getId() version=$itemversion->getVersion()}"><img src="{$CONTENT}/icons/delete-page-blue.gif"/> Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Item Editor</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>{$class->getName()} content</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						{foreach from=$itemversion->getClassFields() item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
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
			{foreach name="fieldlist" from=$itemversion->getFields() item="field"}
				{if $field->getType()=='html'}
					<p class="htmlfield">{$field->getName()|escape}:</p>
					<div id="outer_field_{$field->getId()}">{$field->getEditor($REQUEST,$SMARTY)}</div>
				{/if}
			{/foreach}
<script type="text/javascript">if (initialiseTinyMCE && typeof(tinyMCE_GZ)!='undefined') initialiseTinyMCE_GZ();</script>
<script type="text/javascript">if (initialiseTinyMCE) initialiseTinyMCE();</script>
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
