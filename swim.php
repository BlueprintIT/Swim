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
unset($bootstrap);

SwimEngine::processCurrentRequest();

?>