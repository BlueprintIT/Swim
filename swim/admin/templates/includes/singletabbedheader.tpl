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
	padding: 50px 0px 10px 0px !important;
}

#tabpanel {
  position: absolute;
  top: 10px;
  left: 0;
}
{/literal}</style>
</head>
<body>
