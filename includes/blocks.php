<?

class Block
{
	var $_dir;
	var $_branch;
	
	function Block($dir,$pref)
	{
		$this->_dir=$dir;
		$this->_branch=$pref;
	}
	
	function getPref($name,$default="")
	{
		return getPref($this->_branch.".".$name,$default);
	}
	
	function display($attrs,$text)
	{
	}
}

?>