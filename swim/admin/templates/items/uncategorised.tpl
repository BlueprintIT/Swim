{secure documents="read" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{apiget var="section" type="section" id=$request.query.section}
<script>
  window.top.SiteTree.selectItem('uncat');
</script>
<div id="mainpane">
	<div class="header">
		<h2>Uncategorised Items</h2>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>Contents</h3>
			</div>
			<div class="sectionbody">
				{secure documents="write"}
				{assign var="choices" value=$section->getVisibleClasses()}
				{if count($choices)>0}
					{html_form method="createitem"  targetsection=$section->getId() targetvariant=$session.variant}
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
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
