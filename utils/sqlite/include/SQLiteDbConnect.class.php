<?php
/**
* Web based SQLite management
* Class for manage connection to a user database
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

class SQLiteDbConnect {
	
	/**
	* Database name
	*
	* @access private
	* @var strig
	*/
	var $baseName;
	
	/**
	* Resource connection
	* 
	* @access public
	* @var resource
	*/
	var $connId;
	
	/**
	* Result resource
	* 
	* @access public
	* @var resource
	*/
	var $resId;
	
	/**
	* Error from connection to database
	*
	* @access public
	* @var bool
	*/
	var $connError;
	
	/**
	* User defines function list
	*
	* @access public
	* @var array
	*/
	var $functInfo;	
	
	/**
	* Last query Execution Time (msec)
	*
	* @access public
	* @var integer
	*/
	var $queryTime;
	
	/**
	* Information if the database is writable or not
	* @access private
	* @var boolean
	*/
	var $readOnly;
		
	/**
	* Class constructor
	*
	* @access public
	* @param string $base database name
	*/
	function SQLiteDbConnect($base){
		$this->baseName = $base;
		$this->readOnly = !is_writeable($base);
		if($base == ':memory:') {
			$this->readOnly = false;
			if(DEBUG) $this->connId = sqlitem_popen($base, 0666, $this->connError);
			else $this->connId = @sqlitem_popen($base, 0666, $this->connError);
		} else {
			if(DEBUG) $this->connId = sqlitem_open($base, 0666, $this->connError);
			else $this->connId = @sqlitem_open($base, 0666, $this->connError);
		}
		return $this->connId;
	}
	
	/**
	* return true if database is writeable
	*
	* @access public
	*/
	function isReadOnly(){
		return $this->readOnly;
	}
	
	/**
	* Return database properties
	*
	* @access public
	* @param string $type type of properties (table, view...)
	*/
	function getPropList($type){
		$propList = array();
		if($type!='Function'){
			$query = 'SELECT name FROM (SELECT * FROM sqlite_master UNION SELECT * FROM sqlite_temp_master) WHERE type='.quotes(strtolower($type)).' ORDER BY name;';
			if($this->getResId($query)) {
				while($ligne = SQLiteFetchFunction($this->resId, SQLITE_ASSOC))  {
					$propList[] = $ligne['name'];
				}
			}
		} else {
			$query = 'SELECT funct_name FROM user_function WHERE (base_id='.$GLOBALS['dbsel'].' OR base_id IS NULL)';
			if( $res = SQLiteExecFunction($GLOBALS['db'], $query) ){
				while($ligne = SQLiteFetchFunction($res, SQLITE_ASSOC)) $propList[] = $ligne['funct_name'];
			}				
		}
		return $propList;
	}
	
	/**
	* Close connection to database
	*
	* @access public
	*/
	function close(){
		if(DEBUG) sqlitem_close($this->connId);
		else @sqlitem_close($this->connId);
		return;
	}
	
	/**
	* Exec a query and return the resource result
	*
	* @access public
	* @param string $query 
	* @param bool $buffer
	*/
	function getResId($query, $buffer=true){
		$time_start = microtime();
    if($buffer) {
			if(DEBUG) $this->resId = sqlitem_query($this->connId, $query);
			else $this->resId = @sqlitem_query($this->connId, $query);
		} else {
			if(DEBUG) $this->resId = sqlitem_unbuffered_query($this->connId, $query);
			else $this->resId = @sqlitem_unbuffered_query($this->connId, $query);
		}
		$this->queryTime = round((microtime() - $time_start) * 1000,2);
		return $this->resId;		
	}
	
	/**
	* return array of result
	*
	* @access public
	* @param string $type
	*/
	function getArray($type = ''){
		if(is_resource($this->resId) || is_object($this->resId)){
			$tabOut = array();
			while($row = SQLiteFetchFunction($this->resId, (($type=='')? SQLITE_ASSOC : $type ))) $tabOut[] = $row;
			return $tabOut;
		} else {
			return false;
		}
	}
	
	/**
	* Manage ALTER TABLE, not exist in SQLite
	* 
	* @access public
	* @param string $table Table name
	* @param string $newDefinition SQL definition of CREATE TABLE
	* @param array $oldColumn Array of all the column
	* @param array $newColumn Array of all new column
	*/
	function alterTable($table, $newDefinition, $oldColumn, $newColumn, $nullToNotNull = array()){
		// get the index definition
		$queryIndex = "SELECT sql FROM sqlite_master WHERE tbl_name='".$table."' AND type='index' AND sql IS NOT NULL";
		$listIndex = SQLiteArrayFunction($GLOBALS['workDb']->connId, $queryIndex);
		$query[] = "BEGIN TRANSACTION;\n";
		$query[] = "CREATE TEMPORARY TABLE SQLIteManager_backup AS SELECT * FROM ".brackets($table).";\n";
		if(!empty($nullToNotNull)) {
			foreach($nullToNotNull as $newNotNull) {
				$query[] = "UPDATE SQLIteManager_backup SET ".$newNotNull."='' WHERE ".$newNotNull." IS NULL;";
			}
		}
		$query[] = "DROP TABLE ".brackets($table).";\n";	
		$query[] = $newDefinition."\n";		
		$query[] = "INSERT INTO ".brackets($table)." (".implode(", ", $newColumn).") SELECT ".implode(", ", $oldColumn)." FROM SQLIteManager_backup;\n";
		$query[] = "DROP TABLE SQLIteManager_backup;\n";
		$query[] = "COMMIT TRANSACTION;\n";
		$noerror = true;
		foreach($query as $req) {
			if($this->getResId($req)) $noerror = false;
		}
		if($noerror && is_array($listIndex)){
			foreach($listIndex as $tabSQL){
				if($this->getResId($req)) $noerror = false;
				else $noerror = true;
			}
		}
		return $noerror;
	}
	
	/**
	* Return properties of all user defined function
	*
	* @access public
	*/
	function getUDF(){
		$query = 'SELECT id, base_id, funct_type, funct_name, funct_code, funct_final_code, funct_num_args FROM user_function WHERE (base_id='.$GLOBALS['dbsel'].' OR base_id IS NULL)';
			if( $res = sqlitem_query($GLOBALS['db'], $query) ){
				while($ligne = SQLiteFetchFunction($res, SQLITE_ASSOC)) $this->functInfo[$ligne['id']] = $ligne;
			}
		return $this->functInfo;			
	}
	
	/**
	* Insert user defines function in the executable context to use this in SQL
	*
	* @access public
	*/
	function includeUDF(){
		$tabFunct = $this->getUDF();
		if(!isset($GLOBALS["UDF_declared"])) $GLOBALS["UDF_declared"] = array(); 
		if(is_array($tabFunct)){
			foreach($tabFunct as $infoFunct){
				switch($infoFunct['funct_type']){
					case 1:	// function utilisateur standard
						$code = $infoFunct['funct_code'];
						$funcName = $this->_getPHPfunctionName($code);
						if(!in_array($funcName, $GLOBALS["UDF_declared"]) && !function_exists($funcName)) {
							$GLOBALS["UDF_declared"][] = $funcName;
							eval($code);
						}
						if(DEBUG) sqlitem_create_function($this->connId, $infoFunct['funct_name'], $funcName, $infoFunct['funct_num_args']);
						else @sqlitem_create_function($this->connId, $infoFunct['funct_name'], $funcName, $infoFunct['funct_num_args']);
						break;
					case 2:	// function utilisateur d'aggregation
						$codeStep = $infoFunct['funct_code'];
						$codeFinal = $infoFunct['funct_final_code'];
						$funcStepName = $this->_getPHPfunctionName($codeStep);
						if(!in_array($funcStepName, $GLOBALS["UDF_declared"]) && !function_exists($funcStepName)) {
							$GLOBALS["UDF_declared"][] = $funcStepName;					
							eval($codeStep);
						}
						$funcFinalName = $this->_getPHPfunctionName($codeFinal);
						if(!in_array($funcFinalName, $GLOBALS["UDF_declared"]) && !function_exists($funcFinalName)) {
							$GLOBALS["UDF_declared"][] = $funcFinalName;					
							eval($codeFinal);
						}
						if(DEBUG) sqlitem_create_aggregate ( $this->connId, $infoFunct['funct_name'], $this->_getPHPfunctionName($codeStep), $this->_getPHPfunctionName($codeFinal), $infoFunct['funct_num_args']);
						else @sqlitem_create_aggregate ( $this->connId, $infoFunct['funct_name'], $this->_getPHPfunctionName($codeStep), $this->_getPHPfunctionName($codeFinal), $infoFunct['funct_num_args']);
						break;
				}
			}
		}
	}
	
	/**
	* Retreive function name from PHP code
	*
	* @access private
	* @param string $code PHP code
	*/
	function _getPHPfunctionName($code){
		$codeSearch = str_replace('FUNCTION', 'function', $code);
		preg_match('/function[[:space:]](.*)\((.*)\)[[:space:]]*{/', $codeSearch, $value);
		return $value[1];		
	}
	
	/**
	* Escape string To SQLite
	*
	* @access public
	* @param string $string
	*/
	function formatString($string){
		return @sqlitem_escape_string(stripslashes($string));
	}
	
	/**
	* Get attach database list
	*
	* @access public
	*/
	function getAttachDb(){
		$query = '	SELECT attachment.id AS ATID, attach_id, location, name 
					FROM attachment 
						LEFT JOIN database ON database.id=attachment.attach_id 
					WHERE base_id='.$GLOBALS['dbsel'];
		$tabAttach = SQLiteArrayFunction($GLOBALS['db'], $query, SQLITE_ASSOC);
		$tabout = array();
		foreach($tabAttach as $infoAttach) {
		  //fix when one db attached more than one time
		  if(isset($tabout[$infoAttach['attach_id']]))
		  	while (count($tabout[$infoAttach['attach_id']])) $infoAttach['attach_id'].=' ';
		  
			$tabout[$infoAttach['attach_id']]['id'] 		= $infoAttach['ATID'];
			$tabout[$infoAttach['attach_id']]['location'] 	= $infoAttach['location'];
			$tabout[$infoAttach['attach_id']]['name'] 		= $infoAttach['name'];
		}
		return $tabout;
	}
	
	/**
	 *
	 */
	function copyTable($source, $destination, $copy = true){
		if(strpos($source, ".")){
			list($srcDb, $srcTable) 	= explode('.', $source);
		} else {
			$srcDb = "";
			$srcTable = $source;
		}
		if(strpos($destination, ".")){
			list($dstDb, $dstable) 		= explode('.', $destination);
		} else {
			$dstDb = "";
			$dstable = $destination;
		}
		
		$res = $GLOBALS['workDb']->getResId('PRAGMA table_info('.brackets($srcTable).');');
		$infoTable = $GLOBALS['workDb']->getArray();	
		foreach($infoTable as $iT) $listField[$iT["cid"]] = $iT["name"];
		
		// backup table schema
		$backupSchema = SQLiteArrayFunction($GLOBALS['workDb']->connId, "SELECT sql FROM sqlite_master WHERE tbl_name LIKE '".$srcTable."' ORDER BY ROWID", SQLITE_ASSOC);
		if($dstDb) {
			// create an instance on this
			$res = SQLiteExecFunction($GLOBALS["db"], "SELECT location FROM database WHERE name='".$dstDb."'");
			$dbLocation = SQLiteStringFunction($res);
			$tempDb = &new SQLiteDbConnect($dbLocation);
			$query[] = "ATTACH DATABASE \"".$dbLocation."\" AS \"".$dstDb."\";\n";
		}
		$query[] = "BEGIN TRANSACTION;\n";
		$query[] = "CREATE TABLE SQLIteManager_backup AS SELECT * FROM ".brackets($srcTable).";\n";
		
		if(isset($_REQUEST["dropTable"]) && ($_REQUEST["dropTable"]=="true")){
			$res = $GLOBALS["workDb"]->getResId("SELECT count(*) FROM sqlite_master WHERE name='".brackets($dstable)."'");
			if($exist = SQLiteStringFunction($res)) $query[] = "DROP TABLE ".brackets($dstable).";\n";
		}
				
		if(!$copy) $query[] = "DROP TABLE ".brackets($srcTable).";\n";	

		if(!$copy || ($copy && (($_REQUEST["whatToDo"]=="structure") || ($_REQUEST["whatToDo"]=="data"))) ) {
			foreach($backupSchema as $schema) {
				$tempQuery = $schema["sql"];
				if(isset($tempQuery) && eregi("CREATE[[:space:]]TABLE", $tempQuery)) {
					$tempQuery = ereg_replace("CREATE[[:space:]]TABLE[[:space:]]".$srcTable, "CREATE TABLE ".brackets($dstable), $tempQuery);
					if($dstDb) {
						if(isset($_REQUEST["dropTable"]) && ($_REQUEST["dropTable"]=="true")){
							// check if table exist and drop it
							$res = $tempDb->getResId("SELECT count(*) FROM sqlite_master WHERE name='".brackets($dstable)."'");
							$exist = SQLiteStringFunction($res);
							if($exist) $tempDb->getResId("DROP TABLE ".brackets($dstable));
						}
						$tempDb -> getResId($tempQuery);
						unset($tempQuery);
					}
				}
				
				if(isset($tempQuery) && eregi("[[:space:]]INTO[[:space:]]", $tempQuery)) {
					$tempQuery = eregi_replace("^[[:space:]]INTO[[:space:]]".$srcTable, " INTO ".$destination, $tempQuery);			
				}
				
				if(isset($tempQuery) && eregi("CREATE[[:space:]]TRIGGER[[:space:]]", $tempQuery)) unset($tempQuery);
	
				if(isset($tempQuery) && $tempQuery) $query[] = $tempQuery;
			}
		}
		if(!$copy || ($copy && (($_REQUEST["whatToDo"]=="data") || ($_REQUEST["whatToDo"]=="dataonly"))) )
			$query[] = "INSERT INTO ".brackets($destination)." (".implode(", ", $listField).") SELECT ".implode(", ", $listField)." FROM SQLIteManager_backup;\n";
		$query[] = "DROP TABLE SQLIteManager_backup;\n";
		$query[] = "COMMIT TRANSACTION;\n";
		$noerror = false;
		foreach($query as $req){
			if(!$noerror) {
				if(!SQLiteExecFunction($this->connId, $req)) $noerror = true;
			}
		}
		return $noerror;		
	}
}
?>
