<?

require('styles.php');
setContentType('text/css');

?>

body {
	font-family: <?= $defaultfont ?>;
	font-size: 12pt;
	font-weight: normal;
}

h1 {
	font-size: 1.5em;
	font-weight: bold;
	margin-top: 0.67em;
	margin-bottom: 0.67em;
	margin-left: 0;
	margin-right: 0;
	padding: 0;
	text-align: left;
	color: <?= $h1color ?>;
}

h2 {
	font-size: 1.3em;
	font-weight: bold;
	margin-top: 0.83em;
	margin-bottom: 0.83em;
	margin-left: 0;
	margin-right: 0;
	padding: 0;
	text-align: left;
	color: <?= $h2color ?>;
}

p {
  margin-top: 1em;
  margin-bottom: 1em;
	margin-left: 0;
	margin-right: 0;
	padding: 0;
	text-align: justify;
	font-size: 11pt;
	font-family: <?= $paragraphfont ?>;
}
