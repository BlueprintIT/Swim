<?php
/**
* Web based SQLite management
* Show and manage 'TRIGGER' properties
* @package SQLiteManager
* @author Fr�d�ric HENNINOT
* @version $Id$ $Revision$
*/

include_once INCLUDE_LIB."SQLiteTriggerProperties.class.php";
$triggerProp = &new SQLiteTriggerProperties($workDb);
switch($action){
	case "":
	default:			
		$triggerProp->PropView();
		break;
	case "modify":
	case "add":
		$triggerProp->triggerEditForm();
		break;
	case "save":
	case "delete":
		$triggerProp->saveProp();
		break;
}	 

?>
