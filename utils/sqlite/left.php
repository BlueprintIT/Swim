<?php
/**
* Web based SQLite management
* Show navigation into databases
* @package SQLiteManager
* @author Fr�d�ric HENNINOT
* @version $Id$ $Revision$
*/

include_once "include/defined.inc.php";
include_once INCLUDE_LIB."config.inc.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
 <meta http-equiv="content-type" content="text/html;charset=<?php echo $charset ?>">
 <style type="text/css">
 div.logo { background: white; padding-top: 5px; padding-bottom: 5px; }
 div.design { position:absolute; width: 98%; top:47px; text-align:right; color:Silver; font-size:7px; }
 </style>
 <link href="theme/<?php echo $localtheme ?>/left.css" rel="stylesheet" type="text/css">
</head>

<body>
	<div align="center" width="100%" class="logo"><?php echo displayPics("sqlitemanager.png","SQLiteManager")?></div>
	<div class="base" align="center" style="margin-top: 5px;">
	<a href="index.php" class="base" target="_parent"><?php echo  $traduct->get(1); ?></a>
	</div>
	<table class="main" width="100%" border="0" cellspacing="0" cellpadding="0">
<?php

$query = "SELECT * FROM database ORDER BY name";
$tabDb = SQLiteArrayFunction($db, $query, SQLITE_ASSOC);
foreach($tabDb as $ligne){
	// get Database version
	$versionNum = getDbVersion($ligne["location"]);
	$dbPics = 'database';
	if(in_array($ligne['id'], $attachDbList)) {
		$dbPics .= '_link';
	} else {
		$dbPics .= $versionNum;
	}
	$dbPics .= '.png';
	
	echo "\n\t".'<tr valign="middle" class="database">'.
			 "\n\t\t".'<td class="img_db" width="18">'.displayPics($dbPics, '', 0, 20).'</td>'.
			 "\n\t\t".'<td class="name_db"><a href="index.php?dbsel='.$ligne['id'].'" target="_parent" class="dbsel">'.$ligne['name'].'</a></td>'.
			 "\n\t".'</tr>';
	if($dbsel == $ligne['id']){
		include_once INCLUDE_LIB.'SQLiteDbConnect.class.php';
		$tempDir = dirname($ligne['location']);
		if($tempDir == '.') $baseLocation = DEFAULT_DB_PATH . $ligne['location'];
		else $baseLocation = $ligne['location'];
		$workDb = &new SQLiteDbConnect($baseLocation);
		if(is_resource($workDb->connId) || is_object($workDb->connId)){
			echo "\n\t".'<tr valign="middle"><td colspan="2" class="objects" align="right">'.
					 "\n\t\t".'<table class="items" width="'.(LEFT_FRAME_WIDTH-25).'" border="0" cellspacing="0" cellpadding="0">';
			foreach($dbItems as $item){
				$list = $workDb->getPropList($item);
				if(is_array($list) && count($list)) foreach($list as $Name){
					$actionLink = '<a href="main.php?dbsel='.$dbsel.'&'.strtolower($item).'='.urlencode($Name).'" target="main" class="item">';
					echo "\n\t\t".'<tr>';
					echo '<td class="image" nowrap="nowrap">'.$actionLink.displayPics(strtolower($item).'s.png').'</a>';
          			if(($item!='Function') && ($item!='Trigger')) echo '<a href="main.php?dbsel='.$dbsel.'&'.strtolower($item).'='.urlencode($Name).'&action=browseItem" target="main">'.displayPics('browse.png', '', 0, 10).'</a>';
					else echo displayPics('nobrowse.png');
					echo '</td>';
					echo '<td class="item" nowrap="nowrap">';
					echo $actionLink.$Name.'</a></td>'.
					     "\n\t\t".'</tr>';
				} elseif(DISPLAY_EMPTY_ITEM_LEFT) {
					$actionLink = '<a href="main.php?dbsel='.$dbsel.'&action=add_'.strtolower($item).'" target="main" class="item">';
					echo "\n\t\t".'<tr>'.
					     '<td class="image" nowrap="nowrap" width="35">'.$actionLink.displayPics(strtolower($item).'s.png').'</a>'.displayPics('nobrowse.png').'</td>'.
				       '<td class="'.strtolower($item).'" nowrap="nowrap">';
					if(!$workDb->isReadOnly() && displayCondition('properties')) echo $actionLink.'+ '.$itemTranslated[$item].'</a></td>';
					else echo '<span class="item"><i>+ '.$itemTranslated[$item].'</i></span>';
					echo "\n\t\t".'</tr>';;
				}
			}
			
			echo "\n\t\t".'</table>';
      echo "\n\t".'</td></tr>';
		}
	}
}

/* made in config.inc
if(isset($workDb) && $workDb->connId && ($workDb->baseName!=':memory:')) {
	$workDb->close();
	@sqlitem_close($db);
}
*/

?>

	</table>
	<br/>
	<?php if(isset($theme_author)):?>
	<div class="design">
	Theme designed by <?php echo $theme_author?>
	</div>
	<?php endif;?>
</body>
</html>

