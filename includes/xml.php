<?

/*
 * Swim
 *
 * XML support functions
 *
 * Copyright Blueprint IT Ltd. 2007
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function getDOMText($element)
{
  $text='';
  $el=$element->firstChild;
  while ($el!==null)
  {
    if ($el->nodeType==XML_TEXT_NODE)
    {
      $text.=$el->nodeValue;
    }
    $el=$el->nextSibling;
  }
  return $text;
}

class XMLSerialized
{
  protected function parseElement($element)
  {
    if (isset($this->log))
      $this->log->warn('Unhandler element '.$element->tagName);
  }
  
  protected function parseAttributes($element)
  {
  }

  public function load($element)
  {
    $this->parseAttributes($element);
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        $this->parseElement($el);
      }
      $el=$el->nextSibling;
    }
  }
}

?>
