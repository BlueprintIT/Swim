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

// Load the base includes
require_once 'base.php';

LoggerManager::setLogLevel('',SWIM_LOG_WARN);

$log=&LoggerManager::getLogger('swim');

$request=&Request::decodeCurrentRequest();

callMethod($request);

?>