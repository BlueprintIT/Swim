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
