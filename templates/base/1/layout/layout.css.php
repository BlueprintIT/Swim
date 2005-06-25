<?

require('styles.php');
setContentType('text/css');

?>
html {
	height: 100%;
	width: 100%;
	margin: 0;
	padding: 0;
}

body {
	height: 100%;
	width: 100%;
	margin: 0;
	padding: 0;
	font-size: 1em;
	background-color: white;
	background-image: url('background.gif');
	background-position: top left;
	background-repeat: no-repeat;
}

div#header {
	margin: 0;
	padding: 0;
	border: 0;
	height: <?= $headerheight ?>px;
	width: 100%;
	background-image: url('banner.jpg');
	background-position: top left;
	background-repeat: repeat-x;
}

img#cogs {
	position: absolute;
	top: 0;
	left: 0;
	border: 0;
	padding: 0;
	margin: 0;
	border-right: <?= $spacing ?>px white solid;
}

img#logo {
	position: relative;
	left: 180px;
	top: 40px;
	width: 575px;
	height: 71px;
	border: 0;
	padding: 0;
	margin: 0;
}

div#menubar {
	margin: 0;
	padding: 0;
	border-top: <?= $spacing ?>px white solid;
	border-bottom: <?= $spacing ?>px white solid;
	height: <?= $menuheight ?>px;
	width: 100%;
	background-color: #004ef4;
	z-index: 10;
}

div#spacing {
	border-left: <?= $spacing ?>px solid white;
	margin-left: <?= $sidewidth ?>px;
	height: 100%;
}

div#menubar table {
	margin: 0;
	border: 0;
	padding: 0;
	width: 100%;
	height: 100%;
	border-collapse: collapse;
}

div#menubar tr {
	height: auto;
}

div#menubar td {
	width: <?= 100/6 ?>%;
	border-top: 0;
	border-bottom: 0;
	border-left: 1px white solid;
	border-right: 1px white solid;
	padding: 0;
	margin: 0;
	background-color: <?= $menubackground ?>;
	color: <?= $bordertextcolor ?>;
	font-family: <?= $borderfont ?>;
}

div#menubar td a, div#menubar td a:visited {
	border: 0;
	padding: 0;
	margin: 0;
	height: 100%;
	width: 100%;
	font-size: 0.8em;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

div#menubar td a:hover, div#menubar td a:visited:hover {
	border: 0;
	padding: 0;
	margin: 0;
	height: 100%;
	width: 100%;
	font-size: 0.8em;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

ul#sidemenu {
	margin: 0;
	padding: 0;
	padding-top: <?= $sidetopgap ?>px;
	position: absolute;
	width: <?= $sidewidth ?>px;
	background: transparent;
}

ul#sidemenu li {
	display: block;
	padding-left: 20px;
	padding-bottom: 10px;
	margin-bottom: 20px;
	width: auto;
	font-family: <?= $borderfont ?>;
	color: <?= $bordertextcolor ?>;
	background-image: url('sidemenuline.gif');
	background-position: bottom right;
	background-repeat: no-repeat;
}

ul#sidemenu a, ul#sidemenu a:visited {
	display: block;
	width: auto;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

ul#sidemenu a:hover, ul#sidemenu a:visited:hover {
	display: block;
	width: auto;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

div#body {
	padding: 0;
	margin: 0;
	background-image: url('side.gif');
	background-position: top left;
	background-repeat: repeat-y;
}

div#content {
	margin-left: <?= $sidewidth+$spacing ?>px;
	padding-left: 30px;
	margin-right: 0px;
	padding-right: 20px;
	overflow: auto;
}

div#footer {
	margin: 0;
	padding: 0;
	width: 100%;
	position: relative;
	height: <?= $footerheight ?>px;
	background-color: <?= $yellowborder ?>;
}

img#curve {
	position: absolute;
	top: -104px;
	left: <?= $sidewidth ?>px;
	margin: 0;
	padding: 0;
	border: 0;
	width: 103px;
	height: 104px;
}

img#wheels {
	position: absolute;
	margin: 0;
	border: 0;
	padding: 0;
	left: 20px;
	bottom: 20px;
	width: 145px;
	height: 140px;
}

div#footer p {
	position: absolute;
	margin: 0;
	padding: 0;
	top: 30px;
	left: 190px;
	letter-spacing: 0.075em;
	font-family: <?= $borderfont ?>;
	color: <?= $bordertextcolor ?>;
	font-size: 1.2em;
}

img#iip {
	position: absolute;
	margin: 0;
	border: 0;
	padding: 0;
	right: 10px;
	bottom: 10px;
	width: 124px;
	height: 85px;
}
