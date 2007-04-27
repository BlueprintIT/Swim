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

function setDOMText($element, $value)
{
  $text='';
  while ($element->listChild !== null)
    $element->removeChild($element->lastChild);
  $element->appendChild($element->ownerDocument->createTextNode($value));
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
    self::parse($this, $element);
  }
  
  public static function parse($sink, $element)
  {
    $sink->parseAttributes($element);
    $el=$element->firstChild;
    while ($el!==null)
    {
      if ($el->nodeType==XML_ELEMENT_NODE)
      {
        $sink->parseElement($el);
      }
      $el=$el->nextSibling;
    }
  }
}

?>
