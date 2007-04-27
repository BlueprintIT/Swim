{secure contacts="read" login="true"}
{include file='includes/frameheader.tpl' title="Mail Details"}
{apiget var="item" type="item" id=$request.query.item}
{assign var="variant" value="default"}
{assign var="section" value=$item->getSection()}
{assign var="itemvariant" value=$item->getVariant($variant)}
{if $itemvariant->getCurrentVersion()}
	{assign var="itemversion" value=$itemvariant->getCurrentVersion()}
{else}
	{assign var="itemversion" value=$itemvariant->getDraftVersion()}
{/if}
{assign var="class" value=$itemversion->getClass()}
{assign var="mailing" value=$class->getMailing()}
{request var="details" method="admin" path="mailing/details.tpl" item=$item->getId()}
<div id="mainpane">
	<div class="header">
		{secure contacts="write"}
			<table class="toolbar">
				<tr>
					{if !$itemversion->isComplete()}
						<td>
							<div class="toolbarbutton">
								<a href="{encode method="admin" path="mailing/edit.tpl" item=$item->getId()}">
									<img src="{$CONTENT}/icons/edit-page-blue.gif"/>
									Edit
								</a>
							</div>
						</td>
						<td>
							<div class="toolbarbutton">
								<a href="{encode method="sendmail"  item=$item->getId() nested=$details}">
									<img src="{$CONTENT}/icons/save.gif"/>
									Send
								</a>
							</div>
						</td>
					{/if}
					<td>
						<div class="toolbarbutton">
							{assign var="itemid" value=$item->getId()}
							{assign var="variantname" value=$itemvariant->getVariant()}
							{assign var="version" value=$itemversion->getVersion()}
							<a target="_blank" href="{encode method="preview" path="$itemid/$variantname/$version"}">
								<img src="{$CONTENT}/icons/web-page-green.gif"/>
								Preview
							</a>
						</div>
					</td>
				</tr>
			</table>
		{/secure}
		<h2>Mail Details</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionbody">
				<table class="admin">
					{assign var="field" value=$itemversion->getField('name')}
					<tr>
						<td class="label">{$field->getName()|escape}:</td>
						<td class="details">{$field->output($REQUEST,$SMARTY)}</td>
					</tr>
					{if $itemversion->isComplete()}
						{assign var="field" value=$itemversion->getField('date')}
						<tr>
							<td class="label">{$field->getName()|escape}:</td>
							<td class="details">{$field->output($REQUEST,$SMARTY)}</td>
						</tr>
					{/if}
					{foreach from=$mailing->getItemSets() item="itemset"}
						{assign var="field" value=$itemversion->getField($itemset->getId())}
						<tr>
							<td class="label">{$field->getName()|escape}:</td>
							<td class="details">{$field->output($REQUEST,$SMARTY)}</td>
						</tr>
					{/foreach}
				</table>
			</div>
		</div>
		{assign var="field" value=$itemversion->getField('intro')}
		<p class="htmlfield">{$field->getName()|escape}:</p>
		{$field->output($REQUEST,$SMARTY)}
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
