<?

/*
 * Swim
 *
 * Defines a block that just displays html source.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class XsltBlock extends Block
{
  function XsltBlock($container,$id,$version)
  {
    $this->Block($container,$id,$version);
  }
  
  function readFile($name)
  {
    if (strpos($name,'://')===false)
    {
      if ($this->fileIsReadable($name))
      {
        $file = $this->openFileRead($name);
        $content = stream_get_contents($file);
        $this->closeFile($file);
        return $content;
      }
      return false;
    }
    else
    {
      $file = fopen($name, 'r');
      if ($file!==false)
      {
        $content = stream_get_contents($file);
        fclose($file);
        return $content;
      }
      return false;
    }
  }
  
  function displayContent($parser,$attrs,$text)
  {
    $name=$this->prefs->getPref('block.xsltblock.xml','block.xml');
    $content = $this->readFile($name);
    if ($content===false)
    {
      $this->log->warn('Unable to load XML');
      return true;
    }
    $xml = new DOMDocument('1.0', 'UTF8');
    $xml->loadXML($content);
    
    $name=$this->prefs->getPref('block.xsltblock.stylesheet','block.xslt');
    $content = $this->readFile($name);
    if ($content===false)
    {
      $this->log->warn('Unable to load XSLT');
      return true;
    }
    $xsl = new DOMDocument('1.0', 'UTF8');
    $xsl->loadXML($content);
    
    $proc = domxml_xslt_stylesheet_doc($xsl);
    $result = $proc->process($xml);
    
    return true;
  }
  
  function registerObservers($parser)
  {
    $parser->addObserver('img',$parser->data['template']);
    $parser->addObserver('a',$this);
  }
  
  function unregisterObservers($parser)
  {
    $parser->removeObserver('a',$this);
    $parser->removeObserver('img',$parser->data['template']);
  }
  
  function observeTag($parser,$tagname,$attrs,$text)
  {
    if ($tagname=='a')
    {
      $this->log->debug('Observing a link');
      $link=$attrs['href'];
      if (substr($link,0,12)=='attachments/')
      {
        $this->log->debug('Attachment link');
        $request = new Request();
        $request->method=$parser->data['request']->method;
        $request->resource=$this->getPath().'/file/'.$link;
        $attrs['href']=$request->encode();
      }
      else if (substr($link,0,1)=='/')
      {
        $this->log->debug('Internal link');
        $request = new Request();
        $request->method=$parser->data['request']->method;
        $request->resource=substr($link,1);
        $attrs['href']=$request->encode();
      }
      else
      {
        $this->log->debug('External link');
        $attrs['target']="_blank";
      }
      print(Template::buildElement($parser,$tagname,$attrs,$text));
      return true;
    }
    else
    {
      return Block::observeTag($parser,$tagname,$attrs,$text);
    }
  }
}

?>