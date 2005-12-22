<?php
/**
* Web based SQLite management
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/
if(!session_start()){
	if (!is_writable(session_save_path())) {
	  if (strstr($_SERVER['SCRIPT_FILENAME'],'free.fr')) { 
	    //activation des sessions sur free.fr
	    if (!is_dir(session_save_path())) {
	       if (mkdir(session_save_path(),0700)) session_start();
	    }
	  } else displayError('ERROR : session directory not writable : '.session_save_path());
	}
}
include_once 'include/defined.inc.php';
include_once INCLUDE_LIB.'config.inc.php';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<title>SQLiteManager</title>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" >
</head>
<frameset cols="<?php echo LEFT_FRAME_WIDTH ?>,*">
<frame src="left.php?<?php echo arrayToGet($_GET) ?>" name="left" scrolling="auto">
<frame src="main.php?<?php echo arrayToGet($_GET) ?>" name="main" scrolling="auto">
<noframes>
<script type="text/javascript">if(!document.frames) window.location='main.php?noframe';</script>
</noframes>
</frameset>
</html>
