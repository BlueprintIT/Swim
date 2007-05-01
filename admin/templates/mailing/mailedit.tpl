{secure contacts="write" login="true"}
{include file='includes/frameheader.tpl' title="Edit Mailing"}
{script href="/swim/tinymce/jscripts/tiny_mce/tiny_mce_gzip.js"}
{script href="$CONTENT/scripts/tinymce.js"}
{script href="$SHARED/scripts/BlueprintIT`$smarty.config.YUI`.js"}
{script href="$SHARED/scripts/forms`$smarty.config.YUI`.js"}
{apiget var="section" type="section" id=$request.query.section}
{assign var="mailing" value=$section->getMailing($request.query.mailing)}
<script type="text/javascript">
window.top.SiteTree.selectItem("mailing_{$request.query.mailing}");
</script>
<div id="mainpane">
	{html_form tag_name="mainform" method="savemailing" section=$section->getId() mailing=$mailing->getId()}
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
							<a href="{encode method="admin" path="mailing/maildetails.tpl" section=$section->getId() mailing=$mailing->getId()}"><img src="{$CONTENT}/icons/delete-page-blue.gif"/> Cancel</a>
						</div>
					</td>
				</tr>
			</table>
			<h2>Edit Mailing</h2>
			<div style="clear: left"></div>
		</div>
		<div class="body">
			<div class="section first">
				<div class="sectionheader">
					<h3>{$mailing->getName()}</h3>
				</div>
				<div class="sectionbody">
				</div>
			</div>
			<p class="htmlfield">Introduction:</p>
			<div id="outer_field_intro">
				<textarea class="HTMLEditor" style="width: 100%; height: 400px;" id="intro" name="intro">{$mailing->getIntro()}</textarea>
				<script type="text/javascript">
					tinyMCEparams.content_css+="{encode method="layout" path="styles/text.css" CONTEXT=".intro"}";
					tinyMCEparams.advblockformat_stylesurl="{$SITECONTENT}/content.xml";
				</script>
			</div>
<script type="text/javascript">if (initialiseTinyMCE && typeof(tinyMCE_GZ)!='undefined') initialiseTinyMCE_GZ();</script>
<script type="text/javascript">if (initialiseTinyMCE) initialiseTinyMCE();</script>
		</div>
	</div>
{/html_form}
</div>
{include file='includes/framefooter.tpl'}
{/secure}
