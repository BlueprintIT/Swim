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

// A simplistic parser that pulls tags we are interested in out of a text stream.
class Parser
{
  var $_callbacks;
  var $_tagstack;
  var $_attrname;
  var $_state;
  var $_current;
  var $_quote;
  var $_taglist;
  var $_log;
  
  // Initialises the parser
  function Parser()
  {
    $this->_callbacks=array();
    $this->_tagstack=array();
    $this->_state=0;
    $this->_current="";
    $this->_attrname="";
    $this->_quote="";
    $this->_taglist="";
    $this->_log=LoggerManager::getLogger("Parser");
    $this->_log->debug("In state 0");
  }
  
  // Adds a callback to the parser. Tags with this name will be extracted.
  function addCallback($tagname,$function)
  {
    $this->_log->debug("Callback added for ".$tagname);
    $this->_callbacks[$tagname]=$function;
    if (strlen($this->_taglist)>0)
    {
      $this->_taglist.="|$tagname";
    }
    else
    {
      $this->_taglist=$tagname;
    }
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
    $this->_log->debug("Calling callback for ".$tagname);
    if (isset($this->_callbacks[$tagname]))
    {
      return call_user_func($this->_callbacks[$tagname],$tagname,$attrs,$content);
    }
  }
  
  // Outputs text into the current tag's buffer
  function output($text)
  {
    if (count($this->_tagstack)==0)
    {
      print($text);
    }
    else
    {
      $this->_current['text'].=$text;
    }
  }
  
  // Pushes a new tag onto the stack
  function pushStack($tagname)
  {
    $this->_log->debug("Pushing ".$tagname. " onto the stack");
    $this->_tagstack[]=array('tag' => $tagname, 'attrs' => array(), 'text' => "");
    $this->_current=&$this->_tagstack[count($this->_tagstack)-1];
  }
  
  // Pops the current tag from the stack and calls its callback function
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
    if (is_array($this->_current))
    {
      $this->_log->debug("Buffering callback");
      ob_start();
    }
    $this->callback($result['tag'],$result['attrs'],$result['text']);
    if (is_array($this->_current))
    {
      $this->output(ob_get_contents());
      ob_end_clean();
    }
  }
  
  // Parses a whole file in one call
  function parseFile($filename)
  {
    $this->_log->info("Parsing file ".$filename);
    $source=fopen($filename,"r");
    while (!feof($source))
    {
      $this->parseText(fgets($source));
    }
  }
  
  // Parses some text
  function parseText($text)
  {
    switch($this->_state)
    {
      // State 0 is where we are scanning for a new start tag. Any text before a new
      // start tag is just outputted into the current tag bugger.
      case 0:
        $regex="<((".$this->_taglist.")([\s$>]|\/>)";
        if (is_array($this->_current))
        {
          $regex.="|\/".$this->_current['tag'].">";
        }
        $regex="/^(.*?)".$regex. ")/";
        $this->_log->debug("Regex is ".$regex);
        if (preg_match($regex,$text,$matches))
        {
          $this->_log->debug("Found start or end tag");
          $remaining=substr($text,strlen($matches[0]));
          $this->output($matches[1]);
          if (isset($matches[3]))
          {
            $tagname=$matches[3];
            $this->_log->debug("Start tag for ".$tagname);
            $this->pushStack($tagname);
            if ($matches[4]==">")
            {
              $this->_log->debug("No attributes, remaining in state 0");
            }
            else if ($matches[4]=="/>")
            {
              $this->_log->debug("Simple tag. Popping.");
              $this->popStack();
            }
            else
            {
              $this->_log->debug("Moving to state 1");
              $this->_state=1;
            }
          }
          else
          {
            $this->_log->debug("End tag");
            $this->popStack();
          }
          $this->parseText($remaining);
        }
        else
        {
          $this->output($text);
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
            $this->popStack();
            $this->_state=0;
            $this->parseText(substr($text,2));
          }
          else if (preg_match("/^([A-Za-z:\.]+)=(\"|')/",$text,$matches))
          {
            $this->_log->debug("Found attribute ".$matches[1]." moving to state 2");
            $this->_attrname=$matches[1];
            $this->_quote=$matches[2];
            $this->_state=2;
            $this->_current['attrs'][$matches[1]]="";
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
          $this->_current['attrs'][$this->_attrname].=$matches[1];
          $this->_state=1;
          $this->parseText(substr($text,strlen($matches[0])));
        }
        else
        {
          $this->_current['attrs'][$this->_attrname].=$text;
        }
        break;
    }
  }
}

?>
