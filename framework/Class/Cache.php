<?php
defined('IN_FW') or die('deny');

// memcache / file cache
class Cache
{
	private $conf = null;
	private static $_instance = null;
	private  $_type = 'f';
	private  $con = null;

	private function __clone()
	{
		die('Clone is not allow!');
	}

	private function __construct($type = 'f', $conf = null)
	{
		$this->_type = $type;
		if ($type == 'f') return true;

		if ($type == 'm' && !extension_loaded('memcache'))
			die('open memcache extension first');

		$this->conf = array(
			'host' => C('memcache-host'),
			'port' => C('memcache-port'),
			'timeout' => C('memcache-timeout'),
		);

		if (is_array($conf) && !empty($conf))
		{
			foreach ($conf as $k => $v)
			{
				if (!is_scalar($v) || !isset($this->conf[$k]))
				{
					unset($conf[$k]);
					continue;
				}
				$this->conf[$k] = $v;
			}
		}
	}

	public static function getInstance($type = 'f', $conf = null)
	{
		if (!(self::$_instance instanceof self))
			self::$_instance = new self($type, $conf);
		return self::$_instance;
	}

	public function connect()
	{
		if ($this->_type == 'f') return true;
		if (!is_null($this->con))
			return $this->con;
		$this->con = new Memcache;
		$ret = $this->con->connect($this->conf['host'], $this->conf['port'], $this->conf['timeout']);
		if (APP_DEBUG && !$ret)
			var_dump($this->conf);
		if (!$ret)
			die('connect to memcache error');
	}


	public function clear()
	{
		if ($this->_type == 'f') return true;
		if ($this->_type == 'm')
		{
			if (is_null($this->con))
				$this->connect();
			$this->con->flush();
		}
	}

	//get success return value else return false
	public function get($key)
	{
		if ($this->_type == 'f')
		{
			$file = RUNTIME_DATA.$GLOBALS['_Module'].DS.md5($key);
			if (file_exists($file) && filemtime($file) < time())
			{
				@unlink($file);
				return false;
			}
			if (file_exists($file) && filemtime($file) >= time())
			{
				return @unserialize(file_get_contents($file));
			}
		}
		elseif ($this->_type == 'm')
		{
			if (is_null($this->con))
				$this->connect();
			$ret = $this->con->get($key, 1);
			if (is_string($ret))
				return @unserialize($ret);
			else
				return @$ret;
		}
	}

	public function rm($key)
	{
		if ($this->_type == 'f')
		{
			$file = RUNTIME_DATA.$GLOBALS['_Module'].DS.md5($key);
			if (file_exists($file))
			{
				@unlink($file);
				return true;
			}
		}
		elseif ($this->_type == 'm')
		{
			if (is_null($this->con))
				$this->connect();
			$ret = $this->con->delete($key);
			return $ret;
		}
	}

	public function set($key, $val, $expire = 1200)
	{
		if ($this->_type == 'f')
		{
			$file = RUNTIME_DATA.$GLOBALS['_Module'].DS.md5($key);
			$ret = file_put_contents($file, @serialize($val));
			touch($file, time() + $expire);
			return $ret;
		}
		elseif ($this->_type == 'm')
		{
			if (is_null($this->con))
				$this->connect();
			$ret = $this->con->set($key, @serialize($val), 1, $expire);
			return $ret;
		}
	}

	public function free()
	{
		if ($this->_type == 'f')
			return true;
		if (!is_null($this->con))
			$this->con->close();
	}
}
