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
	margin: 0;
	padding: 0;
	padding-top: <?= $headerheight+$spacing ?>px;
	padding-bottom: <?= $footerheight ?>px;
	overflow: hidden;
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
	position: absolute;
	top: 0;
	left: 0;
	height: <?= $headerheight ?>px;
	width: 100%;
	background-image: url('banner.jpg');
	background-position: <?= $sidewidth+$spacing ?>px 0;
	background-repeat: repeat-x;
	z-index: 10;
}

div#swim {
	position: absolute;
	top: 0;
	border: 2px solid blue;
	left: 25%;
	width: 50%;
	background: white;
	opacity: 0.9;
}

div#swim p {
	color: blue;
	padding: 0;
	margin: 0;
}

div#swim img {
	vertical-align: middle;
}

img#cogs {
	position: absolute;
	top: 0;
	left: 0;
	border: 0;
	padding: 0;
	margin: 0;
	width: 150px;
	height: <?= $headerheight ?>;
	border-right: <?= $spacing ?>px white solid;
}

img#logo {
	position: relative;
	left: 180px;
	top: 30px;
	width: 575px;
	height: 71px;
	border: 0;
	padding: 0;
	margin: 0;
}

div#menubar {
	margin: 0;
	padding: 0;
	position: absolute;
	top: <?= $headerheight-$menuheight ?>px;
	left: 0;
	height: <?= $menuheight ?>px;
	width: 100%;
	z-index: 10;
}

div#spacing {
	margin-left: <?= $sidewidth ?>px;
	height: 100%;
}

div#menubar table {
	margin: 0;
	border: 0;
	border-top: 1px white solid;
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
	border-right: 0;
	padding: 0;
	margin: 0;
	background-color: <?= $menubackground ?>;
	color: <?= $bordertextcolor ?>;
	font-weight: bold;
	font-family: <?= $borderfont ?>;
	font-size: 0.8em;
}

div#menubar td a, div#menubar td a:visited {
	border: 0;
	padding: 0;
	margin: 0;
	height: 100%;
	width: 100%;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

div#menubar td a:hover, div#menubar td a:visited:hover {
	border: 0;
	padding: 0;
	margin: 0;
	height: 100%;
	width: 100%;
	text-decoration: none;
	color: <?= $bordertextcolor ?>;
}

h1#sidetitle {
	font-size: 1em;
	font-weight: bold;
	padding-left: 5px;
	width: <?= $sidewidth-2 ?>px;
	background: transparent;
	position: absolute;
}

ul#sidemenu {
	margin: 0;
	margin-top: <?= $sidetopgap+2 ?>px;
	padding: 0;
	position: absolute;
	left: 2;
	z-index: 10;
	width: <?= $sidewidth-2 ?>px;
	background: <?= $yellowborder ?>;
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
	border-left: <?= $sidewidth ?>px solid <?= $yellowborder ?>;
	height: 100%;
	overflow: auto;
}

div#print {
	padding-right: 2px;
	padding-top: 2px;
	float: right;
}

div#print img#printicon {
	width: 30px;
	height: 26px;
	border: 0px none;
}

div#content {
	padding-top: 10px;
	padding-right: 10px;
	padding-left: 30px;
	padding-bottom: 30px;
	margin: 0;
}

div#footer {
	margin: 0;
	padding: 0;
	bottom: 0;
	width: 100%;
	position: absolute;
	height: <?= $footerheight ?>px;
	background-color: <?= $yellowborder ?>;
}

div#base {
	margin-left: 190px;
	padding-top: 10px;
	padding-right: 5px;
}

div#curve {
	position: absolute;
	bottom: <?= $footerheight ?>px;
	left: <?= $sidewidth ?>px;
	margin: 0;
	padding: 0;
	border: 0;
	width: 103px;
	height: 104px;
	background-image: url('curve.gif');
}

p#mission {
	font-weight: bold;
	margin: 0;
	padding: 0;
	letter-spacing: 0.075em;
	font-family: <?= $borderfont ?>;
	color: <?= $bordertextcolor ?>;
	font-size: 1.2em;
}

p#copyright {
	font-size: 0.8em;
	padding: 0;
	padding-top: 5px;
	margin: 0;
	color: <?= $bordertextcolor ?>;
}

p#copyright a, p#copyright a:visited {
	color: <?= $bordertextcolor ?>;
}

p#copyright a:hover, p#copyright a:visited:hover {
	color: <?= $bordertextcolor ?>;
}

img#iip {
	float: right;
	margin: 0;
	border: 0;
	padding: 0;
	width: 73px;
	height: 50px;
}

img#wheels {
	position: absolute;
	bottom: 5px;
	left: 10px;
	margin: 0;
	border: 0;
	padding: 0;
	left: 20px;
	bottom: 20px;
	width: 145px;
	height: 140px;
}
