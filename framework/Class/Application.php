<?php
defined('IN_FW') or die('deny');

class Application
{
	public static $act = null;
	public static $mod = null;
	public static $tpl = null;
	public static $controller_obj = null;

	public static function run()
	{
		self::init();
		self::session();
		self::parseurl();
	}

	private static function session()
	{
		//缓存保存类型
		if(CACHE_TYPE == 'm' && extension_loaded('memcache')){
			ini_set('session.save_handler', 'memcache');
			ini_set('session.save_path', 'tcp://'.C('memcache-host').':'.C('memcache-port'));
		}
		else{
			ini_set('session.save_handler', 'file');
			ini_set('session.save_path', RUNTIME_SESS);
		}
		$s_timeout = (int)C('session-timeout');
		if($s_timeout > 0){
			//设置SESSION的过期时间
			session_cache_expire($s_timeout);
			session_set_cookie_params($s_timeout*60);
		}
		session_id($_COOKIE[session_name()]);
		session_start();
		setcookie(session_name(), session_id(), $s_timeout*60 + time(), '/');
		unset($s_timeout);
	}

	private static function init()
	{
		defined('LOAD_COMMON') or define('LOAD_COMMON', false);
		if(LOAD_COMMON === false)
			load_functions();
		if(!build())
			die('build directories error');
		$mood = $GLOBALS['_Module'];
		if(!is_dir(RUNTIME_CACHE.$mood)){
			mkdirs(RUNTIME_CACHE.$mood,false,0777);
			file_put_contents(RUNTIME_CACHE.$mood.DS.'index.html', '');
		}
		if(CACHE_TIME_OUT >=10 || (C('template-cache') === true && C('template-is_smarty') === true)){
			if(!is_dir(RUNTIME_DATA.$mood)){
				mkdirs(RUNTIME_DATA.$mood,false,0777);
				file_put_contents(RUNTIME_DATA.$mood.DS.'index.html', '');
			}
		}
		unset($mood);
		safe();
	}

	private static function parseurl()
	{
		if(isset($_REQUEST['act']) && !empty($_REQUEST['act']))
			$act = ucfirst(trim($_REQUEST['act']).'Action');
		else
			$act = 'IndexAction';

		if(isset($_REQUEST['mod']) && !empty($_REQUEST['mod']))
			$method = trim($_REQUEST['mod']);
		else
			$method = 'Index';

		if(CREATE_DEMO){
			//if need create demo
			$demo = new Demo();
			$demo->run();
		}

		if(!class_exists($act)){
			if (APP_DEBUG)
				die('controller class: '.$act.' not find. ');
			else
			{
				//record log
				SL('controller not find', '访问的controller: '.$act.' class not find', '访问日志', 1);
				location();
			}
		}
		self::$controller_obj = &$controller;
		self::$act = str_ireplace('Action', '', $act);
		self::$mod = $method;
		$controller = new $act();
		if (!method_exists($controller, $method)){
			if (APP_DEBUG)
				die('controller class: '.$act.', method: '.$method.' not find. ');
			else{
				//record log
				SL('method not find', '访问的controller: '.$act.' class method: '.$method.' not find', '访问日志', 1);
				location();
			}
		}
		$controller->method = $method;
		$controller->open_token = OPEN_TOKEN;
		$controller->act = self::$act;
		$controller->$method();
	}
}
