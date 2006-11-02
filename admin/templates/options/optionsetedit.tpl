{include file='includes/frameheader.tpl' title="Option Set Details"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{apiget var="selected" type="optionset" id=$request.query.optionset}
{assign var="options" value=$selected->getOptions()}
<script>
var count = {php}print(count($this->get_template_vars("options"))){/php};
{if $selected->useName()}
var usename = true;
{else}
var usename = false;
{/if}
var content = "{$CONTENT}";
{literal}
function deleteRow(row)
{
	row.parentNode.removeChild(row);
}

function createRow()
{
	count++;
	var table = document.getElementById("optionset");
	var row = document.createElement("tr");
	table.tBodies[0].appendChild(row);
	var cell = document.createElement("td");
	row.appendChild(cell);
	var input = document.createElement("input");
	if (usename) {
		input.setAttribute("type", "text");
		input.setAttribute("name", "option["+count+"].name");
		cell.appendChild(input);
		cell = document.createElement("td");
		row.appendChild(cell);
		input = document.createElement("input");
	}
	input.setAttribute("type", "text");
	input.setAttribute("name", "option["+count+"].value");
	cell.appendChild(input);
	cell = document.createElement("td");
	row.appendChild(cell);
	cell.innerHTML = '<a class="option" href="#" onclick="deleteRow(this.parentNode.parentNode); return false"><img alt="Delete row" title="Delete row" src="'+content+'/icons/delete-page-purple.gif"></a>';
}

{/literal}</script>
<div id="mainpane">
	{html_form tag_name="mainform" method="saveoptionset" optionset=$request.query.optionset}
		<div class="header">
			<table class="toolbar">
				<tr>
					<td>
						<div class="toolbarbutton">
							<a href="javascript:BlueprintIT.forms.submitForm('mainform')"><img src="{$CONTENT}/icons/save.gif"/> Save</a>
						</div>
					</td>
					<td>
						<div class="toolbarbutton">
							<a href="{encode method="admin" path="options/optionsetdetails.tpl" optionset=$request.query.optionset}"><img src="{$CONTENT}/icons/check-grey.gif"/> Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Option Set Editor</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>{$selected->getName()}</h3>
				</div>
				<div class="sectionbody">
					<table id="optionset" style="width: 100%">
						<thead>
							<tr>
								{if $selected->useName()}
									<th>Name</th>
								{/if}
								<th>Value</th>
								<th style="width: 10%"></th>
							</tr>
						</thead>
						<tbody>
						{foreach name="options" from=$options item="option"}
							<tr>
								{if $selected->useName()}
									<td><input type="text" name="option[{$smarty.foreach.options.iteration}].name" value="{$option->getName()}"></td>
								{/if}
								<td><input type="text" name="option[{$smarty.foreach.options.iteration}].value" value="{$option->getValue()}"></td>
								<td>
									<input type="hidden" name="option[{$smarty.foreach.options.iteration}].id" value="{$option->getId()}">
									<a class="option" href="#" onclick="deleteRow(this.parentNode.parentNode); return false"><img alt="Delete row" title="Delete row" src="{$CONTENT}/icons/delete-page-purple.gif"></a>
								</td>
							</tr>
						{/foreach}
						</tbody>
						<tfoot>
							<tr>
								{if $selected->useName()}
									<td colspan="2"></td>
								{else}
									<td></td>
								{/if}
								<td>
									<a class="option" onclick="createRow(); return false" href="#">
										<img alt="Add row" title="Add row" src="{$CONTENT}/icons/add-page-purple.gif">
									</a>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	{/html_form}
</div>
{include file='includes/framefooter.tpl'}
