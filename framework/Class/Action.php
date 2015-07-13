<?php
defined('IN_FW') or die('deny');

class Action
{
	public $valArr = null;
	public $method = null;
	public $act = null;
	public $tpl = null;
	public $open_token = null;
	private $smarty = null;
	private static $is_smarty = null;

	public function __construct(){
		self::$is_smarty = C('template-is_smarty');
		if(self::$is_smarty === true){
			//说明使用的是smarty模板
			import(FRAMEWORK_PATH.'Class'.DS.'libs'.DS.'Smarty.class.php');
			$this->smarty = new Smarty;
			//Smarty允许有两种特殊的编译设置存在：
			//1、 任何时候都不自动重新编译(上线阶段):只有没有该文件的编译文件时才生成，模板文件或者配置文件的更改，不会引发重新编译。
			//$smarty->setCompile_check(true);//默认为true,false表示任何时候都不在文件发生变更的情况下生成编译文件，除了无编译文件。
			//$smarty->getCompile_check();//获得当前编译检查的设置
			//2、任何时候都重新编译(调试阶段)：任何时候都重新编译。
			$this->smarty->setForce_compile(APP_DEBUG);//默认为false,true表示每次都重新编译（启用缓存的话，每次都重新缓存）
			//$smarty->getForce_compile();//获得当前强制编译的设置
			$this->smarty->debugging = APP_DEBUG;
			//开启缓存
			$this->smarty->setCaching(C('template-cache'));
			//$this->smarty->getCaching();//获取当前缓存状态，默认是false关闭的
			$this->smarty->setcache_lifetime(CACHE_TIME_OUT);//设置缓存时间单位秒
			$this->smarty->left_delimiter = C('template-left_delimiter');   //左分界符，2.0属性，3.0沿用
			$this->smarty->right_delimiter = C('template-right_delimiter');
			$mod = $GLOBALS['_Module'];
			//$this->smarty->setTemplateDir(APP_TPL.'Index');
			//设置编译目录路径，不设默认"templates_c"
			$this->smarty->setCompileDir(RUNTIME_CACHE.$mod.DS);
			//设置配置目录路径，不设默认"configs"
			$this->smarty->setConfigDir(APP_PATH.APP_NAME.DS.'Conf'.DS);
			//设置新的cache缓存目录
			$this->smarty->setCacheDir(RUNTIME_DATA.$mod.DS);
			unset($mod);
		}
	}

	protected function set($name, $val)
	{
		if(self::$is_smarty === true){
			$this->smarty->assign($name,$val);
		}else{
			Application::$controller_obj->valArr[$name] = $val;
			unset($val);
		}
	}

	protected function setref($name,&$val){
		if(self::$is_smarty === true){
			$this->smarty->assignByRef($name,$val);
		}else{
			Application::$controller_obj->valArr[$name] = $val;
			unset($val);
		}
	}

	protected function success($msg = '操作成功', $code = 200, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	protected function error($msg = '操作失败', $code = 300, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	protected function timeout($msg = '操作超时', $code = 301, $navTabId = '', $rel = '', $callbackType = '', $forwardUrl = '', $confirmMsg = '')
	{
		$data = array(
			'statusCode' => $code,
			'message' => $msg,
			'navTabId' => $navTabId,
			'rel' => $rel,
			'callbackType' => $callbackType,
			'forwardUrl' => $forwardUrl,
			'confirmMsg' => $confirmMsg,
		);
		exit(json_encode($data));
	}

	protected function checktoken()
	{
		if (count($_POST) && Application::$controller_obj->open_token && count($_REQUEST) && isset($_SESSION[HIDDEN_TOKEN_NAME]) && isset($_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]]))
		{
			if (!isset($_REQUEST[HIDDEN_TOKEN_NAME]))
				return false;
			$val2 = trim($_REQUEST[HIDDEN_TOKEN_NAME]);
			if ($val2 != $_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]])
			{
				unset($_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]]);
				unset($_SESSION[HIDDEN_TOKEN_NAME]);
				return false;
			}
			unset($_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]]);
			unset($_SESSION[HIDDEN_TOKEN_NAME]);
		}
		return true;
	}

	protected function display($tpl = null)
	{
		$suffix = C('template-suffix');
		$mood = $GLOBALS['_Module'];
		if(self::$is_smarty === true){
			if(is_null($tpl)){
				if (empty($this->act)) 
					$templates = Application::$act;
				else
					$templates = $this->act;

				$temp = debug_backtrace();
			 	if(isset($temp[1]['function']) && !empty($temp[1]['function']))
			 		$tpl = $temp[1]['function'];
			 	elseif (!empty($this->method))
			 		$tpl = $this->method;
			 	else
					$tpl = Application::$mod;
		 		unset($temp);
			}else{
				$arr = explode('/', $tpl);
				$cnt = count($arr);
				if($cnt==1){//这种格式为index
					if (empty($this->act)) 
						$templates = Application::$act;
					else
						$templates = $this->act;
					$tpl = $arr[0];
				}elseif($cnt==2){//这种格式为Index/index
					$templates = $arr[0];
					$tpl = $arr[1];
				}elseif($cnt==3){//这种格式为Index/Index/index
					$mood = $arr[0];
					$templates = $arr[1];
					$tpl = $arr[2];
				}else{//否则就出错了
					SL('Template file format error', '设置的Template: '.$tpl.' format error', '访问日志', 1);
					if (APP_DEBUG)
						die('Template file format error');
				}
			}
			$this->smarty->setTemplateDir(APP_TPL.$mood.DS.$templates.DS);
			spl_autoload_unregister('my_autoload');
			$this->smarty->display($tpl.'.'.$suffix);
		}else{
			if (is_null($tpl))
				$tpl = $this->method;
			$this->tpl = $tpl;
			if (Application::$act == $this->act && Application::$mod == $this->method)
				Application::$tpl = $tpl;
			else
			{
				$GLOBALS['_FileCount']++;
				return;
			}

			$content = file_get_contents(APP_TPL.$mood.DS.Application::$controller_obj->act.DS.Application::$tpl.'.'.$suffix);
			//parse include
			$ret = preg_match_all('/'.C('template-left_delimiter').'\s*include\s*=\s*"(.*?)"'.C('template-right_delimiter').'/i', $content, $match);
			if ($ret)
			{
				foreach ($match[1] as $k => $v)
				{
					$tArr = explode('/', $v);
					$act_name = ucfirst($tArr[0].'Action');
					$act_name = new $act_name();
					$act_name->$tArr[1]();
					unset($tArr);
				}
			}

			if (is_array(Application::$controller_obj->valArr) && !empty(Application::$controller_obj->valArr))
			{
				foreach (Application::$controller_obj->valArr as $k => $v)
					$$k = $v;
				unset(Application::$controller_obj->valArr);
			}
			$GLOBALS['_FileCount']++;
			require_once(load_tpl(APP_TPL.$mood.DS.Application::$controller_obj->act.DS.Application::$tpl.'.'.$suffix, Application::$controller_obj->open_token));
		}
	}

	public function __destory(){
		if(!empty($this->$smarty))
			unset($this->$smarty);
	}
}
