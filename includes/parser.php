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
    $this->_tagname="";
    $this->_state=0;
    $this->_attrname="";
    $this->_attrvalue="";
    $this->_quote="";
    $this->_log=LoggerManager::getLogger("Parser");
    $this->_log->debug("In state 0");
  }
  
  // Called when a new start tag is found
  function onStartTag($tag)
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
    $this->_log->info("Parsing file ".$filename);
    if (($source=fopen($filename,"r"))!==false)
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
  
  // Parses some text
  function parseText($text)
  {
    $validregex="[A-Za-z0-9]+";
    switch($this->_state)
    {
      // State 0 is where we are scanning for a new start tag. Any text before a new
      // start tag is just outputted into the current tag bugger.
      case 0:
        $regex="/^(.*?)<(\/(".$validregex.")>|(".$validregex."))/";
        if (preg_match($regex,$text,$matches))
        {
          $remaining=substr($text,strlen($matches[0]));
          $this->onText($matches[1]);
          if (isset($matches[4]))
          {
            $tagname=$matches[4];
            $this->_log->debug("Start tag for ".$tagname);
            if ($this->onStartTag($tagname))
            {
              $this->_tagname=$tagname;
              $this->_state=1;
            }
            else
            {
              $this->onText("<".$matches[2]);
            }
          }
          else
          {
            $this->_log->debug("End tag for ".$matches[3]);
            if (!$this->onEndTag($matches[3]))
            {
              $this->onText("<".$matches[2]);
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
          if (substr($text,0,1)==">")
          {
            $this->_log->debug("Found end of start tag, moving to state 0");
            $this->_state=0;
            $this->parseText(substr($text,1));
          }
          else if (substr($text,0,2)=="/>")
          {
            $this->_log->debug("Found end of simple tag, popping and moving to state 0");
            $this->_state=0;
            $this->onEndTag($this->_tagname);
            $this->parseText(substr($text,2));
          }
          else if (preg_match("/^(".$validregex.")=(\"|')/",$text,$matches))
          {
            $this->_log->debug("Found attribute ".$matches[1]." moving to state 2");
            $this->_attrname=$matches[1];
            $this->_attrvalue="";
            $this->_quote=$matches[2];
            $this->_state=2;
            $this->parseText(substr($text,strlen($matches[0])));
          }
          else
          {
            $this->_log->warn("Illegal content in start tag: \"".$text."\"");
          }
        }
        break;
      // State 2 is inside an attribute definition.
      case 2:
        if (preg_match('/^(.*?)'.$this->_quote.'/',$text,$matches))
        {
          $this->_log->debug("Found end of attribute. Back to state 1.");
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
  
  function StackedParser()
  {
    $this->Parser();
    $this->_tagstack=array();
    $this->_current="";
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
    $this->_log->debug("Pushing ".$tagname. " onto the stack");
    $this->_tagstack[]=array('tag' => $tagname, 'attrs' => array(), 'text' => "");
    $this->_current=&$this->_tagstack[count($this->_tagstack)-1];
  }
  
  // Pops the current tag from the stack and returns it.
  function popStack()
  {
    $result=array_pop($this->_tagstack);
    $this->_log->debug("Popped ".$result['tag']." off the stack");
    if (count($this->_tagstack)>0)
    {
      $this->_current=&$this->_tagstack[count($this->_tagstack)-1];
    }
    else
    {
      $this->_current="";
    }
    return $result;
  }  
}

// Designed for parsing very bad html but picking out the few tags we are interested in.
class TemplateParser extends StackedParser
{
  var $_callbacks;
  
  function TemplateParser()
  {
    $this->StackedParser();
    $this->_callbacks=array();
  }

  // Adds a callback to the parser. Tags with this name will be extracted.
  function addCallback($tagname,$function)
  {
    $this->_log->debug("Callback added for ".$tagname);
    $this->_callbacks[$tagname]=$function;
  }
  
  // Adds a callback to a numebr of tag names.
  function addCallbacks($tagnames,$function)
  {
    foreach ($tagnames as $tagname)
    {
      $this->addCallback($tagname,$function);
    }
  }
  
  // Called when a tag has been fully extracted to call the user function.
  function callback($tagname,$attrs,$content)
  {
  }

  // Any uncontained text just goes to stdout.  
  function onUncontainedText($text)
  {
    print($text);
  }
  
  // Checks if we are interested in the tag.
  function onStartTag($tag)
  {
    if (isset($this->_callbacks[$tag]))
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
      if (is_array($this->_current))
      {
        $this->_log->debug("Buffering callback");
        ob_start();
      }
      $this->_log->debug("Calling callback for ".$tag);
      if (isset($this->_callbacks[$tag]))
      {
        call_user_func($this->_callbacks[$tag],$tag,$result['attrs'],$result['text']);
      }
      if (is_array($this->_current))
      {
        $text=ob_get_contents();
        ob_end_clean();
        $this->onText($text);
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
