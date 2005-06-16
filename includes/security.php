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
	global $_PREFS;
	lockResourceRead($_PREFS->getPref('storage.security'),'security');
	return true;
}

function lockSecurityWrite()
{
	global $_PREFS;
	lockResourceWrite($_PREFS->getPref('storage.security'),'security');
	return true;
}

function unlockSecurity()
{
	unlockResource('security');
}

function login($username,$password)
{
	global $_USER;
	
	$newuser = new User($username);
	if ($newuser->userExists())
	{
		if (md5($password)==$newuser->account['password'])
		{
			$newuser->logged=true;
			$_USER=&$newuser;
			$_SESSION['Swim.User']=&$_USER;
			
			return $_USER;
		}
	}
	return false;
}

function logout()
{
	global $_USER;
	
	$_USER->become('guest');
}

class User
{
	var $user;
	var $groups;
	var $account;
	var $log;
	var $logged = false;
	
	function User($username)
	{
		$this->log = &LoggerManager::getLogger('swim.user');
		$this->become($username);
	}
	
	function become($username)
	{
		global $_PREFS;
		
		$this->user=$username;
		$this->groups = array();
		$this->account = array();
		$this->logged=false;
		
		$file = $_PREFS->getPref('security.database');
    if (is_readable($file))
    {
			if (lockSecurityRead())
			{
	      $source=fopen($file,'r');
	      while (!feof($source))
	      {
	        $line=trim(fgets($source));
	        if (substr($line,0,strlen($username)+1)==$username.':')
	        {
	        	$details=explode(':',$line);
	        	$this->account = array('username'=>$details[0],'password'=>$details[1],'groups'=>$details[2]);
	        	$this->groups = explode(',',$this->account['groups']);
	        	break;
	        }
	      }
	      fclose($source);
	      unlockSecurity();
      }
  	}
	}
	
	function userExists()
	{
		return count($this->account)>0;
	}
	
	function getUsername()
	{
		return $this->user;
	}
	
	function inGroup($group)
	{
		$result=in_array($group,$this->groups);
		if ($this->log->isDebugEnabled())
		{
			if ($result)
			{
				$this->log->debug('User is in group '.$group);
			}
			else
			{
				$this->log->debug('User is not in group '.$group);
			}
		}
		return $result;
	}
	
	function isLoggedIn()
	{
		return $this->logged;
	}
	
	function isAdmin()
	{
		return $this->inGroup('admin');
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
	$_USER = new User('guest');
	$_SESSION['Swim.User']=&$_USER;
}

?>