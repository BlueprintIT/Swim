<?

/*
 * Swim
 *
 * Defines a block that allows file management of a given directory.
 *
 * Copyright Blueprint IT Ltd. 2006
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class UserFileManagerBlock extends FileManagerBlock
{
	function UserFileManagerBlock($container,$id,$version)
	{
		$this->FileManagerBlock($container,$id,$version);
	}

	function getStoreUser($request)
	{
		global $_USER;
		
		if (isset($request->query['user']))
		{
			return $request->query['user'];
		}
		else
		{
			return $_USER->getUsername();
		}
	}
	
	function getStoreResource($request)
	{
		$user=$this->getStoreUser($request);
		return Resource::decodeResource($this->prefs->getPref('block.filemanager.storage').'/'.$user);
	}
	
	function onDirCreated($request,$dir)
	{
		$file=$dir->getSubfile("access");
		$user=$this->getStoreUser($request);
		$access=$file->openFileWrite();
		fwrite($access,'INHERIT:INHERIT'."\n");
		fwrite($access,'*:user('.$user.')::user('.$user.'):'."\n");
		$file->closeFile($access);
	}
	
}

?>