<?php
defined('IN_FW') or die('deny');

/*
 *	这里使用的是 utf8, gbk需要自己修改
 */
class Mysql
{
	private $conf = null;
	private $pdo = null;
	private $statement = null;
	private $lastInsID = null;
	private static $_instance = null;

	private function __clone()
	{
		die('Clone is not allow!');
	}

	private function __construct($conf = null)
	{
		if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql'))
			die('open PDO and pdo_mysql first');

		if (version_compare(PHP_VERSION, '5.3.9', '<') && !in_array(PHP_OS, array('WINNT', 'WIN32', 'Windows', '')))
			die('to be safe, PDO need PHP_VERSION > 5.3.6 and PHP_VERSION 5.3.8 has hash bug, so need PHP_VERSION >= 5.3.9, you php version:'.PHP_VERSION);

		$this->conf = array(
			'dsn' => C('db-dsn'),
			'un' => C('db-un'),
			'pw' => C('db-pw'),
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

	public static function getInstance($conf = null)
	{
		if (!(self::$_instance instanceof self))
			self::$_instance = new self($conf);
		return self::$_instance;
	}

	public function connect()
	{
		if (!is_null($this->pdo))
			return $this->pdo;
		try {
			$this->pdo = new PDO($this->conf['dsn'], $this->conf['un'], $this->conf['pw'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_EMULATE_PREPARES => false));
		} catch (PDOException $e) {
			if (APP_DEBUG)
				throw new Exception($e->getMessage()); 
			die('new PDO  class error');
		}
	}


	public function free()
	{
		if (!empty($this->statement))
		{
			$this->statement->closeCursor();
			$this->statement = null;
		}
	}

	/**
	 * 这个单个SQL语句查询的方法
	 */
	public function query($sql, $data = array(), $one = false, $cache_type = CACHE_TYPE, $timeout = CACHE_TIME_OUT)
	{
		if (!is_array($data))
			return false;

		if ($cache_type == 'm' && !extension_loaded('memcache'))
			$cache_type = 'f';

		if (is_null($this->pdo))
			$this->connect();
		$this->free();

		$GLOBALS['_SQLCount']++;

		$res = $this->PdoExec($data,$sql);

		if($res === false)
			return false;

		$key = 'a'.md5($sql.serialize($data));
		
		if (APP_NAME == 'admin'){
			if ($one){
				return $this->statement->fetch(PDO::FETCH_ASSOC);
			}else{
				return $this->statement->fetchAll(PDO::FETCH_ASSOC);
			}
		}
		else
		{
			if($timeout >= 10){
				$cache = Cache::getInstance($cache_type, array('timeout' => $timeout));
				$ret = $cache->get($key);
				if (!empty($ret))
				{
					$GLOBALS['_SQLCount']--;
					return $ret; 
				}
			}
			if ($one){
				$val = $this->statement->fetch(PDO::FETCH_ASSOC);
			}else{
				$val = $this->statement->fetchAll(PDO::FETCH_ASSOC);
			}
			if($timeout >= 10){
				$cache->set($key, $val, $timeout);
				unset($cache,$key);
			}
			return $val;
		}
	}

	/**
	 * 这个多个SQL语句查询的方法
	 */
	public function queryes($arr_sql, $data = array(), $one = false, $cache_type = CACHE_TYPE, $timeout = CACHE_TIME_OUT){
		if (!is_array($data) || !is_array($arr_sql))
			return false;
		$res = array();
		for ($i=0,$n=count($arr_sql);$i<$n;$i++) {
			$res[] = $this->query($arr_sql[$i],(isset($data[$i])&&is_array($data[$i]))?$data[$i]:array(),$one,$cache_type,$timeout);
		}
		return $res;
	}

	/**
     * 分页封装 
     *
     * @param string $sql
     * @param int $page  表示从第几页开始取
     * @param int $pageSize 表示每页多少条
     */
	public function limitQuery($sql, $page=0, $pageSize=20, $data = array(), $cache_type = CACHE_TYPE, $timeout = CACHE_TIME_OUT)
    {
        $page = intval($page);
        if ($page < 0) {
            return array();
        }
        $pageSize = intval($pageSize);
        if ($pageSize > 0) { // pageSize 为0时表示取所有数据
            $sql .= ' LIMIT ' . $pageSize;
            if ($page > 0) {
				$start_limit = ($page - 1) * $pageSize;
                $sql .= ' OFFSET ' . $start_limit;
            }
        }
        return $this->query($sql, $data,false,$cache_type,$timeout);
    }

	private function PdoExec($data,$sql){
		$this->statement = $this->pdo->prepare($sql);
		if (false === $this->statement)
			return false;
		if (!empty($data))
		{
			foreach ($data as $k => $v)
			{
				$this->statement->bindValue($k, $v);
			}
		}
		$res = $this->statement->execute();
		if (!$res)
		{
			return $this->ExceError($sql);
		}else{
			return $res;
		}
	}

	private function ExceError($sql){
		if (APP_DEBUG)
		{
			echo '<pre>';
			print_r($this->statement->errorInfo());
			echo '</pre>';
			throw new Exception('sql:'.$sql);
			die('execute sql error');
		}
		else
		{
			$arr = $this->statement->errorInfo();
			//record log
			SL('sql error', '执行sql: '.$sql.' 发生错误，错误信息: '.$arr[2], 'sql执行错误', 1);
			return false;
		}
	}

	/**
	 * 这个是用来进行添加 删除  修改操作  使用事务操作
	 */
	public function DDLExecute($sql, $data = array()){
		if (!is_array($data))
			return false;

		if (is_null($this->pdo))
			$this->connect();
		$this->free();
		$this->pdo->beginTransaction();//开启事务
		try{
			$this->execRes($data,$sql);
			$this->pdo->commit();//事务提交
			return $this->lastInsID;
		} catch (Exception $e) {
			SL('sql error', '执行sql: '.$sql.' 发生错误，错误信息: '.$e->getMessage(). 'sql执行错误', 1);
			$this->pdo->rollBack();//事务回滚
			return false;
		} 
	}

	/**
	 * 这个是用来进行添加 删除  修改操作  使用事务操作 它是执行多条的
	 */
	public function DDLExecutees($arr_sql, $data = array()){
		if (!is_array($data) && !is_array($arr_sql))
			return false;
		$res = array();
		if (is_null($this->pdo))
			$this->connect();
		$this->free();
		$this->pdo->beginTransaction();//开启事务
		try{
			for ($i=0,$n=count($arr_sql);$i<$n;$i++) {
				if(!isset($data[$i])){
					$data[$i] = array();
				}
				if(!is_array($data[$i])){
					SL('sql error', '执行sql: '.$arr_sql[$i].' 发生错误，错误信息:传的参数不是数组 sql执行错误', 1);
					$this->pdo->rollBack();//事务回滚
					return false;
				}
				$this->execRes($data[$i],$arr_sql[$i]);
				$res[] = $this->lastInsID;
			}
			$this->pdo->commit();//事务提交
			return $res;
		} catch (Exception $e) {
			SL('sql error', '执行sql: '.$sql.' 发生错误，错误信息: '.$e->getMessage(). 'sql执行错误', 1);
			$this->pdo->rollBack();//事务回滚
			return false;
		} 
		return $res;
	}

	/**
	 * 这个方法是用来计算条数的
	 */
	public function CountRows($sql,$data = array(),$cache_type = CACHE_TYPE,$timeout = CACHE_TIME_OUT){
		if (!is_array($data))
			return false;

		if (is_null($this->pdo))
			$this->connect();
		$this->free();
		$res = $this->PdoExec($data,$sql);
		if($res == false)
			return false;

		$GLOBALS['_SQLCount']++;
		if (APP_NAME == 'admin'){
			return $this->statement->fetchColumn();
		}else
		{
			if($timeout >= 10){
				$key = 'a'.md5($sql.serialize($data));
				$cache = Cache::getInstance($cache_type, array('timeout' => $timeout));
				$ret = $cache->get($key);
				if (!empty($ret))
				{
					$GLOBALS['_SQLCount']--;
					return $ret; 
				}
			}
			$val = $this->statement->fetchColumn();
			if($timeout >= 10){
				$cache->set($key, $val, $timeout);
				unset($cache,$key);
			}
			return $val;
		}
	}

	/**
	 * 这个是用来执行计算条数的 它是执行多条的
	 */
	public function CountRowses($arr_sql, $data = array(), $cache_type = CACHE_TYPE, $timeout = CACHE_TIME_OUT){
		if (!is_array($data) && !is_array($arr_sql))
			return false;
		$res = array();
		for ($i=0,$n=count($arr_sql);$i<$n;$i++) {
			$res[] = $this->CountRows($arr_sql[$i],(isset($data[$i])&&is_array($data[$i]))?$data[$i]:array(),$cache_type,$timeout);
		}
		return $res;
	}

	private function execRes($data,$sql){
		$res = $this->PdoExec($data,$sql);

		$GLOBALS['_SQLCount']++;
		
		$in_id = $this->getLastInsertId();

		if (preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $sql) && !empty($in_id))
			$this->lastInsID = $in_id;
		else
			$this->lastInsID = $res;
	}

	public function getLastInsertId()
	{
		if (is_null($this->pdo))
			$this->connect();
		return $this->pdo->lastInsertId();
	}

	public function __destory(){
		$this->free();
		$this->pdo = null;
	}
}