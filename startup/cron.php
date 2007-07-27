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

function hasArgument($name)
{
  global $argv;

  if ((isset($_GET)) && (isset($_GET[$name])))
    return true;
  if ((isset($argv)) && (in_array('--'.$name, $argv)))
    return true;
  return false;
}

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

LoggerManager::setLogLevel('swim.cron',LOG_LEVEL_INFO);

SwimEngine::ensureStarted();

setContentType('text/plain');

$log->info("Cron job startup");

if (hasArgument('consistency'))
{
  $log->info("Consistency check");
  checkConsistency();
}

if (hasArgument('keywords'))
{
  $log->info("Keywords compile");
  SearchEngine::buildIndex();
}

$log->info("htaccess generate");
if (is_writable($_PREFS->getPref('storage.rootdir')))
{
	$hosts = $_PREFS->getPrefBranch('url.host');
	
	$rewrites = AddonManager::getRewrites();
  array_unshift($rewrites, array('pattern' => '^sitemap$', 'target' => 'sitemap.xml [L,R=permanent]'));
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
	$hostpattern = '^(';
  foreach ($hosts as $key => $host)
  {
    if (substr($key, -9) == '.hostname')
      $hostpattern .= $host.'|';
  }
  $hostpattern = substr($hostpattern, 0, -1).')$';
  
	foreach ($rewrites as $rewrite)
	{
		print('RewriteCond %{HTTP_HOST} '.$hostpattern."\n");
		print('RewriteRule '.$rewrite['pattern'].' '.$rewrite['target']."\n");
		print("\n");
	}
	
?>
RewriteCond %{HTTP_HOST} <?= $hostpattern ?>

RewriteCond %{REQUEST_FILENAME} !-f 
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule .* swim/startup/swim.php [L]

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

if (hasArgument('mailing'))
{
  $log->info('Mailing');
  $sections = FieldSetManager::getSections();
  foreach ($sections as $section)
  {
    if ($section->getType() == 'mailing')
    {
      $mailings = $section->getMailings();
      $classes = array();
      foreach ($mailings as $mailing)
      {
        if (($mailing->hasFrequency()) && ($mailing->getNextSend()<=time()))
        {
          $itemversion = $mailing->createMail();
          if ($mailing->hasModerator())
          {
            require_once('Mail.php');
            require_once('Mail/mime.php');

            $smtp = Mail::factory('smtp', array('host' => $_PREFS->getPref('mail.smtphost')));
            $mail = new Mail_mime("\n");
            
            $url = new Request();
            $url->setMethod('admin');
            $url->setPath('mailing/index.tpl');
            $url->setQueryVar('section', $section->getId());
            $url->setQueryVar('item', $itemversion->getItem()->getId());
            $mail->setTxtBody('A new automated mail has been prepared. Please visit http://'.Session::getHost().$url->encode().' to review and send it.');
            
            $body = $mail->get();
            $headers = array('Subject' => 'New automated mailing ready to send');
            $headers['From'] = 'Swim CMS running on '.Session::getHost().' <swim@'.Session::getHost().'>';
            $headers = $mail->headers($headers);

            $smtp->send($mailing->getModerator(), $headers, $body);
            $log->info('New mailing prepared and '.$mailing->getModerator().' notified');
          }
          else
          {
            $mailing->prepareMail($itemversion);
            $log->info('New automated mail placed into queue');
          }
        }
        array_push($classes, $mailing->getClass());
      }

      $items = Item::findItems($section, $classes, null, 'sent', true, 'boolean', false, false);
      foreach ($items as $itemversion)
      {
        $mailing = $itemversion->getClass()->getMailing();
        $log->info('Sending mail for item '.$itemversion->getItem()->getId().' type '.$mailing->getId());
        $start = time();
        $mailing->sendMail($itemversion);
        $diff = time() - $start;
        $log->info('Send took '.$diff.' seconds');
      }
    }
  }
}

if (hasArgument('backup'))
{
  $log->info("Backup");
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

$log->info("Cron job complete");

?>
