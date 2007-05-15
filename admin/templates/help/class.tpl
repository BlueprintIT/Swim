{secure documents="write" login="true"}
{include file='includes/frameheader.tpl' title="SWIM Help"}
{apiget var="section" type="section" id=$request.query.section}
{apiget var="class" type="class" id=$request.query.class}
<div id="mainpane">
	<div class="header">
		<h2>{$class->getName()|escape} Help</h2>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>{$section->getName()|escape} Help</h3>
			</div>
			<div class="sectionbody">
				{$section->getDescription()|escape|nl2br}
			</div>
		</div>
		<div class="section">
			<div class="sectionheader">
				<h3>{$class->getName()|escape} Help</h3>
			</div>
			<div class="sectionbody">
				{$class->getDescription()|escape|nl2br}
			</div>
		</div>
		{foreach name="fieldlist" from=$class->getFields() item="field"}
			{if $field->getDescription()}
				<div class="section">
					<div class="sectionheader">
						<h3><a name="field_{$field->getId()}">{$field->getName()|escape} Field</a></h3>
					</div>
					<div class="sectionbody">
						{$field->getDescription()|escape|nl2br}
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
