{include file='includes/frameheader.tpl' title="Option Set Details"}
<div id="mainpane">
	{apiget var="selected" type="optionset" id=$request.query.optionset}
	<div class="header">
		<table class="toolbar">
			<tr>
				<td>
					<div class="toolbarbutton">
						<a href="{encode method="admin" path="options/optionsetedit.tpl" optionset=$request.query.optionset}">Edit this set</a>
					</div>
				</td>
			</tr>
		</table>
		<h2>Option Set Details</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>{$selected->getName()}</h3>
			</div>
			<div class="sectionbody">
				<table>
					<thead>
						<tr>
							{if $selected->useName()}
								<th>Name</th>
							{/if}
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
					{foreach from=$selected->getOptions() item="option"}
						<tr>
							{if $selected->useName()}
								<td>{$option->getName()}</td>
							{/if}
							<td>{$option->getValue()}</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
{include file='includes/framefooter.tpl'}
