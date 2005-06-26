<?

require('styles.php');
setContentType('text/css');

?>
div.adminpanel {
	width: 50px;
	height: 30px;
	background-color: red;
}

div#contentadmin {
	position: absolute;
	right: 0;
}

div#sideadmin {
	position: absolute;
	left: 0;
}

div#menuadmin {
	position: absolute;
	top: <?= $headerheight-30 ?>px;
	right: 0;
}
