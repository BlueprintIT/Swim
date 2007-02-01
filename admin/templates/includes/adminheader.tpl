{config_load file="admin.conf" scope="global"}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Script-Type" content="text/javascript">
  <title>{$title}</title>
  {stylesheet href="$SHARED/yui/reset/reset-min.css"}
  {stylesheet href="$SHARED/yui/fonts/fonts-min.css"}
  {stylesheet method="admin" path="styles/header.css"}
  {stylesheet method="admin" path="styles/body.css"}
</head>
<body>

  <div id="banner">
    {include file='brand:banner.tpl'}
      <div id="admin">
        {if $USER->isLoggedIn()}
          <p style="margin: 0">Logged in as {$USER->getName()}</p>
          <p style="margin: 0"><a href="{encode method='admin' path='password.tpl'}">Change Password</a> <a href="{encode method='logout' nestcurrent='true'}">Logout</a></p>
        {/if}
      </div>
  </div>

{php}$this->assign_by_ref('sections', AdminManager::getAvailableSections());{/php}
{if count($sections)>0}
  <div id="tabpanel">
	  <table>
	    <tr>
	      {foreach from=$sections item='section'}
	        {if $section->isAvailable()}
	          <td class="spacer"></td>
	          {if $section->isSelected($REQUEST)}
	            <td class="tab selected" selected="true">
	            	<div class="tableft">
	            		<div class="tabright">
	            			<img src="{$section->getIcon()}" alt="{$section->getName()|escape}">{$section->getName()|escape}
	            		</div>
	            	</div>
	            </td>
	          {else}
	            <td class="tab unselected" onclick="document.location.href='{$section->getUrl()}'" onmouseover="this.className='tab hover'" onmouseout="this.className='tab unselected'">
	            	<div class="tableft">
	            		<div class="tabright">
	            			<a href="{$section->getUrl()}">
	            				<img src="{$section->getIcon()}" alt="{$section->getName()|escape}">{$section->getName()|escape}
	            			</a>
	            		</div>
	            	</div>
	            </td>
	          {/if}
	        {/if}
	      {/foreach}
	    </tr>
	  </table>
  </div>
{/if}

