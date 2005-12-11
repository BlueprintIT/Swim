<?

/*
 * Swim
 *
 * Root code for page creation
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

$source = __FILE__;
while (is_link($source))
{
	$source=readlink($source);
}
$bootstrap=dirname($source).'/bootstrap';
unset($source);

require_once $bootstrap.'/bootstrap.php';

$log=LoggerManager::getLogger('swim');

$log->debug('Request start');
$_STATE=STATE_PROCESSING;

$request=Request::decodeCurrentRequest();

callMethod($request);

$log->debug('Request end');
//shutdown();

?>