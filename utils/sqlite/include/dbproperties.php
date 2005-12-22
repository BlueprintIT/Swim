<?php
/**
* Web based SQLite management
* Show and manage database properties
* @package SQLiteManager
* @author Fr�d�ric HENNINOT
* @version $Id$ $Revision$
*/

foreach($dbItems as $item){
?>
<center>
<table width="80%"><tr><td align="center">
<fieldset><legend><?php echo $itemTranslated[$item] ?></legend>
<?php
	$listItem = $workDb->getPropList($item);
	if($listItem){
		echo '<table width="90%" cellpadding="2" cellspacing="0" class="Browse">';
		if( $item == 'Table' ) $colspan = 5;
		elseif($item == 'View') $colspan = 3;
		elseif(($item == 'Function') || ($item == 'Trigger')) $colspan=2;
		echo '	<thead><tr>
					<td align="center" class="tabproptitle">'.$itemTranslated[$item].'</td>
					<td align="center" colspan="'.$colspan.'" class="tabproptitle">'.$traduct->get(33).'</td>'."\n";
		if( ($item == 'Table') || ($item=='View') ) echo '		<td align="center" class="tabproptitle" nowrap="nowrap">'.$traduct->get(118).'</td>'."\n";
		echo '	</tr></thead>';
		$totItem = $totEnr = 0;
		foreach($workDb->getPropList($item) as $itemName){
			if($totItem % 2) $localBgColor = $GLOBALS['browseColor1'];
			else $localBgColor = $GLOBALS['browseColor2'];
			
			$totItem++;
			$linkBase = 'main.php?dbsel='.$GLOBALS['dbsel'].'&'.strtolower($item).'='.$itemName;
			if( ($item == 'Table') || ($item=='View') ){
				if($res = $workDb->getResId('SELECT count(*) FROM '.brackets($itemName))) $nbEnr = SQLiteStringFunction($res);
				else $nbEnr = '&nbsp;';
				$totEnr+=$nbEnr;
			}
			echo "\t<tr bgcolor='".$localBgColor."' onMouseOver=\"setRowColor(this, $totItem, 'over', '".$localBgColor."', '".$GLOBALS['browseColorOver']."', '".$GLOBALS["browseColorClick"]."')\" 
									onMouseOut=\"setRowColor(this, $totItem, 'out', '".$localBgColor."', '".$GLOBALS["browseColorOver"]."', '".$GLOBALS['browseColorClick']."')\">\n";
			echo "		<td class=\"tabprop\" align='left'><a href=\"".$linkBase."\" class=\"PropItemTitle\"><span style=\"font-size: 12px\">&nbsp;".$itemName."</span></a></td>\n";
			if(isset($nbEnr) && $nbEnr && ( ($item == 'Table') || ($item=='View') )) echo "		<td class=\"tabprop\" align='center'><a href=\"".$linkBase."&action=browseItem\" class=\"propItem\">".displayPics("browse2.png", $traduct->get(73))."</a></td>\n";
			elseif( ($item == 'Table') || ($item=='View') ) echo "		<td class=\"tabprop\" align='center'><span style='color: gray'>".displayPics("browse_off.png", $traduct->get(73))."</span></td>\n";
			if(($item == 'Table')) {
				if(!$workDb->isReadOnly() && displayCondition("data")) echo "		<td class=\"tabprop\" align='center'><a href=\"".$linkBase."&action=insertElement\" class=\"propItem\">".displayPics("insertrow.png", $traduct->get(119))."</a></td>\n";
				else echo "		<td class=\"tabprop\" align='center'><i>".displayPics("insertrow_off.png", $traduct->get(119))."</i></td>\n";
			}
			echo "		<td class=\"tabprop\" align='center'><a href=\"".$linkBase."\" class=\"propItem\">".displayPics("properties.png", $traduct->get(61))."</a></td>\n";
			if(!$workDb->isReadOnly() && displayCondition("del")) echo "		<td class=\"tabprop\" align='center'><a href=\"javascript:if(confirm('".$traduct->get(120)." ".(($item!="Trigger")? $traduct->get(122) : $traduct->get(121) )." ".$itemTranslated[$item]." ".$itemName."?')) parent.main.location='".$linkBase."&action=delete';\" class=\"propItem\">".displayPics("delete_table.png", $traduct->get(86))."</a></td>\n";			
			else echo "		<td class=\"tabprop\" align='center'><i>".displayPics("delete_table_off.png", $traduct->get(86))."</i></td>\n";			
			if(isset($nbEnr) && $nbEnr && ( $item=="Table" )) {
				if(!$workDb->isReadOnly() && displayCondition("empty")) echo "		<td class=\"tabprop\" align='center'><a href=\"javascript:if(confirm('".$traduct->get(123)." ".$itemName."?')) parent.main.location='".$linkBase."&action=empty';\" class=\"propItem\">".displayPics("edittrash.png", $traduct->get(77))."</a></td>\n";
				else echo "		<td class=\"tabprop\" align='center'><i>".displayPics("edittrash_off.png", $traduct->get(77))."</i></td>\n";
			} elseif( $item=="Table" ) echo "		<td class=\"tabprop\" align='center'><span style='color: gray'>".displayPics("edittrash_off.png", $traduct->get(77))."</span></td>\n";
			if( ($item == 'Table') || ($item=='View') ) echo '		<td class="tabprop" align="center">'.$nbEnr.'</td>
					</tr>';
		}
		echo '	<tr>
					<td align="center" class="tabproptitle" nowrap="nowrap">'.$totItem.' '.$itemTranslated[$item].$traduct->get(228).'</td>
					<td colspan="'.$colspan.'" class="tabproptitle">&nbsp;</td>'."\n";
		if( ($item == 'Table') || ($item=='View') ) echo '		<td align="center" class="tabproptitle">'.$totEnr.'</td>'."\n";
		echo ' </tr>';
		echo '</table>';
	} else {
		echo '&nbsp;';
	}
	if(!$workDb->isReadOnly() && displayCondition('properties')) formAddItem($item);
?>
</fieldset>
</td></tr></table>
</center>
<?php
}
?>
</body>
</html>
