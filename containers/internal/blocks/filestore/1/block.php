<?
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
	
	function getStoreDir($request)
	{
		$user=getStoreUser($request);
		return $this->prefs->getPref('block.filemanager.storage').'/'.$user;
	}
	
	function checkForDir($request)
	{
		$dir=getStoreDir($request);
		if (!is_dir($dir))
		{
			recursiveMkDir($dir);
			$user=$this->getStoreUser($request);
			$lock=lockResourceWrite($dir);
			$access=fopen($dir.'/access','w');
			fwrite($access,'DENY:DENY'."\n");
			fwrite($access,'*:group(admin),user('.$user.')::group(admin),user('.$user.'):'."\n");
			unlockResource($lock);
		}
		return $dir;
	}
	
		if (isset($request->query['user']))
		{
			if (($request->query['user']!=$_USER->getUsername())&&
					(!$_USER->inGroup('admin')))
			{
				print('You are not allowed to view this file store.');
				return true;
			}
		}
		$dir=checkForDir($request);
		$id=$parser->data['blockid'];
		$lock=lockResourceRead($dir);

		$descriptions=array();
		if (is_readable($dir.'/.descriptions'))
		{
			$desc=fopen($dir.'/.descriptions','r');
			while (!feof($desc))
			{
				$line=trim(fgets($desc));
				$p=strpos($line,'=');
				if ($p>0)
				{
					$descriptions[substr($line,0,$p)]=substr($line,$p+1);
				}
			}
		}

		if (isset($request->query[$id.':upload']))
		{
			$file=$_FILES[$id.':file'];
			if (($file['error']==UPLOAD_ERR_OK)&&(is_uploaded_file($file['tmp_name'])))
			{
				if (!is_readable($dir.'/'.$file['name']))
				{
					unlockResource($lock);
					$lock=lockResourceWrite($dir);
					move_uploaded_file($file['tmp_name'],$dir.'/'.$file['name']);
					if (isset($request->query[$id.':description']))
					{
						$descriptions[$file['name']]=$request->query[$id.':description'];
						$desc=fopen($dir.'/.descriptions','w');
						foreach ($descriptions as $name => $description)
						{
							fwrite($desc,$name.'='.$description."\n");
						}
						fclose($desc);
					}
?><p class="info">File <?= $file['name'] ?> was uploaded.</p><?
				}
				else
				{
?><p class="warning">Upload failed because a file of that name already exists.</p><?
				}
			}
			else
			{
				if ($file['error']==UPLOAD_ERR_INI_SIZE)
				{
?><p class="warning">File was too large to be uploaded.</p><?
				}
				else
				{
?><p class="warning">File upload failed due to a server misconfiguration (error <?= $file['error'] ?>).</p><?
				}
			}
		}
		else if (isset($request->query[$id.':delete']))
		{
			$file=$request->query[$id.':file'];
			if (is_readable($dir.'/'.$file))
			{
				unlockResource($lock);
				$lock=lockResourceWrite($dir);
				unlink($dir.'/'.$file);
				if (isset($descriptions[$file]))
				{
					unset($descriptions[$file]);
					$desc=fopen($dir.'/.descriptions','w');
					foreach ($descriptions as $name => $description)
					{
						fwrite($desc,$name.'='.$description."\n");
					}
					fclose($desc);
				}
?><p class="info">File <?= $file ?> was deleted.</p><?
			}
		}
		
		$list=opendir($dir);
		$count=0;
		$lockfiles=getLockFiles();
		while (($file=readdir($list))!==false)
		{
			if (($file[0]!='.')&&($file!='access')&&(!in_array($file,$lockfiles)))
			{
				if ($count==0)
				{
?>
<table style="width: 100%">
<tr>
<th style="width: 20%; text-align: left">Filename</th>
<th style="width: 50%; text-align: left">Description</th>
<th style="width: 15%; text-align: left">Size</th>
<th style="width: 15%; text-align: left">Options</th>
</tr>
<?
				}
				$count++;
				if (isset($descriptions[$file]))
				{
					$description=$descriptions[$file];
				}
				else
				{
					$description='No description';
				}
				$size=getReadableFileSize($dir.'/'.$file);
				$delete = new Request();
				$delete->resource=$request->resource;
				$delete->method=$request->method;
				if (isset($request->query['user']))
				{
					$delete->query['user'];
				}
				$delete->query[$id.':delete']='true';
				$delete->query[$id.':file']=$file;
?>
<tr>
<td><anchor href="/global/file/extranet/<?= $this->getStoreUser($request) ?>/<?= $file ?>"><?= $file ?></anchor></td>
<td><?= $description ?></td>
<td><?= $size ?></td>
<td><a href="<?= $delete->encode() ?>">Delete</a></td>
</tr>
<?
			}
		}
		if ($count==0)
		{
?>
No files stored.
<?
		}
		else
		{
?>
</table>
<?
		}
		unlockResource($lock);
		$upload = new Request();
		$upload->resource=$request->resource;
		$upload->method=$request->method;
		if (isset($request->query['user']))
		{
			$upload->query['user'];
		}
?>
<form method="POST" action="<?= $upload->encodePath() ?>" enctype="multipart/form-data">
<?= $upload->getFormVars() ?>
<p>Upload a file: <input type="file" name="<?= $id ?>:file"> Description: <input type="text" name="<?= $id ?>:description"> <input type="submit" name="<?= $id ?>:upload" value="Upload"></p>
</form>
<?
		return true;
?>