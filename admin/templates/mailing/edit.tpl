{secure contacts="write" login="true"}
{include file='includes/frameheader.tpl' title="Mail Editor"}
{script method="admin" path="scripts/request.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/yahoo/yahoo`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/dom/dom`$smarty.config.YUI`.js"}
{script href="$SHARED/yui/event/event`$smarty.config.YUI`.js"}
{script href="$CONTENT/scripts/fields`$smarty.config.YUI`.js"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="variant" value="default"}
{assign var="itemvariant" value=$item->getVariant($variant)}
{assign var="itemversion" value=$itemvariant->getDraftVersion()}
{assign var="class" value=$item->getClass()}
{assign var="mailing" value=$class->getMailing()}
{request var="details" method="admin" path="mailing/details.tpl" item=$item->getId()}
{request var="sendmail" method="sendmail" item=$item->getId() nested=$details}
<script type="text/javascript">
window.top.SiteTree.selectItem("{$request.query.item}");
</script>
<div id="mainpane">
	{html_form tag_name="mainform" method="saveitem" itemversion=$itemversion->getId()}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform','redirect','{$details->encode()}')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
						</div>
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform','redirect','{$sendmail->encode()}')"><img src="{$CONTENT}/icons/save.gif"/> Send</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Mail Editor</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>{$class->getName()} content</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						{assign var="field" value=$itemversion->getField('name')}
						<tr>
							<td class="label"><label for="field:{$field->getId()}">{$field->getName()|escape}:</label></td>
							<td class="details">{$field->getEditor($REQUEST,$SMARTY)}</td>
							<td class="description">{$field->getDescription()|escape}</td>
						</tr>
						{foreach from=$mailing->getItemSets() item="itemset"}
							{assign var="field" value=$itemversion->getField($itemset->getId())}
							<tr>
								<td class="label"><label for="field:{$field->getId()}">{$field->getName()|escape}:</label></td>
								<td class="details">{$field->getEditor($REQUEST,$SMARTY)}</td>
								<td class="description">{$field->getDescription()|escape}</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
			{assign var="field" value=$itemversion->getField('intro')}
			<p class="htmlfield">{$field->getName()|escape}:</p>
			<div id="outer_field_{$field->getId()}">{$field->getEditor($REQUEST,$SMARTY)}</div>
<script type="text/javascript">if (initialiseTinyMCE && typeof(tinyMCE_GZ)!='undefined') initialiseTinyMCE_GZ();</script>
<script type="text/javascript">if (initialiseTinyMCE) initialiseTinyMCE();</script>
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
