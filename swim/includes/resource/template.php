<?

/*
 * Swim
 *
 * The template class and related functions
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class Template extends Resource
{
	var $log;
	
	function Template($container,$id,$version)
	{
		$this->Resource($container,$id,$version);
		
		$this->log=LoggerManager::getLogger('swim.template');
	}
	
	function getPath()
	{
		return $this->container->getPath().'/template/'.$this->id;
	}
	
	function getFileModifiedDate($file)
	{
		$f=$this->getDir().'/'.$this->prefs->getPref($file);
		if (is_readable($f))
		{
			$stats=stat($f);
			return $stats['mtime'];
		}
		return false;
	}
	
	function getModifiedDate()
	{
		if (isset($this->modified))
		{
			return $this->modified;
		}
		
		$modified=parent::getModifiedDate();
		
		$stat=$this->getFileModifiedDate('template.file.html');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$stat=$this->getFileModifiedDate('template.file.xml');
		if ($stat!==false)
		{
			$modified=max($modified,$stat);
		}
		$this->modified=$modified;
		return $modified;
	}
	
	function generateRelativeURL($data,$url,$method)
	{
		$request = new Request();
		$request->method=$method;
		if (strlen($url)==0)
		{
			$url=$data['request']->resource;
			$request->query=$data['request']->query;
		}
		else if (substr($url,0,6)=='block/')
		{
			$url=$data['block']->getPath().substr($url,5);
			$request->query['version']=$data['block']->version;
		}
		else if (substr($url,0,5)=='page/')
		{
			$url=$data['page']->getPath().substr($url,4);
			//$request->query['version']=$data['page']->version;
		}
		else if (substr($url,0,9)=='template/')
		{
			$url=$this->getPath().substr($url,8);
			$request->query['version']=$this->version;
		}
		else if (isset($data['block']))
		{
			$url=$data['block']->getPath().'/file/'.$url;
			if (isset($data['block']->version))
				$request->query['version']=$data['block']->version;
		}
		else
		{
			$url=$this->getPath().'/file/'.$url;
			$request->query['version']=$this->version;
		}
		$request->resource=$url;
		return $request;
	}
	
	function generateRequest($data,$url,$method)
	{
		if (strlen($url)==0)
		{
			$request = new Request();
			$request->method=$method;
			$request->resource=$data['request']->resource;
		}
		else if ($url[0]=='/')
		{
			$request = new Request();
			$request->method=$method;
			$request->resource=substr($url,1);
		}
		else if ($url=='page')
		{
			$request = new Request();
			$request->method=$method;
			$request->resource=$data['page'];
		}
		else
		{
		  $request=$this->generateRelativeURL($data,$url,$method);
		}
		return $request;
	}
	
	function generateURL($data,$url,$method='view',$params=array())
	{
		if (strpos($url,"://")>0)
		{
			if (count($params)>0)
			{
				if (strpos($url,"?")===false)
					$url=$url.'?';
				foreach($params as $key => $value)
					$url=$url.$key.'='.htmlentities($value).'&';
				$url=substr($url,0,-1);
			}
			return $url;
		}
	  $request=$this->generateRequest($data,$url,$method);
	  foreach ($params as $key => $value)
	  	$request->query[$key]=$value;
	  return $request->encode();
	}
	
	function displayDate($parser,$tag,$attrs,$text)
	{
		if (isset($attrs['source']))
		{
			if ($attrs['source']=='block')
			{
				$time=$data['block']->getModifiedDate();
			}
			else if ($attrs['source']=='template')
			{
				$time=$this->getModifiedDate();
			}
			else if ($attrs['source']=='page')
			{
				$time=$data['page']->getModifiedDate();
			}
			else if ($attrs['source']=='resource')
			{
				$time=$data['page']->getTotalModifiedDate();
			}
		}
		else
		{
			$time=time();
		}
		if (isset($attrs['format']))
		{
			$format=$attrs['format'];
		}
		else if ($tag=='date')
		{
			$format='l jS F Y';
		}
		else if ($tag=='time')
		{
			$format='g:ia (T)';
		}
		print(date($format,$time));
		return true;
	}
	
	function buildElement($parser,$tag,$attrs,$text='',$closetag=true)
	{
		$result='<'.$tag;
		foreach ($attrs as $name => $value)
		{
			$result.=' '.$name.'="'.$value.'"';
		}
		if ((strlen($text)==0)&&($parser->data['request']->isXML()))
		{
			$result.=' />';
		}
		else
		{
			$result.='>'.$text;
			if ($closetag)
				$result.='</'.$tag.'>';
		}
		return $result;
	}
	
	function displayElement($parser,$tag,$attrs,$text='',$closetag=true)
	{
		$element=$this->buildElement($parser,$tag,$attrs,$text,$closetag);
		print($element);
	}
	
  function displayApplet($parser,$tag,$attrs,$text)
  {
    $this->log->debug('Displaying applet');
    $dims="";
    if (isset($attrs['width']))
      $dims.=' width="'.$attrs['width'].'"';
    if (isset($attrs['height']))
      $dims.=' height="'.$attrs['height'].'"';
    if (isset($attrs['style']))
      $dims.=' style="'.$attrs['style'].'"';
    $class=$attrs['class'];
    $classpath=$attrs['classpath'];
    $codebase=$this->generateURL($parser,$attrs['codebase']);
    print('<object classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"'.$dims."\n\t");
    print('codebase="http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,0,0">'."\n\t");
    $this->displayElement($parser,'param',array('name'=>'type','value'=>'application/x-java-applet;version=1.4'),'',false); print("\n\t");
    $this->displayElement($parser,'param',array('name'=>'code','value'=>$class.'.class'),'',false); print("\n\t");
    $this->displayElement($parser,'param',array('name'=>'codebase','value'=>$codebase),'',false); print("\n\t");
    $this->displayElement($parser,'param',array('name'=>'archive','value'=>$attrs['classpath']),'',false); print("\n\t");
    print($text);
    print('<comment><object type="application/x-java-applet;version=1.4"'.$dims.'">'."\n\t\t");
    $this->displayElement($parser,'param',array('name'=>'code','value'=>$class),'',false); print("\n\t\t");
    $this->displayElement($parser,'param',array('name'=>'codebase','value'=>$codebase),'',false); print("\n\t\t");
    $this->displayElement($parser,'param',array('name'=>'archive','value'=>$attrs['classpath']),'',false); print("\n\t");
    print($text);
    print('</object></comment>'."\n");
    print('</object>');
    return true;
  }
  
  function displayFlash($parser,$tag,$attrs,$text)
  {
    $this->log->debug('Displaying flash');
    $dims="";
    if (isset($attrs['width']))
      $dims.=' width="'.$attrs['width'].'"';
    if (isset($attrs['height']))
      $dims.=' height="'.$attrs['height'].'"';
    if (isset($attrs['style']))
      $dims.=' style="'.$attrs['style'].'"';
    if (isset($attrs['class']))
      $dims=' class="'.$attrs['class'].'"';
    $movie=$this->generateURL($parser,$attrs['movie']);
    print('<object type="application/x-shockwave-flash" data="'.$movie.'"'.$dims.'>'."\n\t");
    print('<param name="movie" value="'.$movie.'">'."\n\t");
    print('<param name="loop" value="false">'."\n\t");
    print('<param name="quality" value="high">'."\n\t");
    print('<param name="bgcolor" value="#ffffff">'."\n\t");
    print('<param name="wmode" value="transparent">'."\n\t");
    print($text);
    print('</object>'."\n\t");

    return true;
  }
  
	function displayEditLink($parser,$tag,$attrs,$text)
	{
		$request = new Request();
		$request->nested=$parser->data['request'];
		if (isset($attrs['method']))
		{
			$request->method=$attrs['method'];
			unset($attrs['method']);
		}
		else
		{
			$request->method='edit';
		}
		if (isset($attrs['block']))
		{
			$block=$parser->data['page']->getReferencedBlock($attrs['block']);
			if ($block===false)
			{
				return true;
			}
			$request->resource=$block;
			unset($attrs['block']);
			$request->query['version']=$block->version;
			if (isset($attrs['file']))
			{
				$request->query['file']=$attrs['file'];
			}
		}
		else
		{
			$page=$parser->data['page'];
			$request->resource=$page;
			$request->query['version']=$page->version;
		}
		$attrs['href']=$request->encode();
		$this->displayElement($parser,'a',$attrs,$text);
		return true;
	}
	
	function displayStylesheet($parser,$tag,$attrs,$text)
	{
		$src = $attrs['src'];
		unset($attrs['src']);
		$src=$this->generateURL($parser->data,$src,'view',$attrs);
		if (!isset($parser->data['styles'][$src]))
		{
			$link=$this->buildElement($parser,'link',array('type'=>'text/css','rel'=>'stylesheet','href'=>$src),'',false);
			$parser->data['head'].=$link."\n";
			$parser->data['styles'][$src]=true;
		}
		return true;
	}
	
	function displayScript($parser,$tag,$attrs,$text)
	{
		if (isset($attrs['src']))
		{
			$src=$this->generateURL($parser->data,$attrs['src']);
			if (!isset($parser->data['scripts'][$src]))
			{
				$script=$this->buildElement($parser,'script',array('type'=>'text/javascript','src'=>$src));
				$parser->data['head'].=$script."\n";
				$parser->data['scripts'][$src]=true;
			}
		}
		else
		{
			$this->displayElement($parser, $tag, $attrs, $text);
		}
		return true;
	}
	
	function displayImage($parser,$tag,$attrs,$text)
	{
    $this->log->debug('Adding image '.$attrs['src']);
    $method = 'view';
    $params = array();
    if (isset($attrs['maxheight']))
    {
    	$params['maxheight']=$attrs['maxheight'];
    	unset($attrs['maxheight']);
    }
    if (isset($attrs['maxwidth']))
    {
    	$params['maxwidth']=$attrs['maxwidth'];
    	unset($attrs['maxwidth']);
    }
    if (count($params)>0)
    	$method='resize';
    if (isset($attrs['padding']))
    {
    	$params['padding']=$attrs['padding'];
    	unset($attrs['padding']);
    }
		$attrs['src']=$this->generateURL($parser->data,$attrs['src'],$method,$params);
		$this->displayElement($parser,'img',$attrs,$text,false);
		return true;
	}
	
	function displayBlock($parser,$tag,$attrs,$text)
	{
		if (isset($attrs['src']))
		{
      $this->log->debug('Loading block '.$attrs['src']);
			$block=Resource::decodeResource(substr($attrs['src'],1));
      unset($attrs['src']);
		}
		else if (isset($attrs['id']))
		{
			$page=$parser->data['page'];
      $this->log->debug('Loading page block '.$attrs['id'].' for page '.$page->getPath());
			$block=$page->getReferencedBlock($attrs['id']);
		}
		if ($block!==null)
		{
			if (isset($parser->data['block']))
				array_push($parser->data['stack'], array('id' => $parser->data['blockid'], 'block'=> $parser->data['block']));
			
			$parser->data['blockid']=$attrs['id'];
			$parser->data['block']=$block;
			
			$result=$block->display($parser,$attrs,$text);
			
			$details = array_pop($parser->data['stack']);
			if ($details !=null)
			{
				$parser->data['blockid']=$details['id'];
				$parser->data['block']=$details['block'];
			}
			else
			{
				unset($parser->data['block']);
				unset($parser->data['blockid']);
			}
		}
		return true;
	}
	
	function displayFileBrowser($parser,$tag,$attrs,$text)
	{
		$src=$this->generateURL($parser->data,'/internal/file/scripts/filebrowser.js');
		if (!isset($parser->data['scripts'][$src]))
		{
			$script=$this->buildElement($parser,'script',array('type'=>'text/javascript','src'=>$src));
			$parser->data['head'].=$script."\n";
			$parser->data['scripts'][$src]=true;
		}

		$id = $attrs['name'];
		$browser = new Request();
		$browser->method='fileselect';
		if (isset($attrs['page']))
			$browser->resource = $attrs['page'];
		else if (isset($attrs['container']))
			$browser->resource = $attrs['container'];
		if (isset($attrs['version']))
			$browser->query['version']=$attrs['version'];
		$browser->query['action']='fileBrowserCallback(\''.$id.'\', selected)';
		echo '<input id="'.$id.'" name="'.$attrs['name'].'" type="hidden" value="'.$attrs['value'].'">';
		if ((isset($attrs['value'])) && (strlen($attrs['value'])>0))
		{
			$rlvalue = $attrs['value'];
			$pos = strrpos($rlvalue, '/');
			if ($pos!==false)
				$rlvalue = substr($rlvalue, $pos+1);
		}
		else
			$rlvalue = '[No file selected]';
		echo '<input id="fbfake-'.$id.'" disabled="true" type="text" value="'.$rlvalue.'">';
		echo '<button type="button" onclick="showFileBrowser(\''.$browser->encode().'\',\''.$id.'\')">Select...</button>';
		echo '<button type="button" onclick="clearFileBrowser(\''.$id.'\')">Clear</button>';
	}
	
	function displayFile($parser,$tag,$attrs,$text)
	{
		$request=$this->generateRequest($parser->data,$attrs['src'],"view");
		$resource=$request->resource;
		if ($resource->isFile())
		{
			ob_start();
			$resource->outputFile();
	    $text=ob_get_contents();
	    ob_end_clean();
	    $parser->parseText($text);
		}
		return true;
	}
	
	function displayAnchor($parser,$tag,$attrs,$text)
	{
		if (isset($attrs['method']))
		{
			$method=$attrs['method'];
			unset($attrs['method']);
		}
		else
		{
			$method=$parser->data['request']->method;
		}
    $href = '';
    if (isset($attrs['href']))
      $href = $attrs['href'];
		$request=$this->generateRequest($parser->data,$href,$method);
		if (isset($attrs['nest']))
		{
			$request->nested=$parser->data['request'];
			unset($attrs['nest']);
		}
		if (isset($attrs['template']))
		{
			$request->query['template']=$attrs['template'];
			unset($attrs['template']);
		}
		foreach ($attrs as $id => $value)
		{
			if (substr($id,0,6)=='query:')
			{
				$id=substr($id,6);
				$request->query[$id]=$value;
				unset($attrs[$id]);
			}
		}
		$attrs['href']=$request->encode();
		$this->displayElement($parser,'a',$attrs,$text);
		return true;
	}
	
	function displayIf($parser,$tag,$attrs,$text)
	{
		$result=false;
		if (isset($attrs['hasVar']))
		{
			$name=$attrs['hasVar'];
			if (isset($attrs['namespace']))
			{
				$name=$attrs['namespace'].'.'.$name;
			}
			else
			{
				if (strpos($name,'.')===false)
				{
					$name='page.variables.'.$name;
				}
			}
			$page=$parser->data['page'];
			if ($page->prefs->isPrefSet($name))
			{
				$value=$page->prefs->getPref($name);
				$result=strlen($value)>0;
			}
		}
		else if (isset($attrs['hasBlock']))
		{
			$block=$parser->data['page']->getBlock($attrs['hasBlock']);
			if ($block!==false)
			{
				$result=true;
			}
		}
		if ($result)
		{
			print($text);
		}
		return true;
	}
	
	function displayVar($parser,$tag,$attrs,$text)
	{
		$name=$attrs['name'];
		if (isset($attrs['namespace']))
		{
			$name=$attrs['namespace'].'.'.$name;
		}
		else
		{
			if (strpos($name,'.')===false)
			{
				$name='page.variables.'.$name;
			}
		}
		$page=$parser->data['page'];
		$this->log->debug('Displaying variable '.$name.' for '.$page->id);
		if ($page->prefs->isPrefSet($name))
		{
			print($page->prefs->getPref($name));
		}
		return true;
	}
	
	function observeTag($parser,$tag,$attrs,$text)
	{
		$this->log->debug('Observed '.$tag);
		if ($tag=='var')
		{
			return $this->displayVar($parser,$tag,$attrs,$text);
		}
		else if ($tag=='head')
		{
			$parser->data['head'].=$text;
		}
		else if ($tag=='html')
		{
			print("<html>\n");
			print("<head>\n");
			print($parser->data['head']);
			print("</head>\n");
			print($text);
			print("</html>\n");
		}
		else if ($tag=='block')
		{
			return $this->displayBlock($parser,$tag,$attrs,$text);
		}
		else if ($tag=='stylesheet')
		{
			return $this->displayStylesheet($parser,$tag,$attrs,$text);
		}
		else if ($tag=='script')
		{
			return $this->displayScript($parser,$tag,$attrs,$text);
		}
		else if ($tag=='applet')
		{
			return $this->displayApplet($parser,$tag,$attrs,$text);
		}
    else if ($tag=='flash')
    {
      return $this->displayFlash($parser,$tag,$attrs,$text);
    }
		else if ($tag=='image')
		{
			return $this->displayImage($parser,$tag,$attrs,$text);
		}
		else if ($tag=='img')
		{
			return $this->displayImage($parser,$tag,$attrs,$text);
		}
    else if ($tag=='anchor')
    {
      return $this->displayAnchor($parser,$tag,$attrs,$text);
    }
		else if ($tag=='editlink')
		{
			return $this->displayEditLink($parser,$tag,$attrs,$text);
		}
		else if ($tag=='file')
		{
			return $this->displayFile($parser,$tag,$attrs,$text);
		}
		else if ($tag=='filebrowser')
		{
			return $this->displayFileBrowser($parser,$tag,$attrs,$text);
		}
		else if ($tag=='if')
		{
			return $this->displayIf($parser,$tag,$attrs,$text);
		}
		else if (($tag=='date')||($tag=='time'))
		{
			return $this->displayDate($parser,$tag,$attrs,$text);
		}
		else
		{
			return false;
		}
	}
	
	function internalDisplay($request,$page)
	{
		$xmlpref='template.file.xml';
		$htmlpref='template.file.html';
    $pagecontent = $page->prefs->getPref('page.contenttype','text/html');
    if ((substr($pagecontent,-4)=='+xml')||(substr($pagecontent,-4)=='/xml')||($request->isXHTML()))
		{
			$file=$this->prefs->getPref($xmlpref);
			if (!is_readable($this->dir.'/'.$file))
			{
				$request->setXML(false);
				$file=$this->prefs->getPref($htmlpref);
			}
      else
      {
        $request->setXML(true);
      }
		}
		else
		{
			$file=$this->prefs->getPref($htmlpref);
		}
		
    if ($page->prefs->isPrefSet('page.contenttype'))
    {
      setContentType($pagecontent);
    }
    else
    {
  		if ($request->isXML())
  		{
  			setContentType('application/xhtml+xml');
  		}
  		else
  		{
  			setContentType('text/html');
  		}
    }
						
		// Parse the template and display
		$parser = new TemplateParser();
		$parser->addEmptyTag("img");
		$parser->data=array('page'=>$page,'template'=>$this,'request'=>$request,'head'=>'', 'stack'=>array());
		$parser->addObserver('head',$this);
		$parser->addObserver('html',$this);
		$parser->addObserver('block',$this);
		$parser->addObserver('var',$this);
		$parser->addObserver('stylesheet',$this);
		$parser->addObserver('script',$this);
    $parser->addObserver('applet',$this);
    $parser->addObserver('flash',$this);
		$parser->addObserver('image',$this);
		//$parser->addObserver('img',$this);
		$parser->addObserver('anchor',$this);
		$parser->addObserver('editlink',$this);
		$parser->addObserver('if',$this);
		$parser->addObserver('date',$this);
		$parser->addObserver('time',$this);
		$parser->addObserver('file',$this);
		$parser->addObserver('filebrowser',$this);
		
		$this->lockRead();
		ob_start();
		$parser->parseFile($this->dir.'/'.$file);
		ob_end_flush();
		$this->unlock();
	}
	
	function display($request,$page)
	{
		$this->internalDisplay($request,$page);
	}
}

function getAllTemplates()
{
	return getAllResources('template');
}

?>