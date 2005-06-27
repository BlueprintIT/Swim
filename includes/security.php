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
			$line=fgets($access);
			while ($line!==false)
			{
				$line=trim($line);
				$parts=explode(':',$line);
				$files=$parts[0];
				if (($files[0]!='/')||($files[strlen($files)-1]!='/'))
				{
					$files=preg_quote($files,'/');
					$files='/'.preg_replace(array('/\\*/','/\\?/'),array('.*','.'),$files).'/';
				}
				if (preg_match($files,$file))
				{
					$denymatch=$parts[($permission*2)+2];
					if (strlen($denymatch)>0)
					{
						if ($this->inAnyGroup(explode(',',$denymatch)))
							return PERMISSION_DENIED;
					}
					if ($perm==PERMISSION_UNKNOWN)
					{
						$allowmatch=$parts[($permission*2)+1];
						if (strlen($allowmatch)>0)
						{
							if ($this->inAnyGroup(explode(',',$allowmatch)))
							{
								$perm=PERMISSION_DENIED;
								break;
							}
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
			$path=$resource->path;
			$file=basename($path);
			$path=dirname($path);
			while ($path!='.')
			{
				$perm=$this->checkSpecificPermission($permission,$resource->getDir().'/'.$path,$file,false);
				if ($perm!=PERMISSION_UNKNOWN)
				{
					$resource->unlock();
					return $perm;
				}
				$path=dirname($path);
			}
		}
		else
		{
			$file=$resource->type.'.conf';
		}
		
		$path=$resource->getDir();
		$perm=$this->checkSpecificPermission($permission,$path,$file,false);
		$resource->unlock();
		if ($perm!=PERMISSION_UNKNOWN)
		{
			return $perm;
		}
		
		$path=$resource->getResource();
		$perm=$this->checkSpecificPermission($permission,$path,$file,false);
		$resource->unlock();
		if ($perm!=PERMISSION_UNKNOWN)
		{
			return $perm;
		}
		
		if (isset($resource->block))
		{
			$block=&$resource->getBlock();
			if (is_a($block->container,'Page'))
			{
				$path=$block->container->getDir();
				$perm=$this->checkSpecificPermission($permission,$path,$file,true);
				if ($perm!=PERMISSION_UNKNOWN)
				{
					return $perm;
				}
				
				$page=&$resource->getPage();
				$path='storage.pages.'.$page->container;
			}
			else
			{
				$path='storage.blocks.'.$resource->container;
			}
		}
		else if (isset($resource->template))
		{
			$path='storage.templates';
		}
		else if (isset($resource->page))
		{
			$path='storage.pages.'.$resource->container;
		}

		$path=$_PREFS->getPref($path);
		$perm=$this->checkSpecificPermission($permission,$path,$file,true);
		if ($perm!=PERMISSION_UNKNOWN)
		{
			return $perm;
		}

		$path=$_PREFS->getPref('storage.basedir');
		return $this->checkSpecificPermission($permission,$path,$file,true);
	}
	
	function getPermission($permission,&$resource)
	{
		$perm=$this->checkPermission($permission,$resource);
		return $perm != PERMISSION_DENIED;
	}
	
	function canRead(&$resource)
	{
		return true;
		//return $this->getPermission(PERMISSION_READ,$resource);
	}
	
	function canWrite(&$resource)
	{
		return $this->inGroup('admin');
		//return $this->getPermission(PERMISSION_WRITE,$resource);
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