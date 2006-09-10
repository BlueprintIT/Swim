<?

/*
 * Swim
 *
 * Automated tasks designed for running from a cron job
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

require('startup.php');

LoggerManager::setLogOutput('',new StdOutLogOutput());

SwimEngine::ensureStarted();

setContentType('text/plain');

SearchEngine::buildIndex();

$hosts = $_PREFS->getPrefBranch('url.host');

$rewrites = AddonManager::getRewrites();
array_push($rewrites, array('pattern' => '^tinymce/jscripts/tiny_mce/plugins/advblockformat/(.*)', 'target' => 'swim/admin/static/tinymce/advblockformat/$1'));
array_push($rewrites, array('pattern' => '^$', 'target' => 'swim/startup/swim.php [L]'));

ob_start();
?>
Options +FollowSymLinks 

RewriteEngine on 
RewriteBase <?= $_PREFS->getPref('url.base'); ?>/

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

<Files access>
	Deny from all
</Files>

<Files .*>
	Deny from all
</Files>
<?

$htaccess = ob_get_contents();
ob_end_clean();
$output = fopen($_PREFS->getPref('storage.sitedir').'/.htaccess','w');
if ($output !== FALSE)
{
	fwrite($output, $htaccess);
	fclose($output);
}

?>