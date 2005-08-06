<?

/*
 * Swim
 *
 * Parsing engines
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

// A simplistic parser that pulls tags out of a text stream.
class Parser
{
  var $_tagname;
  var $_attrname;
  var $_attrvalue;
  var $_state;
  var $_quote;
  var $_log;
  
  // Initialises the parser
  function Parser()
  {
    $this->_tagname='';
    $this->_state=0;
    $this->_attrname='';
    $this->_attrvalue='';
    $this->_quote='';
    $this->_log=&LoggerManager::getLogger('swim.parser.'.get_class($this));
    $this->_log->debug('In state 0');
  }
  
  // Called when a new start tag is found
  function onStartTag($tag)
  {
  }
  
  // Called when a start tag has been completely parsed.
  function onStartTagComplete($tag)
  {
  }
  
  // Called when an attribute is found in a start tag
  function onAttribute($name,$value)
  {
  }
  
  // Called when an end tag is found
  // popStack should be called if this is a valid end tag
  function onEndTag($tag)
  {
  }
  
  // Called when some arbritary text is found
  function onText($text)
  {
  }
  
  // Parses a whole file in one call
  function parseFile($filename)
  {
    $this->_log->debug('Parsing file '.$filename);
    if (($source=fopen($filename,'r'))!==false)
    {
	    while (!feof($source))
	    {
	      $this->parseText(fgets($source));
	    }
	    fclose($source);
	    return true;
	  }
	  return false;
  }
  
  // Starts capturing output
  function startBuffer()
  {
  	ob_start();
  }
  
  // Ends output capture and parses text if requested otherwise just outputs
  function endBuffer($parse=true)
  {
  	$text=ob_get_contents();
  	ob_end_clean();
  	if ($parse)
  	{
	  	$this->parseBlock($text);
  	}
  	else
  	{
  		$this->onText($text);
  	}
  }
  
  // Parses a block of text by splitting at newlines. Mist speed up the regex matching.
  function parseBlock($text)
  {
  	$lines=explode("\n",$text);
  	foreach ($lines as $line)
  	{
  		$this->parseText($line);
  	}
  }
  
  // Parses some text
  function parseText($text)
  {
    $validregex='[A-Za-z0-9]+';
    switch($this->_state)
    {
      // State 0 is where we are scanning for a new start tag. Any text before a new
      // start tag is just outputted into the current tag bugger.
      case 0:
        $regex='/<(\/('.$validregex.')>|('.$validregex.'))/';
        $matches=array();
        if (preg_match($regex,$text,$matches,PREG_OFFSET_CAPTURE))
        {
          $remaining=substr($text,$matches[0][1]+strlen($matches[0][0]));
					
          if ($matches[0][1]>0)
	          $this->onText(substr($text,0,$matches[0][1]));

          if (isset($matches[3][0]))
          {
            $tagname=$matches[3][0];
            $this->_log->debug('Start tag for '.$tagname);
            if ($this->onStartTag($tagname))
            {
            	$this->_log->debug('Capturing tag '.$tagname);
              $this->_tagname=$tagname;
              $this->_state=1;
            }
            else
            {
            	$this->_log->debug('Ignoring tag '.$tagname);
              $this->onText('<'.$matches[1][0]);
            }
          }
          else
          {
            $this->_log->debug('End tag for '.$matches[2][0]);
            if (!$this->onEndTag($matches[2][0]))
            {
              $this->onText('<'.$matches[1][0]);
            }
          }
          $this->parseText($remaining);
        }
        else
        {
          $this->onText($text);
        }
        break;
      // State 1 is inside a start tag, scanning for attributes and the end of the start tag.
      case 1:
        $text=ltrim($text);
        if (strlen($text)>0)
        {
          if ($text[0]=='>')
          {
            $this->_log->debug('Found end of start tag, moving to state 0');
            $this->_state=0;
            $this->onStartTagComplete($this->_tagname);
            $this->parseText(substr($text,1));
          }
          else if (substr($text,0,2)=='/>')
          {
            $this->_log->debug('Found end of simple tag, moving to state 0');
            $this->_state=0;
            $this->onEndTag($this->_tagname);
            $this->parseText(substr($text,2));
          }
          else if (preg_match('/^('.$validregex.')=(\'|")/',$text,$matches))
          {
            $this->_log->debug('Found attribute '.$matches[1].' moving to state 2');
            $this->_attrname=$matches[1];
            $this->_attrvalue='';
            $this->_quote=$matches[2];
            $this->_state=2;
            $this->parseText(substr($text,strlen($matches[0])));
          }
          else
          {
            $this->_log->warn('Illegal content in start tag: \''.$text.'\'');
          }
        }
        break;
      // State 2 is inside an attribute definition.
      case 2:
        if (preg_match('/^(.*?)'.$this->_quote.'/',$text,$matches))
        {
          $this->_log->debug('Found end of attribute. Back to state 1.');
          $this->_attrvalue.=$matches[1];
          $this->_state=1;
          $this->onAttribute($this->_attrname,$this->_attrvalue);
          $this->parseText(substr($text,strlen($matches[0])));
        }
        else
        {
          $this->_attrvalue.=$text;
        }
        break;
    }
  }
}

// Adds a stack to the parser so proper parsing is simpler
class StackedParser extends Parser
{
  var $_tagstack;
  var $_current;
  var $_emptytags;
  
  function StackedParser()
  {
    $this->Parser();
    $this->_tagstack=array();
    $this->_emptytags=array();
    $this->_current='';
  }

	function addEmptyTag($tag)
	{
		$this->_emptytags[]=$tag;
	}
	
  // If the tag is one that is deemed always empty then we make it so.
  function onStartTagComplete($tag)
  {
  	if (in_array($tag,$this->_emptytags))
  	{
  		$this->onEndTag($tag);
  	}
  }
  
  // Adds the given attribute to the current tag on the stack.
  function onAttribute($name,$value)
  {
    $this->_current['attrs'][$name]=$value;
  }
  
  // Called when some text arrives not within any tag on the stack.
  function onUncontainedText($text)
  {
  }
  
  // Outputs text into the current tag's buffer
  function onText($text)
  {
    if (is_array($this->_current))
    {
      $this->_current['text'].=$text;
    }
    else
    {
      $this->onUncontainedText($text);
    }
  }

  // Pushes a new tag onto the stack
  function pushStack($tagname)
  {
    $this->_log->debug('Pushing '.$tagname. ' onto the stack');
    $this->_tagstack[]=array('tag' => $tagname, 'attrs' => array(), 'text' => '');
    $this->_current=&$this->_tagstack[count($this->_tagstack)-1];
  }
  
  // Pops the current tag from the stack and returns it.
  function popStack()
  {
    $result=array_pop($this->_tagstack);
    $this->_log->debug('Popped '.$result['tag'].' off the stack');
    if (count($this->_tagstack)>0)
    {
      $this->_current=&$this->_tagstack[count($this->_tagstack)-1];
    }
    else
    {
      $this->_current='';
    }
    return $result;
  }  
}

class TagObserver
{
	var $callback;
	
	function TagObserver($callback)
	{
		$this->callback=$callback;
	}

  function observeTag(&$parser,$tagname,$attrs,$content)
  {
  	return $this->callback($parser,$tagname,$attrs,$content);
  }
}

// Designed for parsing very bad html but picking out the few tags we are interested in.
class TemplateParser extends StackedParser
{
  var $observers;
  var $data;
  
  function TemplateParser()
  {
    $this->StackedParser();
    $this->observers=array();
  }

	// Adds an object observer to the parser
	function addObserver($tagname,&$observer)
	{
    $this->_log->debug('Observer added for '.$tagname);
    if (!isset($this->observers[$tagname]))
    {
    	$this->observers[$tagname]=array();
    }
    $this->observers[$tagname][]=&$observer;
	}
	
  // Adds an observer to a number of tag names.
  function addObservers($tagnames,&$observer)
  {
    foreach ($tagnames as $tagname)
    {
      $this->addObserver($tagname,$observer);
    }
  }
  
  function removeObserver($tag,&$observer)
  {
  	if (is_array($this->observers[$tag]))
  	{
	  	foreach (array_keys($this->observers[$tag]) as $key)
	  	{
	  		unset($this->observers[$tag][$key]);
	  	}
	  	if (count($this->observers[$tag])==0)
	  	{
	  		unset($this->observers[$tag]);
	  	}
	  }
  }
  
  // Adds a callback to the parser. Tags with this name will be extracted.
  function addCallback($tagname,$function)
  {
    $this->_log->debug('Callback added for '.$tagname);
  	$this->addObserver($tagname,new TagObserver($function));
  }
  
  // Adds a callback to a numebr of tag names.
  function addCallbacks($tagnames,$function)
  {
  	$this->addObservers($tagnames,new TagObserver($function));
  }
  
  // Called when a tag has been fully extracted to call the user function.
  function callObserver(&$observer,$tagname,$attrs,$content)
  {
    ob_start();
    $this->_log->debug('Calling observer for '.$tagname);
    $done=$observer->observeTag($this,$tagname,$attrs,$content);
    $text=ob_get_contents();
    ob_end_clean();
    $this->onText($text);
    return $done;
  }

  // Any uncontained text just goes to stdout.  
  function onUncontainedText($text)
  {
    print($text);
  }
  
  // Checks if we are interested in the tag.
  function onStartTag($tag)
  {
    if (isset($this->observers[$tag]))
    {
      $this->pushStack($tag);
      return true;
    }
    else
    {
      return false;
    }
  }
  
  // If this is a tag we are parsing then pop it.
  function onEndTag($tag)
  {
    if ((is_array($this->_current))&&($tag==$this->_current['tag']))
    {
      $result=$this->popStack();
      if ($result['tag']!=$tag)
      {
      	$this->_log->error('Was expecting end tag for '.$tag.' but got '.$result['tag']);
      }
      if (isset($this->observers[$tag]))
      {
		    $this->_log->debug('Buffering callbacks');
		    foreach (array_keys($this->observers[$tag]) as $i)
		    {
		    	$observer=&$this->observers[$tag][$i];
		    	if ($this->callObserver($observer,$tag,$result['attrs'],$result['text']))
		    	{
		    		break;
		    	}
		    }
      }
      else
      {
	      $this->onText($result['text']);
	    }
      return true;
    }
    else
    {
      return false;
    }
  }
}

?>
