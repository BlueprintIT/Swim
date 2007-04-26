{secure contacts="read" login="true"}
{include file='includes/frameheader.tpl' title="Content management"}
{apiget var="section" type="section" id=$request.query.section}
{assign var="mailing" value=$section->getMailing($request.query.mailing)}
<div id="mainpane">
	<div class="header">
		<h2>Mailing Details</h2>
		<div style="clear: left"></div>
	</div>
	<div class="body">
		<div class="section first">
			<div class="sectionheader">
				<h3>{$mailing->getName()}</h3>
			</div>
			<div class="sectionbody">
				<table class="admin">
					<tr>
						<td class="label">Subject:</td>
						<td class="details">{$mailing->getSubject()}</td>
					</tr>
				</table>
			</div>
		</div>
		<p class="htmlfield">Introduction:</p>
		{stylesheet method="layout" path="styles/text.css" CONTEXT=".content"}
		<div id="field_content" class="content">{$mailing->getIntro()}</div>
	</div>
</div>
{include file='includes/framefooter.tpl'}
{/secure}
