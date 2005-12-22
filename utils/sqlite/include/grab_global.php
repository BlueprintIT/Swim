<?php
/**
* Web based SQLite management
* Export and init variable
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

if (!empty($_GET)) {
	extract($_GET);
}
if (!empty($_POST)) {
	extract($_POST);
}

// Notice Error Management
if(!isset($dbsel)) 				$dbsel = "";
if(!isset($action)) 				$action = "";
if(!isset($table)) 				$table = "";
if(!isset($TableName)) 			$TableName = "";
if(!isset($view)) 				$view = "";
if(!isset($ViewName)) 			$ViewName = "";
if(!isset($trigger)) 			$trigger = "";
if(!isset($TriggerName)) 		$TriggerName = "";
if(!isset($function)) 			$function = "";	
if(!isset($index_action)) 		$index_action = "";
if(!isset($export_action)) 	$export_action = "";
if(!isset($option_action)) 	$option_action = "";
?>
