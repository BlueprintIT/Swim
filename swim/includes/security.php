<?

/*
 * Swim
 *
 * Security engine
 *
 * Copyright Blueprint IT Ltd. 2006
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
define('PERMISSION_EDIT',2);
define('PERMISSION_DELETE',3);

class User
{
	var $user;
  var $name;
  var $password;
  var $exists = false;
	var $groups = array();
	var $log;
	var $logged = false;
	
	function User($username=false)
	{
    global $_STORAGE;
    
		$this->log = LoggerManager::getLogger('swim.user');
		if ($username!==false)
		{
      $this->log->debug('Creating user '.$username);
      $this->user=$username;
      $results = $_STORAGE->query("SELECT * FROM User WHERE id='".$_STORAGE->escape($username)."';");
      if ($results->valid())
      {
        $this->log->debug('User is valid');
        $details=$results->fetch();
        $this->exists=true;
        $this->name=$details['name'];
        $this->password=$details['password'];
        
        $results = $_STORAGE->query("SELECT access FROM UserAccess WHERE user='".$_STORAGE->escape($username)."';");
        while ($results->valid())
        {
          $details=$results->fetch();
          $this->log->debug('Found group '.$details['access']);
          $this->groups[]=$details['access'];
        }
      }
		}
		else
		{
			$this->user='guest';
			$this->groups=array();
		}
	}
	
	function setPassword($password)
	{
    global $_STORAGE;
    
    $_STORAGE->queryExec("UPDATE User set password='".$_STORAGE->escape(md5($password))."' WHERE id='".$_STORAGE->escape($this->user)."';");
	}
  
  function login($password)
  {
    return (md5($password)==$this->password);
  }
  
	function userExists()
	{
		return $this->exists;
	}
	
	function getUsername()
	{
		return $this->user;
	}
  
  function getGroups()
  {
    return $this->groups;
  }
  
  function getName()
  {
    return $this->name;
  }
	
	function setName($value)
	{
    global $_STORAGE;
    
    if ($this->exists)
    {
      $_STORAGE->queryExec("UPDATE User set name='".$_STORAGE->escape($value)."' WHERE id='".$_STORAGE->escape($this->user)."';");
      $this->name=$value;
    }
    else
    {
      $this->log->warn('Attempt to change non-existant user.');
    }
	}
  
  function hasPermission($perm,$access)
  {
    global $_STORAGE;
    
    if ($this->inGroup('root'))
    {
      return true;
    }
    
    switch ($access)
    {
      case PERMISSION_READ:
        $type='read';
        break;
      case PERMISSION_WRITE:
        $type='write';
        break;
      case PERMISSION_EDIT:
        $type='edit';
        break;
      case PERMISSION_DELETE:
        $type='remove';
        break;
      default:
        return false;
    }
    $results = $_STORAGE->query("SELECT Permission.".$type." FROM UserAccess JOIN Permission ON UserAccess.access=Permission.access WHERE section='".$_STORAGE->escape($perm)."';");
    $result = 0;
    while ($results->valid())
    {
      $id=$results->fetch();
      if ($id[0]<0)
      {
        return false;
      }
      $result+=$id[0];
    }
    if ($result>0)
    {
      return true;
    }
    return false;
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
    global $_STORAGE;
    
    if ($this->exists)
    {
  		if (!in_array($group,$this->groups))
  		{
  			$this->groups[]=$group;
        $_STORAGE->queryExec("INSERT INTO UserAccess (user,access) VALUES('".$_STORAGE->escape($this->user)."','".$_STORAGE->escape($group)."');");
  		}
    }
    else
    {
      $this->log->warn('Attempt to change non-existant user.');
    }
	}
	
	function removeGroup($group)
	{
    global $_STORAGE;
    
    if ($this->exists)
    {
  		$nwgroups=array();
  		foreach ($this->groups as $grp)
  		{
  			if ($grp!=$group)
  			{
  				$newgroups[]=$grp;
  			}
  		}
  		$this->groups=$nwgroups;
      $_STORAGE->queryExec("DELETE FROM UserAccess WHERE user='".$_STORAGE->escape($this->user)."' AND access='".$_STORAGE->escape($group)."';");
    }
    else
    {
      $this->log->warn('Attempt to change non-existant user.');
    }
	}
	
	function clearGroups()
	{
    global $_STORAGE;
    
    if ($this->exists)
    {
  		$this->groups=array();
      $_STORAGE->queryExec("DELETE FROM UserAccess WHERE user='".$_STORAGE->escape($this->getUsername())."';");
    }
    else
    {
      $this->log->warn('Attempt to change non-existant user.');
    }
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
				LockManager::lockResourceRead($dir);
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
				LockManager::unlockResource($dir);
			}
		}
		return $perm;
	}
	
	function checkPermission($permission,$resource)
	{
		global $_PREFS;
		
    if ($this->inGroup('root'))
    {
      return PERMISSION_ALLOWED;
    }
    
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
				{
					$resource->unlock();
					return $perm;
				}

				$dir=dirname($dir);
			}
		}
		else
		{
			$file='resource.conf';
		}
		
		$perm=$this->checkSpecificPermission($permission,$resource->getDir(),$file,false);
		if ($perm!=PERMISSION_UNKNOWN)
		{
			$resource->unlock();
			return $perm;
		}
		
		$resource->unlock();
		
		while (isset($resource->parent))
		{
			$resource=$resource->parent;
			$perm=$this->checkSpecificPermission($permission,$resource->getDir(),$file,$resource->isWritable());
			if ($perm!=PERMISSION_UNKNOWN)
				return $perm;
		}
		
		$container=$resource->container;
		$perm=$this->checkSpecificPermission($permission,$container->getDir(),$file,$container->isWritable());
		if ($perm!=PERMISSION_UNKNOWN)
			return $perm;
		
		return $this->checkSpecificPermission($permission,$_PREFS->getPref('storage.basedir'),$file,false);
	}
	
	function getPermission($permission,$resource)
	{
		$perm=$this->checkPermission($permission,$resource);
		if ($perm==PERMISSION_UNKNOWN)
			$perm=PERMISSION_DEFAULT;
		$this->log->debug('Permission for '.$permission.' is '.$perm);
		return $perm;
	}
	
	function canRead($resource)
	{
		//return true;
		$this->log->debug('Checking canRead');
  	return $this->getPermission(PERMISSION_READ,$resource)==PERMISSION_ALLOWED;
	}
	
	function canWrite($resource)
	{
		//return true;
		$this->log->debug('Checking canWrite');
    if (($resource->isWritable())&&($this->hasPermission('documents',PERMISSION_WRITE)))
    {
  		return $this->getPermission(PERMISSION_WRITE,$resource)==PERMISSION_ALLOWED;
    }
    return false;
	}
}

class Group
{
  var $id;
  var $name;
  var $description;
  
  function Group($id)
  {
    global $_STORAGE;
    
    $this->id = $id;
    $results = $_STORAGE->query("SELECT * FROM Access WHERE id='".$_STORAGE->escape($id)."';");
    if ($results->valid())
    {
      $details=$results->fetch();
      $this->valid=true;
      $this->name = $details['name'];
      $this->description = $details['description'];
    }
    else
    {
      $this->valid=false;
    }
  }
  
  function getID()
  {
    return $this->id;
  }
  
  function getName()
  {
    return $this->name;
  }
  
  function getDescription()
  {
    return $this->description;
  }
}

class UserManager
{
  static function login($username,$password)
  {
    global $_USER;
    
    $newuser = new User($username);
    if (($newuser->userExists())&&($newuser->login($password)))
    {
      $_SESSION['Swim.User']=$username;
      $_USER=$newuser;
      return $newuser;
    }
    return false;
  }
  
  static function logout()
  {
    global $_USER;
    
    $_USER = new User();
    unset($_SESSION['Swim.User']);
  }
  
  static function createUser($username)
  {
    global $_STORAGE;
    
    $_STORAGE->queryExec("INSERT INTO User (id) VALUES ('".$_STORAGE->escape($username)."');");
  	$user = new User($username);
  	return $user;
  }
  
  static function deleteUser($user)
  {
  	global $_STORAGE;
  	
    if ($user->userExists())
    {
      $_STORAGE->queryExec("DELETE FROM User WHERE id='".$_STORAGE->escape($user->getUsername())."';");
      $_STORAGE->queryExec("DELETE FROM UserAccess WHERE user='".$_STORAGE->escape($user->getUsername())."';");
    }
    return true;
  }
  
  static function getAllUsers()
  {
    global $_STORAGE;
    
    $users=array();
    $result = $_STORAGE->query("SELECT id FROM User;");
    while ($result->valid())
    {
      $id = $result->fetch();
      $user = new User($id[0]);
      $users[$user->getUsername()]=$user;
    }
    return $users;
  }
  
  static function getGroups()
  {
    global $_STORAGE;
    
    $groups=array();
    $result = $_STORAGE->query("SELECT id FROM Access WHERE id<>'root';");
    while ($result->valid())
    {
      $id = $result->fetch();
      $group = new Group($id[0]);
      $groups[$group->getID()]=$group;
    }
    return $groups;
  }
}

class UserAdminSection extends AdminSection
{
  public function getName()
  {
    return 'User Management';
  }
  
  public function getPriority()
  {
    return ADMIN_PRIORITY_SECURITY;
  }
  
  public function getURL()
  {
    $request = new Request();
    $request->method='users';
    return $request->encode();
  }
  
  public function isAvailable()
  {
    global $_USER;
    
    return $_USER->hasPermission('users',PERMISSION_READ);
  }
  
  public function isSelected($request)
  {
    if ($request->method == 'users')
      return true;
      
    return false;
  }
}

// Start up the session
session_name('SwimSession');
session_start();


if (isset($_SESSION['Swim.User']))
{
	$GLOBALS['_USER'] = new User($_SESSION['Swim.User']);
  $GLOBALS['_USER']->logged=true;
}
else
{
	$GLOBALS['_USER'] = new User();
}

AdminManager::addSection(new UserAdminSection());

?>