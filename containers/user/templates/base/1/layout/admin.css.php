<?

require('styles.php');
setContentType('text/css');

?>
div.adminpanel {
	padding: 5px;
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

div#pageadmin {
	position: absolute;
	top: 0;
	left: 0;
}
