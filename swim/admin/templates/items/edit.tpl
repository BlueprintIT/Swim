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
<script>{literal}
function submitForm(form)
{
  document.forms[form].submit();
}
{/literal}</script>
<div id="mainpane">
	{html_form tag_name="mainform" method="saveitem" itemversion=$itemversion->getId()}
		<div class="header">
			<div class="toolbar">
				<div class="toolbarbutton">
					<a href="javascript:submitForm('mainform')">Save</a>
				</div>
				<div class="toolbarbutton">
					<a href="{encode method="admin" path="items/details.tpl" item=$item->getId() version=$itemversion->getVersion()}">Cancel</a>
				</div>
			</div>
			<h2>Item Editor</h2>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>Item Options</h3>
				</div>
				<div class="sectionbody">
					<table class="admin">
						{foreach from=$class->getFields($itemversion) item="field"}
							{if $field->getType()!='html' && $field->getType()!='sequence'}
								<tr>
									<td class="label"><label for="field:{$field->getId()}">{$field->getName()}:</label></td>
									<td class="details">{$field->getEditor()}</td>
									<td class="description">{$field->getDescription()}</td>
								</tr>
							{/if}
						{/foreach}
					</table>
				</div>
			</div>
			{assign var="pos" value="0"}
			{foreach name="fieldlist" from=$class->getFields($itemversion) item="field"}
				{if $field->getType()=='html'}
					{if $pos==0}
						<div class="section">
							<div class="sectionheader">
								<h3>Item Content</h3>
							</div>
							<div class="sectionbody">
						{assign var="pos" value="1"}
					{/if}
					<div id="field_{$field->getId()}">{$field->getEditor()}</div>
				{/if}
			{/foreach}
			{if pos==1}
					</div>
				</div>
			{/if}
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
