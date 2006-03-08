<?

/*
 * Swim
 *
 * Page viewing method
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class CSSHandler
{
	var $defines = array();
	var $base;
	
	function CSSHandler($resource)
	{
		$this->base=$resource;
	}
	
	function processInclude($content)
	{
		$resource=Resource::decodeResource($content);
		if (($resource!==null)&&($resource->exists()))
		{
			$this->parse($resource);
		}
		else
		{
			print("/* WARNING could not include ".$content." */\n");
		}
	}
	
	function processDefine($content)
	{
		list($text,$def)=explode(' ',$content,2);
		$this->defines[$text]=$def;
	}
	
	function evaluateCalc($content)
	{
		return eval('return '.$content.';');
	}
	
	function evaluateUrl($content)
	{
		$content=substr($content,1,-1);
		$request = new Request();
		$request->method='view';
		$request->resource=$content;
		return "url('".$request->encode()."')";
	}
	
	function applyDefines($line)
	{
		$changed=true;
		while ($changed)
		{
			$changed=false;
			foreach ($this->defines as $text => $value)
			{
				$pos=strpos($line,$text);
				if ($pos!==false)
				{
					$line=substr($line,0,$pos).$value.substr($line,$pos+strlen($text));
					$changed=true;
				}
			}
		}
		return $line;
	}
	
	function outputLine($line)
	{
		if (strlen($line)==0)
		{
			print "\n";
			return;
		}
		
		if ((strlen($line)>=2)&&($line[0]=='/')&&($line[1]=='/'))
		{
			print '/*'.substr($line,2).' */'."\n";
			return;
		}

		if ($line[0]=='#')
		{
			$type=substr($line,1,strpos($line,' ')-1);
			$content=substr($line,2+strlen($type));
			if ($type=='define')
			{
				$this->processDefine($content);
				return;
			}
			else if ($type=='include')
			{
				$this->processInclude($content);
				return;
			}
		}

		$line=$this->applyDefines($line);
		$pos=strpos($line,"-swim-");
		while ($pos!==false)
		{
			$start=substr($line,0,$pos);
			$spos=strpos($line,"(",$pos);
			$epos=strpos($line,")",$spos);
			$type=substr($line,$pos+6,$spos-($pos+6));
			$content=substr($line,$spos+1,$epos-($spos+1));
			$end=substr($line,$epos+1);
			
			if ($type=='url')
			{
				$result=$this->evaluateUrl($content);
			}
			else if ($type=='calc')
			{
				$result=$this->evaluateCalc($content);
			}
			if (isset($result))
			{
				$line=$start.$result.$end;
			}
			else
			{
				$pos+=1;
			}
			$pos=strpos($line,'-swim-',$pos);
		}
		print($line."\n");
	}
	
	function parse($resource)
	{
		ob_start();
		$resource->outputFile();
		$css=ob_get_contents();
		ob_end_clean();
		$lines=explode("\n",$css);
		foreach ($lines as $line)
		{
			$this->outputLine(rtrim($line));
		}
	}
	
	function output()
	{
		$this->parse($this->base);
	}
}

class CSSHandlerFactory
{
	function CSSHandlerFactory()
	{
	}
	
	function getHandler($resource)
	{
		return new CSSHandler($resource);
	}
	
	function output($resource)
	{
		$handler = $this->getHandler($resource);
		$handler->output();
	}
}

?>