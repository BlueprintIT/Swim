<?

/*
 * Swim
 *
 * XML support functions
 *
 * Copyright Blueprint IT Ltd. 2005
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

?>
