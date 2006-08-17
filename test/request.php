<?

/*
 * Swim
 *
 * Tests for request issues
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

if (get_magic_quotes_gpc())
{
  $query = "test=\\'hello\\'";
  $result = decodeQuery($query);
  logTest('1','Strip slashes',$result['test']=="'hello'");
}

?>
