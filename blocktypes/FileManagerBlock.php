<?

/*
 * Swim
 *
 * Defines a block that allows file management of a given directory.
 *
 * Copyright Blueprint IT Ltd. 2005
 *
 * $HeadURL$
 * $LastChangedBy$
 * $Date$
 * $Revision$
 */

class FileManagerBlock extends Block
{
	function FileManagerBlock($container,$id,$version)
	{
		$this->Block($container,$id,$version);
	}

	function getStoreResource($request)
	{
		return Resource::decodeResource($request);
	}
	
	function onDirCreated($request,$dir)
	{
	}
	
	function checkForDir($request)
	{
		$resource=$this->getStoreResource($request);
		if ($resource!==null)
		{
			if (!$resource->exists())
			{
				$resource->mkDir();
				$this->onDirCreated($request,$resource);
			}
		}
		return $resource;
	}
	
	function displayTableHeader()
	{
?>
<th style="width: 20%; text-align: left">Filename</th>
<th style="width: 50%; text-align: left">Description</th>
<th style="width: 15%; text-align: left">Size</th>
<th style="width: 15%; text-align: left">Options</th>
<?
	}
	
	function displayTableFooter()
	{
	}
	
	function displayFileDetails($request,$resource,$description,$delete)
	{
		$size=getReadableFileSize($resource->getDir().'/'.$resource->id);
?>
<td><anchor target="_blank" method="view" href="/<?= $resource->getPath() ?>"><?= basename($resource->id) ?></anchor></td>
<td><?= $description ?></td>
<td><?= $size ?></td>
<td><a href="<?= $delete->encode() ?>">Delete</a></td>
<?
	}
	
	// TODO do something a bit cleverer here.
	function getModifiedDate()
	{
		return time();
	}
	
	function displayFormHeader()
	{
	}
	
	function displayAdminPanel($request,$data,$attrs)
	{
	}
	
	function displayContent($parser,$attrs,$text)
	{
		global $_USER;

		$request=$parser->data['request'];
		$resource=$this->checkForDir($request);
		if (!$_USER->canRead($resource))
		{
			print('You are not allowed to view this file store.');
			return true;
		}
		$dir=$resource->getDir().'/'.$resource->id;
		$id=$parser->data['blockid'];
		$resource->lockRead();

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

		if ($_USER->canWrite($resource))
		{
			if (isset($request->query[$id.':upload']))
			{
				$file=$_FILES[$id.':file'];
				if (($file['error']==UPLOAD_ERR_OK)&&(is_uploaded_file($file['tmp_name'])))
				{
					if (!is_readable($dir.'/'.$file['name']))
					{
						$resource->lockWrite();
						$resource->unlock();
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
							unset($request->query[$id.':description']);
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
				unset($request->query[$id.':upload']);
			}
			else if (isset($request->query[$id.':delete']))
			{
				$file=$request->query[$id.':file'];
				if (is_readable($dir.'/'.$file))
				{
					$resource->lockWrite();
					$resource->unlock();
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
			unset($request->query[$id.':file']);
			unset($request->query[$id.':delete']);
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
<?
					$this->displayTableHeader();
?>
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
				$fileresource=$resource->getSubfile($file);
				$delete = new Request();
				$delete=$request;
				$delete->query[$id.':delete']='true';
				$delete->query[$id.':file']=$file;
?>
<tr>
<?
				$this->displayFileDetails($request,$fileresource,$description,$delete);
?>
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
			$this->displayTableFooter();
?>
</table>
<?
		}
		$resource->unlock();
		if ($_USER->canWrite($resource))
		{
			$upload=$request;
?>
<table align="center">
<form method="POST" action="<?= $upload->encodePath() ?>" enctype="multipart/form-data">
<?= $upload->getFormVars() ?>
<? $this->displayFormHeader($id); ?>
<tr><td><label for="file">Upload a file:</label></td><td><input type="file" id="file" name="<?= $id ?>:file"></td></tr>
<tr><td><label for="description">Description:</label></td><td><input type="text" id="description" name="<?= $id ?>:description"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="<?= $id ?>:upload" value="Upload"></td></tr>
</form>
</table>
<?
		}
		return true;
	}
}

?>
