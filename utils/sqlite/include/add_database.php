<?php
/**
* Web based SQLite management
* add database form
* check if the database is Ok
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

$tempError = error_reporting();
error_reporting(E_ALL & ~(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE));
$dbFilename = '';
if($action == 'saveDb'){
	$error = false;
	if(!empty($_POST['dbname']) && !empty($_POST['dbpath'])){
		if(isset($_POST['dbpath'])) $dbFilename = stripslashes($_POST['dbpath']);
		if ($_POST['uploadDB']){
			if(is_dir(DEFAULT_DB_PATH) && is_writable(DEFAULT_DB_PATH)){
				if(move_uploaded_file($_FILES['dbRealpath']['tmp_name'], DEFAULT_DB_PATH.$_FILES['dbRealpath']['name'])) $dbFilename = DEFAULT_DB_PATH.$_FILES['dbRealpath']['name'];
			} else {
				$error = true;
				$message = '<li><span style="color: red; font-size: 11px;">'.$GLOBALS['traduct']->get(144).'</span></li>';
			}
		}
		if(DEMO_MODE && $_POST['dbname'] != ':memory:') $dbFilename = DEFAULT_DB_PATH.basename(str_replace("\\", '/', $dbFilename));
		$tempDir = dirname($dbFilename);
		if($tempDir == '.') $dbFile = DEFAULT_DB_PATH . $dbFilename;
		else $dbFile = $dbFilename;
		if(!$error && $newDb = @sqlitem_open($dbFile, 0666, $errorString)){
			@sqlitem_close($newDb);
			$query = 'INSERT INTO database (name, location) VALUES ('.quotes(stripslashes($_POST['dbname'])).', '.quotes($dbFilename).')';
			if(!SQLiteExecFunction($db, $query)) {				
				$error = true;
				$message .= '<li><span style="color: red; font-size: 11px;">'.$GLOBALS['traduct']->get(100).'</span></li>';
			} else {
				if(DEBUG) $dbsel = sqlitem_last_insert_rowid($db);
				else $dbsel = @sqlitem_last_insert_rowid($db);
			}
		} else {			
			$error = true;
			$message .= '<li><span style="color: red; font-size: 11px;">'.$GLOBALS['traduct']->get(101).'</span></li>';
		}
	} else {
		$error = true;
		$message .= '<li><span style="color: red; font-size: 11px;">'.$GLOBALS['traduct']->get(102).'</span></li>';
	}
}
error_reporting($tempError);
if(!READ_ONLY && (!WITH_AUTH || (isset($SQLiteManagerAuth) &&  $SQLiteManagerAuth->getAccess('properties')))) {
	if(empty($action) || ($action=='passwd') || $error){
		if(!isset($_POST['dbname'])) 	$_POST['dbname'] = '';
		if(!isset($_POST['dbpath'])) 	$_POST['dbpath'] = '';
		if(!isset($_POST['dbFilename'])) $_POST['dbFilename'] = '';
		echo '	<table width="400">
				<form name="database" enctype="multipart/form-data" method="POST">
				<tr><td colspan="2" align="center">'.$GLOBALS['traduct']->get(103).'</td></tr>';
		if($error) echo '<tr><td colspan="2" align="center">'.$GLOBALS['traduct']->get(9).' : '.((isset($message))? $message : 'unknown' ).'</td></tr>';
		echo '	<tr><td align="right">'.$GLOBALS['traduct']->get(19).' :&nbsp;</td><td><input type="text" class="text" name="dbname" value="'.$_POST['dbname'].'" size="20"></td></tr>
				<tr><td align="center" colspan="2"><hr width="80%"</td></tr>
				<tr><td align="right" rowspan="3" valign="absmiddle" nowrap="nowrap">'.$GLOBALS['traduct']->get(104).' :&nbsp;</td><td><input type="file" class="file" name="dbRealpath" value="'.$_POST['dbpath'].'" size="20" onChange="checkPath();"></td></tr>
				<tr><td><input type="checkbox" name="uploadDB" value=1>&nbsp;'.$GLOBALS['traduct']->get(143).'</td></tr>
				<tr><td><input type="text" class="text" name="dbpath" value="'.$dbFilename.'" size="40"></td></tr>
				<tr><td colspan="2" align="center"><input class="button" type="submit" value="'.$GLOBALS['traduct']->get(51).'"></td></tr>
				<input type="hidden" name="action" value="saveDb">
				</form>
				</table>';
	} else {
		if(!$noDb) echo "<script>parent.main.location='main.php?dbsel=$dbsel'; parent.left.location='left.php?dbsel=$dbsel';</script>";
		else echo "<script>document.location='index.php?dbsel=$dbsel';</script>";
	}
}
?>
