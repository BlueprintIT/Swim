<?

require('styles.php');
setContentType('text/css');

?>
div.adminpanel {
	padding: 5px;
	border: 2px blue solid;
	background-color: white;
}

*.highlight {
	border: 2px blue solid !important;
}

ul#sidemenu.highlight {
	margin-top: <?= $sidetopgap ?>px;
	left: 0;
	width: <?= $sidewidth ?>px;
}

div#content.highlight {
	padding-top: 8px;
	padding-right: 8px;
	padding-left: 28px;
	padding-bottom: 28px;
}

div.adminpanel a, div.adminpanel a:visited,div.adminpanel a:hover, div.adminpanel a:visited:hover {
	color: blue;
}

div.adminpanel img {
	border: 0;
}

img.icon {
	vertical-align: middle;
	padding-right: 5px;
}

div#contentadmin {
	position: absolute;
	right: 30px;
}

div#sideadmin {
	position: absolute;
	left: 0;
	z-index: 20;
}

div#menuadmin {
	position: absolute;
	bottom: <?= $menuheight ?>px;
	right: 0;
}

div#pageadmin {
	position: absolute;
	top: 0;
	left: 0;
}
