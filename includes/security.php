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
	var $groups = array();
	var $account = array();
	var $log;
	var $logged = false;
	
	function User($username=false)
	{
		$this->log = &LoggerManager::getLogger('swim.user');
		if ($username!==false)
		{
			$this->become($username);
		}
		else
		{
			$this->user='guest';
			$this->groups=array();
			$this->account=array();
		}
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
	
	function setPassword($password)
	{
		$this->account['password']=md5($password);
	}
	
	function store()
	{
		global $_PREFS;
		$file = $_PREFS->getPref('security.database');
    if (is_writable($file))
    {
			if (lockSecurityWrite())
			{
				$lines = array();
	      $source=fopen($file,'r');
	      while (!feof($source))
	      {
	        $line=trim(fgets($source));
        	$details=explode(':',$line);
        	if ($details[0]!=$this->username)
        	{
        		$lines[]=$line;
        	}
	      }
	      fclose($source);
	      $source=fopen($file,'w');
	      foreach ($lines as $line)
	      {
	      	fwrite($source,$line."\n");
	      }
	      $line=$this->account['username'].':'.$this->account['password'].':'.$this->account['groups'].':'.$this->account['name'];
	      fwrite($source,$line."\n");
	      fclose($source);
	      unlockSecurity();
      }
  	}
	}
	
	function loadFromDescriptor($line)
	{
		$this->logged=false;
  	$details=explode(':',$line);
		$this->user=$details[0];
  	$this->account = array('username'=>$details[0],'password'=>$details[1],'groups'=>$details[2],'name'=>$details[3]);
  	$this->groups = explode(',',$this->account['groups']);
	}
	
	function become($username)
	{
		global $_PREFS;
		
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
	        	$this->loadFromDescriptor($line);
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
	
	function getName()
	{
		return $this->account['name'];
	}

	function setName($value)
	{
		$this->account['name']=$value;
	}

	function hasPrivilege($priv)
	{
		if ($priv[strlen($priv)-1]==')')
		{
			$pos=strrpos($priv,'(');
			if ($pos>0)
			{
				$type=substr($priv,0,$pos);
				$value=substr($priv,$pos+1,-1);
				if ($type=='group')
				{
					return $this->inGroup($value);
				}
				else if ($type=='user')
				{
					return $this->getUsername()==$value;
				}
			}
		}
		else if ($priv=='*')
		{
			return $this->isLoggedIn();
		}
		return false;
	}
	
	function hasAnyPrivilege($privs)
	{
		foreach ($privs as $priv)
		{
			if ($this->hasPrivilege($priv))
			{
				return true;
			}
		}
		return false;
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
		
	function addGroup($group)
	{
		if (!in_array($group,$this->groups))
		{
			$this->groups[]=$group;
			if (strlen($this->account['groups'])>0)
			{
				$this->account['groups'].=',';
			}
			$this->account['groups'].=$group;
		}
	}
	
	function removeGroup($group)
	{
		$nwgroups=array();
		$this->account['groups']='';
		foreach ($this->groups as $grp)
		{
			if ($grp!=$group)
			{
				$newgroups[]=$grp;
				$this->account['groups'].=','.$grp;
			}
		}
		if (strlen($this->account['groups'])>0)
		{
			$this->account['groups']=substr($this->account['groups'],1);
		}
		$this->groups=$nwgroups;
	}
	
	function clearGroups()
	{
		$this->groups=array();
		$this->account['groups']='';
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
		$perm=PERMISSION_UNKNOWN;
		
		if ((!isset($file))||(strlen($file)==0))
		{
			$this->log->error('Bad file specified in permissions check. Bailing out.');
			$this->log->debug('Dir was '.$dir);
			return $perm;
		}
		$this->log->debug('Checking permission on '.$file.' in dir '.$dir);
		
		if (is_readable($dir.'/access'))
		{
			if ($lock)
			{
				$lck=lockResourceRead($dir);
			}
			$access=fopen($dir.'/access','r');
			do
			{
				$line=trim(fgets($access));
			} while ($line[0]=='#');
			$line=explode(':',$line);
			
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
				if ($line[0]=='#')
					continue;
					
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
						if ($this->hasAnyPrivilege(explode(',',$denymatch)))
							return PERMISSION_DENIED;
					}
					$allowmatch=$parts[($permission*2)+1];
					if (strlen($allowmatch)>0)
					{
						$this->log->debug('Allow match is '.$allowmatch);
						if ($this->hasAnyPrivilege(explode(',',$allowmatch)))
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
		if (($resource->isFile())&&(strlen($resource->id)>0))
		{
			if ($resource->isExistingDir())
			{
				$file='resource.conf';
				$dir=$resource->id;
			}
			else
			{
				$file=basename($resource->id);
				$dir=dirname($resource->id);
			}
			$this->log->debug('Checking permissions on '.$dir.' '.$file.' '.($resource->id));
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
		
		while (isset($resource->parent))
		{
			$resource=&$resource->parent;
			$perm=$this->checkSpecificPermission($permission,$resource->getDir(),$file,false);
			if ($perm!=PERMISSION_UNKNOWN)
				return $perm;
		}
		
		$container=&$resource->container;
		$perm=$this->checkSpecificPermission($permission,$container->getDir(),$file,$container->isWritable());
		if ($perm!=PERMISSION_UNKNOWN)
			return $perm;
		
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
		//return true;
		$this->log->debug('Checking canRead');
		return $this->getPermission(PERMISSION_READ,$resource)==PERMISSION_ALLOWED;
	}
	
	function canWrite(&$resource)
	{
		//return true;
		$this->log->debug('Checking canWrite');
		return $this->getPermission(PERMISSION_WRITE,$resource)==PERMISSION_ALLOWED;
	}
}

function &createUser($username)
{
	$user = new User($username);
	if ($user->user==$username)
	{
		return false;
	}
	$user->user=$username;
	$user->account['username']=$username;
	return $user;
}

function deleteUser(&$user)
{
	global $_PREFS;
	
	$file = $_PREFS->getPref('security.database');
	if (is_writable($file))
	{
		$users=array();
		lockSecurityWrite();
	  $lines=array();
	  $source=fopen($file,'r');
	  while (!feof($source))
	  {
      $line=trim(fgets($source));
    	$details=explode(':',$line);
    	if ($details[0]!=$user->getUsername())
    	{
    		$lines[]=$line;
    	}
    }
    fclose($source);
    $source=fopen($file,'w');
    foreach ($lines as $line)
    {
    	fwrite($source,$line."\n");
    }
    fclose($source);
	  unlockSecurity();
	}
}

function &getAllUsers()
{
	global $_PREFS;
	$file = $_PREFS->getPref('security.database');
	if (is_readable($file))
	{
		$users=array();
		lockSecurityRead();
	  $source=fopen($file,'r');
	  while (!feof($source))
	  {
	    $line=trim(fgets($source));
	    if (strlen($line)>0)
	    {
		    $user = new User();
		    $user->loadFromDescriptor($line);
		    $users[$user->getUsername()]=&$user;
		    unset($user);
	    }
	  }
		unlockSecurity();
		return $users;
	}
	else
	{
		return array();
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