<?

class Logger
{
	function Logger()
	{
	}
	
	function fatal($text)
	{
	}
	
	function error($text)
	{
	}
	
	function warn($text)
	{
	}
	
	function info($text)
	{
	}
	
	function debug($text)
	{
	}
	
	function isFatalEnabled()
	{
		return false;
	}
	
	function isErrorEnabled()
	{
		return false;
	}
	
	function isWarnEnabled()
	{
		return false;
	}
	
	function isInfoEnabled()
	{
		return false;
	}
	
	function isDebugEnabled()
	{
		return false;
	}
}

class LoggerManager
{
	function getLogger($name)
	{
		return new Logger();
	}
	
	function shutdown()
	{
	}
}

?>