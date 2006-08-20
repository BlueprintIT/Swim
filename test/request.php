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

$result = decodeQuery('prices[0].price.value=27');
logTest('2', 'Query array', $result['prices'][0]['price']['value']==27);
$result = decodeQuery('prices[0][price][].value=bloober&prices[0][price][]value=bleeber');
logTest('3', 'Query array', $result['prices'][0]['price'][0]['value']=='bloober');
logTest('4', 'Query array', $result['prices'][0]['price'][1]['value']=='bleeber');
?>
