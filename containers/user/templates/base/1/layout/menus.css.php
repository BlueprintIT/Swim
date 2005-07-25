<?

require('styles.php');
setContentType('text/css');

?>
*.menupopup {
	display: none;
	position: absolute;
	margin: 0;
	padding: 0;
}

*.menuitem {
	white-space: nowrap;
}

div.popoutright, div.popoutdown {
	display: none;
}

td.menuitem {
	background-color: <?= $menubackground ?>;
	padding-left: 18px !important;
	background-position: 0px 5px;
}

td.menufocus {
	background-color: <?= $menuhighlight ?> !important;
	background-repeat: no-repeat;
	background-image: url('bullet.gif') !important;
}

ul.menupopup {
	background-color: <?= $menubackground ?>;
	border: black 1px solid;
}

li.level1.menuitem {
	white-space: normal;
}

li.level2 {
	white-space: nowrap !important;
}

ul#sidemenu li.level1.menufocus a, ul#sidemenu li.level1.menufocus a:visited, ul#sidemenu li.level1.menufocus a:hover, ul#sidemenu li.level1.menufocus a:visited:hover {
	color: <?= $menuhighlight ?>;
}

ul#sidemenu li.level2 a, ul#sidemenu li.level2 a:visited {
	color: <?= $bordertextcolor ?> !important;
}

ul.menupopup li.menuitem {
	display: block;
	padding: 2px !important;
	padding-left: 18px !important;
	margin: 0 !important;
	border: 1px black solid;
	background-position: 0px 45% !important;
	background-image: none !important;
}

ul.menupopup li.menufocus {
	background-color: <?= $menuhighlight ?>;
	background-repeat: no-repeat;
	background-image: url('bullet.gif') !important;
}
