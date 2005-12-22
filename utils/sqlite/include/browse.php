<?php
/**
* Web based SQLite management
* Show result query with paginate, sort, modify/delete links
* @package SQLiteManager
* @author Frédéric HENNINOT
* @version $Id$ $Revision$
*/

include_once INCLUDE_LIB.'ParsingQuery.class.php';
include_once INCLUDE_LIB.'sql.class.php';
if(!isset($withForm)) $withForm = true;
if(!isset($DisplayQuery) || empty($DisplayQuery)){
	if($action == 'sql') {
		$displayResult = false;
	}
	if(!empty($table) || !empty($view)) $DisplayQuery = 'SELECT * FROM '.brackets($table).brackets($view);
	else $DisplayQuery = '';
} else if(!isset($_FILES)) {
	$DisplayQuery = urldecode($GLOBALS['DisplayQuery']);
} elseif( !empty($_POST['DisplayQuery']) || !empty($_GET['DisplayQuery']) ) {
	//if($_POST['sqltype'] != 2) 
  $DisplayQuery = stripslashes($DisplayQuery);	
}

if(!isset($displayResult)) $displayResult = true;
if(!isset($sql_action)) $sql_action = '';
if( ($sql_action=='explain') && !eregi('EXPLAIN', $DisplayQuery) ) $DisplayQuery = 'EXPLAIN '.$DisplayQuery;
$SQLiteQuery =& new sql($workDb, $DisplayQuery);
if( $sql_action != 'modify'){
	$error = $SQLiteQuery->verify(false);
} else {
	$error = false;
}
if($SQLiteQuery->withReturn && !$error && $displayResult){
	include_once INCLUDE_LIB.'SQLiteToGrid.class.php';
	if(!empty($GLOBALS["table"])) $linkItem = 'table='.$GLOBALS["table"];
	else $linkItem = 'view='.$GLOBALS["view"];
	
	$accessResult = $SQLiteQuery->checkAccessResult($DisplayQuery);	
	
	$DbGrid =& new SQLiteToGrid($workDb->connId, $SQLiteQuery->query, 'Browse', true, BROWSE_NB_RECORD_PAGE, '70%');
	$DbGrid->enableSortStyle(false);
	$DbGrid->setGetVars('?dbsel='.$GLOBALS['dbsel'].'&table='.$table.'&action=browseItem&DisplayQuery='.urlencode($DisplayQuery));
	if($DbGrid->getNbRecord()<=BROWSE_NB_RECORD_PAGE) $DbGrid->disableNavBarre();
	if($accessResult && (!$workDb->isReadOnly() && displayCondition('data'))){
		$DbGrid->addCalcColumn($GLOBALS["traduct"]->get(33), "	<div class=\"BrowseImages\"><a href=\"?dbsel=".$GLOBALS["dbsel"]."&table=".$accessResult."&action=modifyElement&query=#%QUERY%#&pos=#%POS%#&currentPage=browseItem\" class=\"Browse\">".displayPics("edit.png", $GLOBALS["traduct"]->get(14))."</a>&nbsp;
											<a href=\"?dbsel=".$GLOBALS["dbsel"]."&table=".$accessResult."&action=deleteElement&query=#%QUERY%#&pos=#%POS%#&currentPage=browseItem\" class=\"Browse\">".displayPics("deleterow.png", $GLOBALS["traduct"]->get(15))."</a></div>", "center", 0);
	}

	$showTime = '<div class="time" align="center">'.$GLOBALS['traduct']->get(213).' '.$SQLiteQuery->queryTime.' '.$GLOBALS['traduct']->get(214).'</div>';
	if($allFullText) $caption = '<a href="?dbsel='.$GLOBALS["dbsel"].'&'.$linkItem.'&action=browseItem&fullText=0">'.displayPics("nofulltext.png", $GLOBALS['traduct']->get(225)).'</a>';
	else $caption = '<a href="?dbsel='.$GLOBALS["dbsel"].'&'.$linkItem.'&action=browseItem&fullText=1">'.displayPics("fulltext.png", $GLOBALS['traduct']->get(226)).'</a>';
	if($allHTML) $capHTML = '<a href="?dbsel='.$GLOBALS["dbsel"].'&'.$linkItem.'&action=browseItem&HTMLon=0">'.displayPics("HTML_on.png", "HTML").'</a>';
	else $capHTML = '<a href="?dbsel='.$GLOBALS["dbsel"].'&'.$linkItem.'&action=browseItem&HTMLon=1">'.displayPics("HTML_off.png", "Texte").'</a>';

    $DbGrid->addCaption("top", '<div><div style="float: left">'.$caption.str_repeat('&nbsp;', 3).$capHTML.'</div>'.$showTime.'</div>');
    
    $DbGrid->build();
	
	if(!isset($noDisplay) || !$noDisplay) displayQuery($DbGrid->getRealQuery());

	if($DbGrid->getNbRecord()) {

    	$DbGrid->show();
    	echo '<!-- browse.php -->'."\n";
		echo '<div class="BrowseOptions">';
		if(empty($view) && (!$workDb->isReadOnly() && displayCondition("properties"))){
			echo '<hr width="60%">';
			echo '	<table align="center" class="BrowseOption"><tr><td>
					<form name="addView" action="main.php?dbsel='.$GLOBALS['dbsel'].'" method="POST">
					&nbsp;&raquo;&nbsp;'.$GLOBALS['traduct']->get(97).'
					<input type="text" class="text" name="ViewName"> '.$GLOBALS['traduct']->get(98).'
					<input class="button" type="submit" value="'.$GLOBALS['traduct']->get(69).'">
					<input type="hidden" name="action" value="save">
					<input type="hidden" name="ViewProp" value="'.urlencode($DisplayQuery).'">
					</form></tr></td></table>';
		}
		if($accessResult && (displayCondition('export'))){
			echo '<hr width="60%">';
			echo '	<table align="center" class="BrowseOption"><tr><td>
					<a href="main.php?dbsel='.$GLOBALS['dbsel'].'&table='.$GLOBALS['table'].'&queryExport='.urlencode($DisplayQuery).'&action=export" class="Browse">&nbsp;&raquo;&nbsp;'.$GLOBALS['traduct']->get(76).'</a>
					</tr></td></table>';
		}
		echo '</div>';
		
	}
	if(!$DbGrid->getNbRecord()) $SQLiteQuery->getForm($DbGrid->getRealQuery());
} else {
	if(!$SQLiteQuery->multipleQuery && (!isset($noDisplay) || !$noDisplay)) displayQuery($DisplayQuery, true, $SQLiteQuery->changesLine);
	else $SQLiteQuery->DisplayMultipleResult();
	if(!empty($DisplayQuery) && $error) {
		$withForm = true;
		$errorMessage = "";
		if(is_array($SQLiteQuery->lineError)) $errorMessage = $GLOBALS["traduct"]->get(99)." : ".implode(", ", $SQLiteQuery->lineError)."<br>";
		$errorMessage .= $SQLiteQuery->errorMessage;
		displayError($errorMessage);
	}
	if($withForm && WITH_AUTH && isset($SQLiteManagerAuth) &&  !$SQLiteManagerAuth->getAccess("execSQL")) $withForm = false;
	if($withForm) $SQLiteQuery->getForm($DisplayQuery);
}
?>

</body>
</html>
