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
    	$src = $this->prefs->getPref('block.image.src');
    	if (strlen($src)>0)
    	{
	      print('<image src="'.$src.'"');
	      foreach ($attrs as $key => $value)
	      {
	      	if ($key == 'notag')
	      		continue;
	      		
	      	if ($key == 'id')
	      		continue;
	      		
		      print($key.'="'.htmlentities($value).'"');
	      }
	      print('/>');
	    }
    }
    return true;
  }
}

?>
