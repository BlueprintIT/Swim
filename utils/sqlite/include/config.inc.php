<?php
/**
* Web based SQLite management
* Check if the config database is OK
* and set a tab with the list of user's databases
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

include_once INCLUDE_LIB."grab_global.php";
include_once INCLUDE_LIB."SQLite.i18n.php";
include_once INCLUDE_LIB."common.lib.php";

function LastAction() {
  global $workDb;
  if(isset($workDb))
   if ($workDb->connId && ($workDb->baseName!=":memory:")) {
	  $workDb->close();
	  @sqlitem_close($db);
  }
}
register_shutdown_function('LastAction');

if(isset($noframe)){
	session_register("noframe");
	$_SESSION["noframe"] = $noframe = true;
}

if(!file_exists("./theme/".$localtheme."/define.php")) {
	unset($_COOKIE["SQLiteManager_currentTheme"]);
	$localtheme = "default";
}
include_once("./theme/".$localtheme."/define.php");

if(!SQLiteCheckOk()){
	displayError($traduct->get(6));
	exit;	
} else {
	$tempError = error_reporting();
	error_reporting(E_ALL & ~(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE));
	if(!$db = @sqlitem_open(SQLiteDb, 0666, $error)){
		displayError($traduct->get(7)." : $error");
		exit;
	}
	
	define("READ_ONLY", !is_writeable(SQLiteDb));
	/*
	if(!is_writeable(SQLiteDb)){	
		displayError($traduct->get(8));
		exit;
	}
	*/
	
	error_reporting($tempError);
	
	if(WITH_AUTH){
		include_once INCLUDE_LIB."SQLiteAuth.class.php";
		$SQLiteManagerAuth =& new SQLiteAuth();
	}

	$query = "SELECT count(*) FROM database";
	if($res = SQLiteExecFunction($db, $query)){
			if(!SQLiteFetchFunction($res)){
				displayHeader("");
				$noDb = true;
				include_once INCLUDE_LIB."add_database.php";
				if(empty($action) || $error) exit;
			}
	}
	// check if exist ':memory: database
	$query = "SELECT * FROM database WHERE location LIKE ':memory:'";
	if($resMem = SQLiteExecFunction($db, $query)){
		$tempMem = @sqlitem_popen(":memory:", 0666, $error);
	}
	
	$tabDb = SQLiteArrayFunction($db, $query, SQLITE_ASSOC);

	$tabSqliteVersion = SQLiteArrayFunction($db, "SELECT sqlite_version();");
	$SQLiteVersion = $tabSqliteVersion[0]["sqlite_version()"];
	if($dbsel){
		$tabInfoDb = SQLiteArrayFunction($db, "SELECT * FROM database WHERE id=$dbsel", SQLITE_ASSOC);
		$tabInfoDb = isset($tabInfoDb[0])?$tabInfoDb[0]:'';
	}
	
	$existAttachTable = SQLiteArrayFunction($db, "SELECT name FROM sqlite_master WHERE type='table' AND name='attachment';", SQLITE_ASSOC);
	if(empty($existAttachTable)) {
		// create table for attachment management
		$query = "CREATE TABLE attachment (
					id INTEGER PRIMARY KEY ,
					base_id INTEGER ,
					attach_id INTEGER) ;";
		SQLiteExecFunction($db, $query);
	}
	$attachDbList = array();
	$attachLocation = array();
	if(!empty($dbsel)){
		// Get attach database list for dbsel
		$query = "SELECT attach_id, location, name FROM attachment LEFT JOIN database ON database.id=attachment.attach_id WHERE base_id=".$dbsel;
		$attachList = SQLiteArrayFunction($db, $query, SQLITE_ASSOC);
		$attachDbList = array();
		$attachInfo = array();
		foreach($attachList as $key=>$value) {
			$attachDbList[] = $value["attach_id"];	
			$attachInfo[$value["attach_id"]]["location"] = $value["location"];
			$attachInfo[$value["attach_id"]]["name"] = $value["name"];
		}	
	}		
}

?>
