<?

/*
 * Swim
 *
 * Utility functions
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

function printArray($array,$indent="")
{
	print($indent."{\n");
	$newindent=$indent."  ";
	while(list($key, $value) = each($array))
  {
  	if (is_array($value))
  	{
  		print($newindent."$key => Array\n");
  		printArray($value,$newindent);
  	}
  	else
  	{
	  	print($newindent."$key => $value\n");
	  }
  }
  print($indent."}\n");
}

?>