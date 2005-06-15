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

function lockSecurityRead()
{
	global $_PREFS,$_LOCKS;
	
	$lockfile = $_PREFS->getPref('security.lock');
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_SH))
	{
		$_LOCKS['security']=&$file;
		return true;
	}
	else
	{
		return false;
	}
}

function lockSecurityWrite()
{
	global $_PREFS,$_LOCKS;
	
	$lockfile = $_PREFS->getPref('security.lock');
	$file = fopen($lockfile,'a');
	if (flock($file,LOCK_EX))
	{
		$_LOCKS['security']=&$file;
		return true;
	}
	else
	{
		return false;
	}
}

function unlockSecurity()
{
	global $_LOCKS;
	
	if (isset($_LOCKS['security']))
	{
		$file=&$_LOCKS['security'];
		unset($_LOCKS['security']);
		flock($file,LOCK_UN);
		fclose($file);
	}
}

class User
{
	var $user;
	var $log;
	
	function User()
	{
		$this->log = &LoggerManager::getLogger('swim.user');
	}
	
	function getUsername()
	{
		return $this->user;
	}
	
	function isAdmin()
	{
		return isset($this->user);
	}
	
	function canAccess(&$request)
	{
		if ($this->isAdmin())
		{
			return true;
		}
		if ($request->method=='admin')
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
		$expected=$user.':'.md5($password).':';
		$this->log->debug('Checking for '.$expected);
		$file = $_PREFS->getPref('security.database');
    if (is_readable($file))
    {
			if (lockSecurityRead())
			{
	      $source=fopen($file,'r');
	      while (!feof($source))
	      {
	        $line=fgets($source);
	        $this->log->debug('Checking against '.$line);
	        if (substr($line,0,strlen($expected))==$expected)
	        {
	        	$this->user=$user;
	        	$success=true;
	        	break;
	        }
	      }
	      fclose($source);
	      unlockSecurity();
      }
  	}
  	else
  	{
  		$this->log->error('Could not read security database '.$file);
  	}
  	return $success;
	}
}

// Start up the session
session_name('SwimSession');
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