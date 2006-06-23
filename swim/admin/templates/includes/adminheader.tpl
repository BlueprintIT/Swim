<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

{php}
function display_tab($title,$url,$selected)
{
  if ($selected)
  {
  	print('<td class="selected">'.$title.'</td>');
  }
  else
  {
  	print('<td><a href="'.$url.'">'.$title.'</a></td>');
  }
}
{/php}
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="Content-Script-Type" content="text/javascript">
  <title>{$title}</title>
  {stylesheet method="admin" path="styles/admin.css"}
<style type="text/css">{literal}
body {
	padding-top: 140px;
	padding-bottom: 10px;
	padding-left: 0px;
	padding-right: 0px;
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
</div>

<table id="tabpanel">
<tr>
{php}
foreach (AdminManager::$sections as $section)
{
  if ($section->isAvailable())
  {
  	print('<td class="spacer"></td>');
    display_tab($section->getName(), $section->getUrl(), $section->isSelected($this->get_template_vars('REQUEST')));
  }
}
{/php}
  <td class="remainder"></td>
</tr>
</table>