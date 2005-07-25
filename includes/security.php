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

define('PERMISSION_UNKNOWN',0);
define('PERMISSION_ALLOWED',1);
define('PERMISSION_DENIED',-1);
define('PERMISSION_DEFAULT',PERMISSION_ALLOWED);

define('PERMISSION_READ',0);
define('PERMISSION_WRITE',1);

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
	
	function __sleep()
	{
		unset($this->log);
		return array('user','groups','account','logged');
	}
	
	function __wakeup()
	{
		$this->log = &LoggerManager::getLogger('swim.user');
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

	function inAnyGroup($groups)
	{
		foreach ($groups as $group)
		{
			if ($this->inGroup($group))
			{
				return true;
			}
		}
		return false;
	}
		
	function inAllGroups($groups)
	{
		foreach ($groups as $group)
		{
			if (!($this->inGroup($group)))
			{
				return false;
			}
		}
		return true;
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
	
	function checkSpecificPermission($permission,$dir,$file,$lock)
	{
		$this->log->debug('Checking permission on '.$file.' in dir '.$dir);
		
		$perm=PERMISSION_UNKNOWN;
		if (is_readable($dir.'/access'))
		{
			if ($lock)
			{
				$lck=lockResourceRead($dir);
			}
			$access=fopen($dir.'/access','r');
			$line=explode(':',trim(fgets($access)));
			
			if ($line[$permission]=='INHERIT')
			{
			}
			else if ($line[$permission]=='DEFAULT')
			{
				$perm=PERMISSION_DEFAULT;
			}
			else if ($line[$permission]=='ALLOW')
			{
				$perm=PERMISSION_ALLOWED;
			}
			else if ($line[$permission]=='DENY')
			{
				$perm=PERMISSION_DENIED;
			}
			$line=fgets($access);
			while ($line!==false)
			{
				$line=trim($line);
				$parts=explode(':',$line);
				$files=$parts[0];
				if (($files[0]!='/')||($files[strlen($files)-1]!='/'))
				{
					$files=preg_quote($files,'/');
					$files='/'.preg_replace(array('/\\\\\*/','/\\\\\?/'),array('.*','.'),$files).'/';
				}
				if (preg_match($files,$file))
				{
					$this->log->debug('Matched file to '.$line);
					$denymatch=$parts[($permission*2)+2];
					if (strlen($denymatch)>0)
					{
						$this->log->debug('Deny match is '.$denymatch);
						if ($this->inAnyGroup(explode(',',$denymatch)))
							return PERMISSION_DENIED;
					}
					$allowmatch=$parts[($permission*2)+1];
					if (strlen($allowmatch)>0)
					{
						$this->log->debug('Allow match is '.$allowmatch);
						if ($this->inAnyGroup(explode(',',$allowmatch)))
						{
							$perm=PERMISSION_ALLOWED;
							break;
						}
					}
				}
				$line=fgets($access);
			}
			fclose($access);
			if ($lock)
			{
				unlockResource($lck);
			}
		}
		return $perm;
	}
	
	function checkPermission($permission,&$resource)
	{
		global $_PREFS;
		
		$resource->lockRead();
		if ($resource->isFile())
		{
			$file=basename($resource->id);
			$dir=dirname($resource->id);
			while ($dir!='.')
			{
				$perm=$this->checkSpecificPermission($permission,$resource->getDir().'/'.$dir,$file,false);
				if ($perm!=PERMISSION_UNKNOWN)
					return $perm;

				$dir=dirname($dir);
			}
		}
		else
		{
			$file='resource.conf';
		}
		
		$perm=$this->checkSpecificPermission($permission,$resource->getDir(),$file,false);
		if ($perm!=PERMISSION_UNKNOWN)
			return $perm;
		
		$resource->unlock();
		
		$container=&$resource->container;
		$perm=$this->checkSpecificPermission($permission,$container->getDir(),$file,true);
		if ($perm!=PERMISSION_UNKNOWN)
			return $perm;
		
		if (is_a($container,'Page'))
		{
			$container=&$container->container;
			$perm=$this->checkSpecificPermission($permission,$container->getDir(),$file,true);
			if ($perm!=PERMISSION_UNKNOWN)
				return $perm;
		}
		
		return $this->checkSpecificPermission($permission,$_PREFS->getPref('storage.basedir'),$file,true);
	}
	
	function getPermission($permission,&$resource)
	{
		$perm=$this->checkPermission($permission,$resource);
		if ($perm==PERMISSION_UNKNOWN)
			$perm=PERMISSION_DEFAULT;
		$this->log->debug('Permission for '.$permission.' is '.$perm);
		return $perm;
	}
	
	function canRead(&$resource)
	{
		$this->log->debug('Checking canRead');
		return $this->getPermission(PERMISSION_READ,$resource)==PERMISSION_ALLOWED;
	}
	
	function canWrite(&$resource)
	{
		$this->log->debug('Checking canWrite');
		return $this->getPermission(PERMISSION_WRITE,$resource)==PERMISSION_ALLOWED;
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