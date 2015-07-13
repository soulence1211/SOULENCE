<?php
defined('IN_FW') or die('deny');

class Upload 
{
	private $type = 'images';
	private $ext = array('jpg', 'gif', 'png');
	private $name = 'file';
	private $dir = null;

	public function __construct($type = 'images', $extArr = array('jpg', 'gif', 'png'))
	{
		$this->dir = $type.DS.date('Ymd').DS;
		$this->type = $type;
		$this->ext = $extArr;
		if (!is_dir(APP_UPLOAD.$this->dir)){
			mkdirs(APP_UPLOAD.$this->dir, true);
		}
	}

	public function upload($name = 'file')
	{
		$this->name = $name;
		if (!is_array($_FILES) || empty($_FILES) || !isset($_FILES[$this->name]))
			return json_encode(array('code' => '-1', 'msg' => 'no upload file find'));

		$code = 0;
		$msg = 'upload success';
		if ($_FILES[$name]['error'] != 0)
		{
			$code = $_FILES[$name]['error'];
			switch ($_FILES[$name]['error'])
			{
			case 1:
			case 2:
				$msg = 'upload file size not allow';
				break;
			case 3:
				$msg = 'File upload only partially';
				break;
			case 4:
				$msg = 'No file was uploaded';
				break;
			case 5:
				$msg = 'Upload file size is 0';
				break;
			default:
				$msg = 'Unknown error';
				break;
			}
		}
		if ($code != 0)
			return json_encode(array('code' => $code, 'msg' => $msg));
		if (!is_uploaded_file($_FILES[$name]['tmp_name']))
			return json_encode(array('code' => -2, 'msg' => 'this file not uploaded file'));
		if (!in_array(substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.') + 1), $this->ext))
			return json_encode(array('code' => -2, 'msg' => 'this file extension not allow'));
		$file = md5(microtime()).substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.'));
		$ret = move_uploaded_file($_FILES[$name]['tmp_name'], APP_UPLOAD.$this->dir.$file);
		if (!$ret){
			return json_encode(array('code' => -3, 'msg' => 'move uploaded file failed'));
		}else{
			return json_encode(array('code' => 0, 'msg' => 'upload success', 'file' => $this->dir.$file));
		}
	}
}
