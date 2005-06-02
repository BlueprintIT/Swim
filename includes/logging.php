<?

/*
 * Swim
 *
 * Logging code
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

define("SWIM_LOG_NONE",0);
define("SWIM_LOG_FATAL",1);
define("SWIM_LOG_ERROR",2);
define("SWIM_LOG_WARN",3);
define("SWIM_LOG_INFO",4);
define("SWIM_LOG_DEBUG",5);

class Logger
{
	var $name;
	var $level;
	
	function Logger($name,&$level)
	{
		$this->name=$name;
		$this->level=&$level;
	}
	
	function setLevel($level)
	{
		$this->level=level;
	}
	
	function output($text)
	{
		print($text."<br />\n");
	}
	
	function fatal($text)
	{
		if ($this->isFatalEnabled())
		{
			$this->output("<b>[FATAL]</b> ".$this->name.": ".$text);
		}
	}
	
	function error($text)
	{
		if ($this->isErrorEnabled())
		{
			$this->output("<b>[ERROR]</b> ".$this->name.": ".$text);
		}
	}
	
	function warn($text)
	{
		if ($this->isWarnEnabled())
		{
			$this->output("<b>[WARN]</b> ".$this->name.": ".$text);
		}
	}
	
	function info($text)
	{
		if ($this->isInfoEnabled())
		{
			$this->output("<b>[INFO]</b> ".$this->name.": ".$text);
		}
	}
	
	function debug($text)
	{
		if ($this->isDebugEnabled())
		{
			$this->output("<b>[DEBUG]</b> ".$this->name.": ".$text);
		}
	}
	
	function isFatalEnabled()
	{
		return $this->level>=SWIM_LOG_FATAL;
	}
	
	function isErrorEnabled()
	{
		return $this->level>=SWIM_LOG_ERROR;
	}
	
	function isWarnEnabled()
	{
		return $this->level>=SWIM_LOG_WARN;
	}
	
	function isInfoEnabled()
	{
		return $this->level>=SWIM_LOG_INFO;
	}
	
	function isDebugEnabled()
	{
		return $this->level>=SWIM_LOG_DEBUG;
	}
}

class LoggerManager
{
	var $level = SWIM_LOG_WARN;
	var $loggers = array();
	
	function LoggerManager()
	{
	}
	
	function &getLogger($name)
	{
		global $_LOGMANAGER;
		
		if (isset($_LOGMANAGER->loggers[$name]))
		{
			$logger=&$_LOGMANAGER->loggers[$name];
		}
		else
		{
			$logger = new Logger($name,$_LOGMANAGER->level);
			$_LOGMANAGER->loggers[$name]=&$logger;
		}
		return $logger;
	}
	
	function shutdown()
	{
	}
}

$_LOGMANAGER = new LoggerManager();
LoggerManager::getLogger("Internal");

function caught_error($type,$text,$file,$line)
{
	$log = &LoggerManager::getLogger("Internal");
	
	if (($type==E_ERROR)||($type==E_PARSE)||($type==E_CORE_ERROR)||($type==E_COMPILE_ERROR)||($type==E_USER_ERROR))
	{
		$log->error($text);
	}
	else if (($type==E_WARNING)||($type==E_CORE_WARNING)||($type==E_COMPILE_WARNING)||($type==E_USER_WARNING))
	{
		$log->warn($text);
	}
	else if (($type==E_NOTICE)||($type==E_USER_NOTICE))
	{
		$log->info($text);
	}
}

set_error_handler('caught_error');

?>