<?

/*
 * Swim
 *
 * Security engine
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class User
{
	var $user;
	
	function getUsername()
	{
		return $this->user;
	}
	
	function isAdmin()
	{
		return isset($this->user);
	}
	
	function logout()
	{
		unset($this->user);
	}
	
	function login($user,$password)
	{
		global $_PREFS;
		
		$result=false;
		$expected=$user.":".md5($password).":";
		$file = $_PREFS->getPref("security.database");
    if (is_readable($file))
    {
      $source=fopen($file,"r");
      if (flock($source,LOCK_SH))
      {
	      while (!feof($source))
	      {
	        $line=fgets($source);
	        if (substr($line,0,strlen($expected))==$expected)
	        {
	        	$this->user=$user;
	        	$result=true;
	        	break;
	        }
	      }
	      flock($source,LOCK_UN);
      }
      fclose($source);
  	}
  	return $result;
	}
}

if (isset($_SESSION['Swim.User']))
{
	$_USER=&$_SESSION['Swim.User'];
}
else
{
	$_USER = new User;
	$_SESSION['Swim.User']=&$_USER;
}

?>