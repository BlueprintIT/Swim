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
	var $log;
	
	function User()
	{
		$this->log = &LoggerManager::getLogger("swim.user");
	}
	
	function getUsername()
	{
		return $this->user;
	}
	
	function isAdmin()
	{
		return isset($this->user);
	}
	
	function canAccess(&$page)
	{
		if ($this->isAdmin())
		{
			return true;
		}
		if ($page->request->mode=="admin")
		{
			return false;
		}
		return true;
	}
	
	function logout()
	{
		unset($this->user);
	}
	
	function login($user,$password)
	{
		global $_PREFS;
		
		$success=false;
		$expected=$user.":".md5($password).":";
		$this->log->debug("Checking for ".$expected);
		$file = $_PREFS->getPref("security.database");
    if (is_readable($file))
    {
      $source=fopen($file,"r");
      if (flock($source,LOCK_SH))
      {
	      while (!feof($source))
	      {
	        $line=fgets($source);
	        $this->log->debug("Checking against ".$line);
	        if (substr($line,0,strlen($expected))==$expected)
	        {
	        	$this->user=$user;
	        	$success=true;
	        	break;
	        }
	      }
	      flock($source,LOCK_UN);
      }
      fclose($source);
  	}
  	else
  	{
  		$this->log->error("Could not read security database ".$file);
  	}
  	return $success;
	}
}

// Start up the session
session_name("SwimSession");
session_start();


if (isset($_SESSION['Swim.User']))
{
	$_USER=&$_SESSION['Swim.User'];
}
else
{
	$_USER = new User();
	$_SESSION['Swim.User']=&$_USER;
}

?>