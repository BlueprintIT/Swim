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
}

td.menufocus {
	background-color: <?= $menuhighlight ?> !important;
}

ul.menupopup {
	background-color: <?= $menubackground ?>;
	border: black 1px solid;
}

ul.menupopup li.menuitem {
	display: block;
	padding: 2px;
	margin: 0;
	border: 1px black solid;
}

ul.menupopup li.menufocus {
	background-color: <?= $menuhighlight ?>;
}
