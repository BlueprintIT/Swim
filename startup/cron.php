<?

/*
 * Swim
 *
 * Automated tasks designed for running from a cron job
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

require('startup.php');

function checkConsistency()
{
  global $_STORAGE,$_PREFS;
  
  $log = LoggerManager::getLogger('swim.consistency');
  
  $sections = FieldSetManager::getSections();
  foreach ($sections as $section)
  {
    $log->info('Scanning '.$section->getName());
    $items = $section->getItems();
    foreach ($items as $item)
    {
      $variants = $item->getVariants();
      foreach ($variants as $variant)
      {
        $versions = $variant->getVersions();
        foreach ($versions as $version)
        {
        	if (!$version->getClass()->isValidView($version->getView()))
        		$log->warn('Itemversion '.$version->getId().' has invalid view '.$version->getView()->getId().' for class '.$version->getClass()->getId().'.');

        	$path = $version->getStoragePath();
          $results = $_STORAGE->query('SELECT * FROM File WHERE itemversion='.$version->getId().';');
          while ($results->valid())
          {
          	$details = $results->fetch();
          	if (!is_file($path.'/'.$details['file']))
          		$log->warn('Missing file in '.$item->getId().'/'.$variant->getVariant().'/'.$version->getVersion().'/'.$details['file']);
          }
        }
      }
    }
  }
  
  $results = $_STORAGE->query('SELECT ItemVariant.id FROM ItemVariant LEFT JOIN Item ON Item.id=ItemVariant.item WHERE ISNULL(Item.id);');
  while ($results->valid())
  {
  	$log->warn('Disconnected variant '.$results->fetchSingle());
  }
  
  $results = $_STORAGE->query('SELECT VariantVersion.id FROM VariantVersion LEFT JOIN ItemVariant ON VariantVersion.itemvariant = ItemVariant.id WHERE ISNULL(ItemVariant.id);');
  while ($results->valid())
  {
  	$log->warn('Disconnected version '.$results->fetchSingle());
  }
}

$mainhost = $_PREFS->getPref('url.host.1.hostname');
LoggerManager::setLogOutput('',new StdOutLogOutput($mainhost.' [$[txtlevel+5]] $[logger+30]: $[text] ($[file]:$[line])', $mainhost.'       $[logger+30]: $[function]$[arglist] ($[file]:$[line])'));
$log = Loggermanager::getLogger('swim.cron');

SwimEngine::ensureStarted();

setContentType('text/plain');

checkConsistency();
SearchEngine::buildIndex();

if (is_writable($_PREFS->getPref('storage.rootdir')))
{
	$hosts = $_PREFS->getPrefBranch('url.host');
	
	$rewrites = AddonManager::getRewrites();
	array_push($rewrites, array('pattern' => 'tinymce/jscripts/tiny_mce/plugins/advblockformat/(.*)', 'target' => 'swim/admin/static/tinymce/advblockformat/$1'));
	array_push($rewrites, array('pattern' => '^$', 'target' => 'swim/startup/swim.php [L]'));
	
	ob_start();
	
?>
RewriteEngine on 
RewriteBase <?= $_PREFS->getPref('url.base'); ?>/
Options -Indexes

<?
	if (is_file($_PREFS->getPref('storage.config').'/htaccess'))
		readfile($_PREFS->getPref('storage.config').'/htaccess');
?>

<?
	
	foreach ($rewrites as $rewrite)
	{
		foreach ($hosts as $host)
		{
			print("RewriteCond %{HTTP_HOST} ^".$host."$\n");
			print("RewriteRule ".$rewrite['pattern']." ".$rewrite['target']."\n");
		}
		print("\n");
	}
	
	foreach ($hosts as $host)
	{
?>
RewriteCond %{HTTP_HOST} ^<?= $host ?>$
RewriteCond %{REQUEST_FILENAME} !-f 
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* swim/startup/swim.php [L]
<?
	}
	
?>

<Files .*>
	Deny from all
</Files>
<?
	
	$htaccess = ob_get_contents();
	ob_end_clean();
	$output = fopen($_PREFS->getPref('storage.rootdir').'/.htaccess','w');
	if ($output !== FALSE)
	{
		fwrite($output, $htaccess);
		fclose($output);
	}
	else
		$log->error('Unable to write configuration. Attempt to open htaccess failed');
}
else
	$log->error('Unable to write configuration. htaccess in unwritable');

if (isset($_GET['backup']))
{
	if ((is_executable($_PREFS->getPref('tools.tar'))) && (is_executable($_PREFS->getPref('tools.mysqldump'))))
	{
		$backupfile = $_PREFS->getPref('storage.backup');
		if (is_writable($backupfile))
		{
			$backupfile .= '/'.$mainhost.'-';
			$backupfile .= date('Ymd-Hi');
			$backupfile .= '.tar.bz';
			system($_PREFS->getPref('tools.mysqldump').' --result-file='.$_PREFS->getPref('storage.sitedir').'/database.sql --add-drop-table --ignore-table='.$_PREFS->getPref('storage.mysql.database').'.Keywords -u '.$_PREFS->getPref('storage.mysql.user').' -p'.$_PREFS->getPref('storage.mysql.pass').' -e '.$_PREFS->getPref('storage.mysql.database'));
			system($_PREFS->getPref('tools.tar').' -cjhf '.$backupfile.' -C '.$_PREFS->getPref('storage.sitedir').' database.sql files');
			unlink($_PREFS->getPref('storage.sitedir').'/database.sql');
		}
		else
			$log->error('Unable to backup since backup file is not writable.');
	}
	else
		$log->error('Unable to backup since tools are unavailable.');
}

?>
