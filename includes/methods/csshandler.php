<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class CSSHandler
{
	var $resource;
	
	function CSSHandler(&$resource)
	{
		$this->resource=&$resource;
	}
	
	function outputLine($line)
	{
		$pos=strpos($line,"viewurl('");
		while ($pos!==false)
		{
			$epos=strpos($line,"')",$pos);
			$start=substr($line,0,$pos)."url('";
			$end=substr($line,$epos);
			$request = new Request();
			$request->method='view';
			$request->resource=substr($line,$pos+9,($epos-$pos)-9);
			$line=$start.$request->encode().$end;
			$pos=strpos($line,'viewurl(');
		}
		print($line);
	}
	
	function output()
	{
		ob_start();
		$this->resource->outputFile();
		$css=ob_get_contents();
		ob_end_clean();
		$lines=explode("\n",$css);
		foreach ($lines as $line)
		{
			$this->outputLine($line);
		}
	}
}

class CSSHandlerFactory
{
	function CSSHandlerFactory()
	{
	}
	
	function &getHandler(&$resource)
	{
		return new CSSHandler($resource);
	}
	
	function output(&$resource)
	{
		$handler = &$this->getHandler($resource);
		$handler->output();
	}
}

?>