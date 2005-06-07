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

define('SWIM_LOG_NONE',0);
define('SWIM_LOG_FATAL',1);
define('SWIM_LOG_ERROR',2);
define('SWIM_LOG_WARN',3);
define('SWIM_LOG_INFO',4);
define('SWIM_LOG_DEBUG',5);
define('SWIM_LOG_ALL',2000);

class LogOutput
{
	var $pattern;
	var $level = SWIM_LOG_ALL;
	
	function LogOutput()
	{
		$pattern='';
	}
	
	function setLevel($level)
	{
		$this->level=$level;
	}
	
	function internalOutput($text)
	{
	}
	
	function convertPattern($text,$vars)
	{
		$count=preg_match_all('/\$\[([^=#$[\]]+?)\]/',$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for($p=$count-1; $p>=0; $p--)
		{
			$pref=$matches[1][$p][0];
			$offset=$matches[0][$p][1];
			$length=strlen($matches[0][$p][0]);
			if (isset($vars[$pref]))
			{
				$replacement=$vars[$pref];
			}
			else
			{
				$replacement='';
			}
			$text=substr_replace($text,$replacement,$offset,$length);
		}
		return $text;
	}
	
	function output(&$logger,$detail)
	{
		if ($detail['level']<=$this->level)
		{
			$detail['txtlevel']='UNKNOWN';
			switch($detail['level'])
			{
				case SWIM_LOG_FATAL:
					$detail['txtlevel']='FATAL';
					break;
				case SWIM_LOG_ERROR:
					$detail['txtlevel']='ERROR';
					break;
				case SWIM_LOG_WARN:
					$detail['txtlevel']='WARN';
					break;
				case SWIM_LOG_INFO:
					$detail['txtlevel']='INFO';
					break;
				case SWIM_LOG_DEBUG:
					$detail['txtlevel']='DEBUG';
					break;
			}
			$detail['logger']=$logger->name;
			
			$this->internalOutput($this->convertPattern($this->pattern,$detail));
		}
	}
}

class FileLogOutput extends LogOutput
{
	function FileLogOutput()
	{
		$this->LogOutput();
	}
	
	function internalOutput($text)
	{
	}
}

class PageLogOutput extends LogOutput
{
	function PageLogOutput()
	{
		$this->LogOutput();
		$this->pattern='<b>[$[txtlevel]]</b> $[logger]: $[text] ($[file]:$[line])<br />';
	}
	
	function internalOutput($text)
	{
		print($text."\n");
	}
}

class Logger
{
	var $name;
	var $level;
	var $parent;
	var $output;
	
	function Logger(&$parent,$name)
	{
		$this->name=$name;
		$this->parent=&$parent;
	}
	
	function setParent(&$parent)
	{
		$this->parent=&$parent;
	}
	
	function setLogOutput(&$output)
	{
		$this->output=&$output;
	}
	
	function clearLogOutput()
	{
		unset($this->output);
	}
	
	function getLevel()
	{
		if (isset($this->level))
		{
			return $this->level;
		}
		return $this->parent->getLevel();
	}
	
	function clearLevel()
	{
		unset($this->level);
	}
	
	function setLevel($level)
	{
		$this->level=$level;
	}
	
	function doOutput(&$logger,$detail)
	{
		if (isset($this->output))
		{
			$this->output->doOutput($logger,$detail);
		}
		else
		{
			$this->parent->doOutput($logger,$detail);
		}
	}
	
	function output($level,$text,$trace)
	{
		$trace['level']=$level;
		$trace['text']=$text;
		$this->doOutput($this,$trace);
	}
	
	function getStackTrace()
	{
		$trace=debug_backtrace();
		if (count($trace)<4)
			return;
		$calledfrom=$trace[2];
		$calledfrom['function']=$trace[3]['function'];
		return $calledfrom;
	}
	
	function log($level,$text,$trace = null)
	{
		if ($this->getLevel()>=$level)
		{
			if (!isset($trace))
			{
				$trace=$this->getStackTrace();
			}
			$this->output($level,$text,$trace);
		}
	}
	
	function fatal($text)
	{
		$this->log(SWIM_LOG_FATAL,$text);
	}
	
	function error($text)
	{
		$this->log(SWIM_LOG_ERROR,$text);
	}
	
	function warn($text)
	{
		$this->log(SWIM_LOG_WARN,$text);
	}
	
	function info($text)
	{
		$this->log(SWIM_LOG_INFO,$text);
	}
	
	function debug($text)
	{
		$this->log(SWIM_LOG_DEBUG,$text);
	}
	
	function isFatalEnabled()
	{
		return $this->getLevel()>=SWIM_LOG_FATAL;
	}
	
	function isErrorEnabled()
	{
		return $this->getLevel()>=SWIM_LOG_ERROR;
	}
	
	function isWarnEnabled()
	{
		return $this->getLevel()>=SWIM_LOG_WARN;
	}
	
	function isInfoEnabled()
	{
		return $this->getLevel()>=SWIM_LOG_INFO;
	}
	
	function isDebugEnabled()
	{
		return $this->getLevel()>=SWIM_LOG_DEBUG;
	}
}

class LoggerManager
{
	var $level = SWIM_LOG_WARN;
	var $loggers = array();
	var $output;
	
	function LoggerManager()
	{
		$this->output = new PageLogOutput();
	}
	
	function doOutput(&$logger,$detail)
	{
		$this->output->output($logger,$detail);
	}
	
	function getLevel()
	{
		return $this->level;
	}
	
	function createLogger($name)
	{
		if (!isset($this->loggers[$name]))
		{
			$logger = new Logger($this,$name);

			$bestcount=0;
			$bestparent=&$this;
			foreach (array_keys($this->loggers) as $key)
			{
				if (strlen($key)<strlen($name))
				{
					if ((strlen($key)>$bestcount)&&(substr($name,0,strlen($key))==$key))
					{
						$bestcount=strlen($key);
						$bestparent=&$this->loggers[$key];
					}
				}
				else if (strlen($key)>strlen($name))
				{
					if (substr($key,0,strlen($name))==$name)
					{
						$tlogger=&$this->loggers[$key];
						$tlogger->setParent($logger);
					}
				}
			}
			$logger->setParent($bestparent);
			$this->loggers[$name]=&$logger;
		}
	}
	
	function setLogLevel($prefix,$level)
	{
		global $_LOGMANAGER;
		
		if (strlen($prefix)>0)
		{
			$logger=&LoggerManager::getLogger($prefix);
			$logger->setLevel($level);
		}
		else
		{
			$_LOGMANAGER->level=$level;
		}
	}
	
	function setLogOutput($prefix,&$output)
	{
		global $_LOGMANAGER;
		
		if (strlen($prefix)>0)
		{
			$logger=&LoggerManager::getLogger($prefix);
			$logger->setOutput($output);
		}
		else
		{
			$_LOGMANAGER->output=&$output;
		}
	}
	
	function &getLogger($name)
	{
		global $_LOGMANAGER;
		
		if (!isset($_LOGMANAGER->loggers[$name]))
		{
			$_LOGMANAGER->createLogger($name);
		}
		return $_LOGMANAGER->loggers[$name];
	}
	
	function shutdown()
	{
	}
}

$_LOGMANAGER = new LoggerManager();
LoggerManager::getLogger('php');

function caught_error($type,$text,$file,$line)
{
	$log = &LoggerManager::getLogger('php');
	
	$trace = array('file' => $file, 'line' => $line, 'function' => '');
	
	if (($type==E_ERROR)||($type==E_PARSE)||($type==E_CORE_ERROR)||($type==E_COMPILE_ERROR))
	{
		$log->log(SWIM_LOG_FATAL,$text,$trace);
	}
	else if (($type==E_WARNING)||($type==E_CORE_WARNING)||($type==E_COMPILE_WARNING)||($type==E_USER_ERROR))
	{
		$log->log(SWIM_LOG_ERROR,$text,$trace);
	}
	else if (($type==E_NOTICE)||($type==E_USER_WARNING))
	{
		$log->log(SWIM_LOG_WARN,$text,$trace);
	}
	else if ($type==E_USER_NOTICE)
	{
		$log->log(SWIM_LOG_INFO,$text,$trace);
	}
}

set_error_handler('caught_error');

?>