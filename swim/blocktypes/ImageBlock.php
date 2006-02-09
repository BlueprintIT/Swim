<?

/*
 * Swim
 *
 * A simple block to display an image.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class ImageBlock extends Block
{
  function ImageBlock($container,$id,$version)
  {
    $this->Block($container,$id,$version);
  }

  function displayContent($parser,$attrs,$text)
  {
    if ($this->prefs->isPrefSet('block.image.src'))
    {
      print('<image src="'.$this->prefs->getPref('block.image.src').'"/>');
    }
    return true;
  }
}

?>
