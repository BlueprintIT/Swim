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
	private $user;
  private $name;
  private $password;
  private $exists = false;
	private $groups = array();
	private $log;
	public $logged = false;
	
	function User($username=false)
	{
		$this->log = LoggerManager::getLogger('swim.user');
		if ($username!==false)
      $this->user=$username;
    else
      $this->user = 'guest';
    $this->reload();
	}
  
  function reload()
  {
    global $_STORAGE;
    
    $this->exists = false;
    $this->name = 'Guest';
    $this->groups = array();
    unset($this->password);
    if ($this->user!='guest')
    {
      $results = $_STORAGE->query("SELECT * FROM User WHERE id='".$_STORAGE->escape($this->user)."';");
      if ($results->valid())
      {
        $this->log->debug('User is valid');
        $details=$results->fetch();
        $this->exists=true;
        $this->name=$details['name'];
        $this->password=$details['password'];
        
        $results = $_STORAGE->query("SELECT access FROM UserAccess WHERE user='".$_STORAGE->escape($this->user)."';");
        while ($results->valid())
        {
          $details=$results->fetch();
          $this->log->debug('Found group '.$details['access']);
          $this->groups[]=UserManager::getGroup($details['access']);
        }
      }
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
    
    if ($this->inGroup(UserManager::getGroup('root')))
    {
      return true;
    }
    
    switch ($access)
    {
      case PERMISSION_READ:
        $type='canread';
        break;
      case PERMISSION_WRITE:
        $type='canwrite';
        break;
      case PERMISSION_EDIT:
        $type='canedit';
        break;
      case PERMISSION_DELETE:
        $type='canremove';
        break;
      default:
        return false;
    }
    $this->log->debug('Checking '.$this->user.' for '.$perm.' '.$type);
    $results = $_STORAGE->query("SELECT Permission.".$type." FROM UserAccess JOIN Permission ON UserAccess.access=Permission.access WHERE section='".$_STORAGE->escape($perm)."' AND user=\"".$_STORAGE->escape($this->user)."\";");
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
    $this->log->debug('Found '.$result);
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
					return $this->inGroup(Usermanager::getGroup($value));
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
        $_STORAGE->queryExec("INSERT INTO UserAccess (user,access) VALUES('".$_STORAGE->escape($this->user)."','".$_STORAGE->escape($group->getID())."');");
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
      $_STORAGE->queryExec("DELETE FROM UserAccess WHERE user='".$_STORAGE->escape($this->user)."' AND access='".$_STORAGE->escape($group->getID())."';");
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
				$this->log->debug('User is in group '.$group->getID());
			}
			else
			{
				$this->log->debug('User is not in group '.$group->getID());
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
    global $_USER;
    
    if ($this === $_USER)
    {
      $perm = ObjectCache::getItem('canRead',$resource->getPath());
      if ($perm !== null)
        return $perm;
    }
		$this->log->debug('Checking canRead');
  	$perm = $this->getPermission(PERMISSION_READ,$resource)==PERMISSION_ALLOWED;

    if ($this === $_USER)
      ObjectCache::setItem('canRead', $resource->getPath(), $perm);

    return $perm;
	}
	
	function canWrite($resource)
	{
    global $_USER;
    
    if ($this === $_USER)
    {
      $perm = ObjectCache::getItem('canWrite',$resource->getPath());
      if ($perm !== null)
        return $perm;
    }
		$this->log->debug('Checking canWrite');
    $perm = false;
    if (($resource->isWritable())&&($this->hasPermission('documents',PERMISSION_WRITE)))
  		$perm = $this->getPermission(PERMISSION_WRITE,$resource)==PERMISSION_ALLOWED;

    if ($this === $_USER)
      ObjectCache::setItem('canWrite', $resource->getPath(), $perm);

    return $perm;
	}
}

class Group
{
  var $id;
  var $name;
  var $description;
  var $valid;
  
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
  
  function groupExists()
  {
    return $this->valid;
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
  public static function login($username,$password)
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
  
  public static function logout()
  {
    global $_USER;
    
    $_USER = new User();
    unset($_SESSION['Swim.User']);
  }
  
  public static function getUser($name)
  {
    $user = ObjectCache::getItem('user', $name);
    if ($user == null)
    {
      $user = new User($name);
      ObjectCache::setItem('user', $name, $user);
    }
    return $user;
  }
  
  public static function getGroup($name)
  {
    $group = ObjectCache::getItem('group', $name);
    if ($group == null)
    {
      $group = new Group($name);
      if (!$group->groupExists())
        $group = null;
      ObjectCache::setItem('group', $name, $group);
    }
    return $group;
  }
  
  public static function createUser($username)
  {
    global $_STORAGE;
    
    
    $_STORAGE->queryExec("INSERT INTO User (id) VALUES ('".$_STORAGE->escape($username)."');");
  	$user = self::getUser($username);
    if (!$user->userExists())
      $user->reload();
    return $user;
  }
  
  public static function deleteUser($user)
  {
  	global $_STORAGE;
  	
    if ($user->userExists())
    {
      $_STORAGE->queryExec("DELETE FROM User WHERE id='".$_STORAGE->escape($user->getUsername())."';");
      $_STORAGE->queryExec("DELETE FROM UserAccess WHERE user='".$_STORAGE->escape($user->getUsername())."';");
    }
    $user->reload();
    return true;
  }
  
  public static function getAllUsers()
  {
    global $_STORAGE;
    
    $users=array();
    $result = $_STORAGE->query("SELECT id FROM User;");
    while ($result->valid())
    {
      $id = $result->fetch();
      $user = self::getUser($id[0]);
      $users[$user->getUsername()]=$user;
    }
    return $users;
  }
  
  public static function getAllGroups()
  {
    global $_STORAGE;
    
    $groups=array();
    $result = $_STORAGE->query("SELECT id FROM Access WHERE id<>'root';");
    while ($result->valid())
    {
      $id = $result->fetch();
      $group = self::getGroup($id[0]);
      $groups[$group->getID()]=$group;
    }
    return $groups;
  }
}

// Start up the session
session_name('SwimSession');
@session_start();


if (isset($_SESSION['Swim.User']))
{
	$GLOBALS['_USER'] = UserManager::getUser($_SESSION['Swim.User']);
  $GLOBALS['_USER']->logged=true;
}
else
{
	$GLOBALS['_USER'] = new User();
}

?>