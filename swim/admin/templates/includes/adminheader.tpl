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
<style type="text/css">{literal}
body {
	padding: 140px 0px 10px 0px !important;
}

#banner {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100px;
}

#tabpanel {
  position: absolute;
  top: 100px;
  left: 0;
}
{/literal}</style>
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

{php}$this->assign_by_ref('sections', AdminManager::$sections);{/php}
  <table id="tabpanel">
    <tr>
      {foreach from=$sections item='section'}
        {if $section->isAvailable()}
          <td class="spacer"></td>
          {if $section->isSelected($REQUEST)}
            <td class="tab selected" selected="true">{$section->getName()|escape}</td>
          {else}
            <td class="tab unselected"><a href="{$section->getUrl()}">{$section->getName()|escape}</a></td>
          {/if}
        {/if}
      {/foreach}
      <td class="remainder"></td>
    </tr>
  </table>
