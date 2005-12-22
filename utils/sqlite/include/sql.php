<?php
/**
* Web based SQLite management
* Manage manual query and file query
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

if(!empty($_FILES["sqlFile"]["tmp_name"])){
	$fp = fopen($_FILES["sqlFile"]["tmp_name"], "r");
	$DisplayQuery = fread($fp, $_FILES["sqlFile"]["size"]);
}
include INCLUDE_LIB."browse.php";

?>
