<?php
/*
 *auther:soulence
 *date:2012-12-11
 *些文件定义了一些公用的方法
 */
defined('IN_FW') or die('deny');

/*
 * 这个用来接收参数的
 * 它可以获取多种类型   
 * 如果不需要过滤请设置$safe为false  默认是过虑
 */
function getpval($k,$default=null ,$type='', $safe=true) {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break; 
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_SERVER; break; 
		case 'S': $var = &$_SESSION; break; 
		case 'E': $var = &$_ENV; break;
		default:
		if(isset($_GET[$k])) {
			$var = &$_GET;
		} else {
			$var = &$_POST;
		}
		break;
	}
	$requestval=isset($var[$k]) ? $var[$k] : $default;
	if(empty($requestval)){
		if($requestval === 0 || $requestval === '0'){
			return 0;
		}
		return '';
	}
	return ($safe && is_string($requestval))?str_replace(array("###","%20"),array(""," "),addslashes(trim(urldecode($requestval)))) : $requestval;
}

/*
 *获取客户端的IP，如果$ num是true  那么将返回整型的IP
 *如果无效的IP地址则返回 unknown
 *注：此功能可能得到代理IP
 * @access public
 * @return string
 */
function getUserIp($num = false)
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	if (!ip2long($ip))
		return 'unknown';
	else
	{
		if ($num)
			return printf( '%u', ip2long($ip));
		else
			return $ip;
	}
}

/*
 *这个函数用于设置cookie的客户
 *如果设置cookie的成功返回TRUE，否则返回false
 *默认过期一天时间
 */
function setc($name, $value, $expire = null, $path = '/', $domain = null, $secure = false, $httponly = true)
{
	if (is_null($expire))
		$expire = 86400;
	if (is_null($domain) && isset($_SERVER['HTTP_HOST']))
		$domain = trim(str_ireplace('www.', '', $_SERVER['HTTP_HOST']));
	return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
}

/*
 *这个函数用于从客户获得的cookie
 *如果获得成功的cookie返回cookie的值否则返回false
 *注：此功能将使用用htmlspecialchars功能
 */
function getc($name)
{
	if (!isset($_COOKIE[$name]))
		return false;
	return htmlspecialchars($_COOKIE[$name]);
}

/*
 *这个函数使用从客户端删除的cookie
 *如果删除Cookie成功返回TRUE，否则返回false
 */
function delc($name)
{
	return setcookie ($name, '', time() - 3600);
}

/*
 *获取路径，如果参数为true，返回URL路径否则返回文件的真实路径默认为true
 *如果路径错误，返回false否则返回字符串
 */
function getpath($path, $p = true)
{
	if ($p)
	{
		if (!is_dir($path) && !is_file($path))
			return false;
		return str_replace(APP_PATH, SITE_URL, $path);
	}
	else
		return str_replace(SITE_URL, APP_PATH, $path);
}

/*
 *这个函数用于创建目录
 *如果成功返回TRUE，否则返回false
 *注：此功能参数所需要的绝对路径
 */
function mkdirs($dir, $need_file = false, $mode = 0700)
{
	$dir = str_replace("\\", DS, $dir);
	if (is_dir($dir))
		return true;
	$dirArr = explode(DS, $dir);
	$dirArr = array_filter($dirArr);
	if (!is_array($dirArr) || empty($dirArr))
		return true;
	$tmp = '';
	foreach ($dirArr as $k => $dir)
	{
		if (0 != ($k % 2))
			$tmp .= DS.$dir.DS;
		else
			$tmp .= $dir;
		if (!is_dir($tmp))
		{
			$ret = @mkdir($tmp, $mode);
			if (!$ret)
			{
				unset($dirArr);
				return $ret;
			}
			else
			{
				if (substr($tmp, strlen($tmp) - 1) == DS)
					$f = $tmp.'index.html';
				else
					$f = $tmp.DS.'index.html';

				if ((!file_exists($f) &&  !file_exists(RUNTIME_PATH.'build.lock')) || $need_file)
					file_put_contents($f, '');
			}
		}
	}
	unset($dirArr);
	return true;
}

/*
 *这个函数用于删除目录或文件
 *注：此功能参数所需要的绝对地址
 */
function rm($dir, $deleteRootToo = false)
{
	$dir = str_replace("\\", DS, $dir);
	if (is_file($dir) && file_exists($dir))
		return @unlink($dir);
	if (is_dir($dir))
		return unlinkRecursive($dir, $deleteRootToo);
}

/**
 * 递归删除一个目录
 * @$dir 目录的目录名
 * @$deleteRootToo 是否删除指定顶级目录 默认值false
*/
function unlinkRecursive($dir, $deleteRootToo = false)
{
	if (!$dh = @opendir($dir))
		return false;
	while (false !== ($obj = readdir($dh)))
	{
		if($obj == '.' || $obj == '..') 
			continue;
		if (!@unlink($dir . DS . $obj))
			unlinkRecursive($dir.DS.$obj, $deleteRootToo);
	}
	closedir($dh);
	if ($deleteRootToo)
		return @rmdir($dir);
	return true;
}

/*
 * 发送错误状态
 */
function send_http_status($code)
{
	static $_status = array(
		// 成功 2xx
		200 => 'OK',
		// 重定向 3xx
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily ',  // 1.1
		// 客户端发送内容错误 4xx
		400 => 'Bad Request',
		403 => 'Forbidden',
		404 => 'Not Found',
		// 服务器错误 5xx
		500 => 'Internal Server Error',
		503 => 'Service Unavailable',
	);
	if (isset($_status[$code]))
	{
		header('HTTP/1.1 '.$code.' '.$_status[$code]);
		header('Status:'.$code.' '.$_status[$code]);
	}
}

/*
 *获取配置值
 *成功返回值否则返回false
 */
function C($key)
{
	static $arr = array();

	if (!file_exists(CONFIG_FILE))
		return false;

	if (!is_array($arr) || empty($arr))
		$arr = require(CONFIG_FILE);
	$tmpArr = explode('-', $key);
	$value = false;
	foreach ($tmpArr as $t)
	{
		if ((!isset($arr[$t]) && (false === $value)) || ((false !== $value) && !isset($value[$t])))
			return false;
		if (false === $value)
			$value = $arr[$t];
		else
			$value = $value[$t];
	}
	unset($arr, $tmpArr);
	return $value;
}

/*
 * 从安全模式过滤器变量_REQUEST/$_ POST/$_GET/$_COOKIE/$_ SERVER
 * 默认打开安全模式
 */
function safe()
{
	if (!OPEN_SAFE_MODEL)
		return;
	if (is_array($_REQUEST) && !empty($_REQUEST))
	{
		foreach ($_REQUEST as $k => $v)
		{
			if(is_string($v)){
				$is_get = isset($_GET[$k]) ? true : false;
				$is_post = isset($_POST[$k]) ? true : false;
				$v = trim($v);
				unset($_REQUEST[$k], $_GET[$k], $_POST[$k]);
				$k = trim($k);
				$k = urldecode($k);
				$v = urldecode($v);

				if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false))
					die('you are too young too simple, you ip:'.getIp());
				//integer value
				if (stripos($k, 'i_') === 0)
					$v = intval($v);
				//float value
				elseif (stripos($k, 'f_') === 0)
					$v = floatval($v);
				//double value
				elseif (stripos($k, 'd_') === 0)
					$v = doubleval($v);
				//text value
				elseif (stripos($k, 't_') === 0)
					$v = trim(strip_tags($v));
				//html value
				elseif (stripos($k, 'h_') === 0)
					$v = '<pre>'.trim(htmlspecialchars($v)).'</pre>';
				if ($is_get)
					$_GET[$k] = $v;
				if ($is_post)
					$_POST[$k] = $v;
				$_REQUEST[$k] = $v;
			}
		}
	}

	if (is_array($_SERVER) && !empty($_SERVER))
	{
		foreach ($_SERVER as $k => $v)
		{
			if (is_array($v))
				continue;
			$v = trim($v);
			$k = trim($k);

			if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false))
				die('you are too young too simple, you ip:'.getIp());
		}
	}

	if (is_array($_COOKIE) && !empty($_COOKIE))
	{
		foreach ($_COOKIE as $k => $v)
		{
			$v = trim($v);
			unset($_COOKIE[$k]);
			$k = trim($k);
			$k = urldecode($k);
			$v = urldecode($v);

			if ($k != addslashes($k) || $k != strip_tags($k) || htmlspecialchars($k) != $k || (strpos($k, '%') !== false))
				die('you are too young too simple, you ip:'.getIp());
			//integer value
			if (stripos($k, 'i_') === 0)
				$v = intval($v);
			//float value
			elseif (stripos($k, 'f_') === 0)
				$v = floatval($v);
			//double value
			elseif (stripos($k, 'd_') === 0)
				$v = doubleval($v);
			//text value
			elseif (stripos($k, 't_') === 0)
				$v = trim(strip_tags($v));
			//html value
			elseif (stripos($k, 'h_') === 0)
				$v = trim(htmlspecialchars($v));
			$_COOKIE[$k] = $v;
		}
	}
}

/*
 * 这个是用来构建项目的 成功返回TRUE 反之返回 FALSE
 */
function build()
{
	if (file_exists(RUNTIME_PATH.'build.lock'))
		return true;
	if (!defined('APP_NAME') || !defined('APP_PATH'))
		return false;
	$path = str_replace("\\", DS, realpath(str_replace("\\", DS, APP_PATH)));
	$temp_path = str_replace("\\", DS, realpath(str_replace("\\", DS, dirname($_SERVER['SCRIPT_FILENAME']))));
	if (!$path)
		return false;
	$ret = true;
	if (!is_dir($path.DS.APP_NAME.DS.'Common'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Common');
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Conf'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Conf');
	if (!$ret)
		return false;
	if (!is_dir($temp_path.DS.'Static'.DS.'js'))
		$ret = mkdirs($temp_path.DS.'Static'.DS.'js');
	if (!$ret)
		return false;
	if (!is_dir($temp_path.DS.'Static'.DS.'css'))
		$ret = mkdirs($temp_path.DS.'Static'.DS.'css');
	if (!$ret)
		return false;
	if (!is_dir($temp_path.DS.'Static'.DS.'images'))
		$ret = mkdirs($temp_path.DS.'Static'.DS.'images');
	unset($temp_path);
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Lib'.DS.'Action'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Lib'.DS.'Action'.DS.'Index');
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Lib'.DS.'Model'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Lib'.DS.'Model'.DS.'Index');
	if (!$ret)
		return false;

	if (!is_dir($path.DS.APP_NAME.DS.'uploads'.DS))
		$ret = mkdirs($path.DS.APP_NAME.DS.'uploads'.DS);
	if (!$ret)
		return false;

	if (!is_dir($path.DS.APP_NAME.DS.'Lib'.DS.'Class'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Lib'.DS.'Class');
	if (!$ret)
		return false;

	if (!is_dir($path.DS.APP_NAME.DS.'Runtime'.DS.'Cache'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Runtime'.DS.'Cache',false,0777);
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Runtime'.DS.'Data'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Runtime'.DS.'Data',false,0777);
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Runtime'.DS.'Logs'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Runtime'.DS.'Logs',false,0777);
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Runtime'.DS.'Session'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Runtime'.DS.'Session',false,0777);
	if (!$ret)
		return false;
	if (!is_dir($path.DS.APP_NAME.DS.'Tpl'))
		$ret = mkdirs($path.DS.APP_NAME.DS.'Tpl'.DS.'Index'.DS.'Index');
	if (!$ret)
		return false;
	file_put_contents(RUNTIME_PATH.'build.lock', '');
	return true;
}

/*
 * 输出当前内存使用情况
 */
function echo_memory_usage($mem_usage)
{
	if ($mem_usage < 1024)
		return $mem_usage." b";
	elseif ($mem_usage < 1048576)
		return round($mem_usage/1024,2)." kb";
	else
		return round($mem_usage/1048576,2)." mb";
}

/*
 * 引入文件
 */
function import($file)
{
	if (file_exists($file))
	{
		$GLOBALS['_FileCount']++;
		compile($file);
		require_once($file);
		return true;
	}
	return false;
}

/*
 * 这个是用来生成KEY值的
 */
function getFileKey()
{
	$key = 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		foreach ($_POST as $k => $v)
			$key .= $k;
	}
	else
	{
		foreach ($_GET as $k => $v)
			$key .= $k;
	}
	$key = md5($key.$GLOBALS['_Module'].(isset($_REQUEST['act'])?ucfirst(trim($_REQUEST['act'])):'Index'));
	return $key;
}

/*
 * 生成缓存文件
 */
function compile($file)
{
	if (APP_DEBUG)
		return;
	if(CACHE_CLASS_FILE===false)
		return;
	$key = getFileKey();$mod = $GLOBALS['_Module'];
	$compiled_file2 = RUNTIME_CACHE.$mod.DS.$key.'_pre.php';
	$compiled_file = RUNTIME_CACHE.$mod.DS.$key.'_finish.php';
	if (file_exists($compiled_file) && filemtime($compiled_file) > filemtime($file))
		return;
	$extension = pathinfo($file, PATHINFO_EXTENSION);
	$content = file_get_contents($file);
	$content = str_replace("\r", '', $content);
	if ('php' == $extension)
		$content = str_replace(array("<?php\n","<?php","?>"), '', $content);
	file_put_contents($compiled_file2, $content, FILE_APPEND);
}

/*
 * 调试模式用的
 */
function debuginfo()
{
	if (!APP_DEBUG)
		return;
	echo '<div style="clear:both;text-align:center">use time: '.($GLOBALS['_endTime'] - $GLOBALS['_beginTime']).' seconds<br/>memory use: '.echo_memory_usage($GLOBALS['_endUseMems'] - $GLOBALS['_startUseMems']).'<br/>SQL Counts: '.$GLOBALS['_SQLCount'].'<br/>require file counts: '.$GLOBALS['_FileCount'].'</div>';
}

/*
 * 负载系统的功能和用户定义的函数
 */
function load_functions()
{
	if (is_dir(APP_COMMON))
	{
		if (!($dir = @opendir(APP_COMMON)))
			die('open User common function directory failed');
		while (false !== ($file = readdir($dir)))
		{
			if ($file != "." && $file != ".." && is_file(APP_COMMON.$file) && substr($file, strpos($file, '.')) == '.php')
				import(APP_COMMON.$file);
		}
		closedir($dir);
	}
}

/*
 * 注册的自动加载文件
 */
function my_autoload($classname)
{
	$mod = $GLOBALS['_Module'];
	$sys_class = FRAMEWORK_PATH.'Class'.DS.$classname.'.php';
	$user_class1 = APP_CLASS.$classname.'.php';
	$user_class2 = APP_ACTION.$mod.DS.$classname.'.php';
	$user_class3 = APP_MODEL.$mod.DS.$classname.'.php';
	if (!import($sys_class) && !import($user_class1) && !import($user_class2) && !import($user_class3))
		die('load class: '.$classname.' failed');
}

/*
 * 返回一个URL地址
 */
function U($act, $param = null, $app = null, $domain = null)
{
	if (is_null($app))
		$app = APP_NAME;
	if (is_null($domain))
		$domain = SITE_URL;
	if (stripos($domain, 'http') === false)
		$domain = 'http://'.$domain;

	$act = trim($act);
	if (strlen($act) < 1)
		return false;
	$ret = $domain.$app.'/';
	$ret .= strtoupper(substr($act, 0, 1)).substr($act, 1).'/';
	if (is_array($param) && !empty($param))
	{
		foreach ($param as $k => $v)
			$ret .= urlencode($k).'/'.urlencode($v).'/';
	}
	$ret = substr($ret, 0, -1);
	return $ret.'.'.C('template-suffix');
}

/*
 * 这个方法是用来实例化Action的  如：new UserAction()  new CityAction() 则使用 A('User')  A('City')
 * 如果是访问其它文件更里面的Action类 如: \Lib\Action\User\UserAction 则应该 A('User\\User')
 * 第二个参数表示为创建对象时是否有传初到始化参数
 */
function A($action,$params = array(),$type='Action'){
	if(empty($action) || !is_array($params))
		return false;
	static $_cache_obj = array();
	$mood = $GLOBALS['_Module'];
	$_cache_key = md5(md5($mood.$action.$type.serialize($params),true));
	$_cache_value = $_cache_obj[$_cache_key];
	if(isset($_cache_value) && is_object($_cache_value))
		return $_cache_value;
	$arr = explode('\\', $action);
	$cnt = count($arr);
	if($cnt==1 || $cnt==2){
		$class = ucfirst($action).$type;
		if($cnt==2){
			$GLOBALS['_Module'] = ucfirst($arr[0]);
			$class = ucfirst($arr[1]).$type;
		}
		if (!class_exists($class))
		{
			if (APP_DEBUG)
				die($type.' class: '.$class.' not find. ');
			else
			{
				//record log
				SL($type.' not find', '访问的'.$type.': '.$class.' class not find', '访问日志', 1);
				location();
			}
		}
		if(is_array($params) && !empty($params)){
			$params = array_values($params);$cnt = count($params);
			switch ($cnt) {
				case 1:
					$obj = new $class($params[0]);
					break;
				case 2:
					$obj = new $class($params[0],$params[1]);
					break;
				case 3:
					$obj = new $class($params[0],$params[1],$params[2]);
					break;
				case 4:
					$obj = new $class($params[0],$params[1],$params[2],$params[3]);
					break;
				case 5:
					$obj = new $class($params[0],$params[1],$params[2],$params[3],$params[4]);
					break;
				case 6:
					$obj = new $class($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
					break;
				case 7:
					$obj = new $class($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6]);
					break;
				case 8:
					$obj = new $class($params[0],$params[1],$params[2],$params[3],$params[4],$params[5],$params[6],$params[7]);
					break;							
				default:
					if (APP_DEBUG)
						die($type.' class: '.$class.' construct function arguments too much,up to eight. ');
					else
					{
						//record log
						SL($type.' arguments', '访问的'.$type.': '.$class.' construct function arguments too much,up to eight.', '访问日志', 1);
						location();
					}
					break;
			}
		}else{
			$obj = new $class();
		}
		$_cache_obj[$_cache_key] = $obj;
		if($cnt==2){
			$GLOBALS['_Module'] = $mood;
		}
		unset($arr,$action,$params,$_cache_key,$mood);
		return $obj;
	}else{
		SL($type.' format error', '设置的'.$type.': '.$action.' format error', '访问日志', 1);
		if (APP_DEBUG)
			die($action.' format error');
	}
}

/*
 * 这个方法是用来实例化Model的  如：new UserModel()  new CityModel() 则使用 M('User')  M('City')
 * 如果$model为空则会自动寻找与之对的Model
 * 如果是访问其它文件更里面的Model类 如: \Lib\Model\User\UserModel 则应该 M('User\\User')
 * 第二个参数表示为创建对象时是否有传初到始化参数
 */
function M($model='',$params=array())
{
	if(empty($model))
		$model = Application::$controller_obj->act;
	return A($model,$params,'Model');
}


/*
 * 地址跳转
 */
function location($url = SITE_URL, $time = 0, $msg = '')
{
	$url = str_replace(array("\n", "\r"), '', $url);
	if (empty($msg))
		$msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	if (!headers_sent())
	{
        // redirect
		if (0 === $time) {
			header('Location: ' . $url);
		}
		else
		{
			header("Content-type: text/html; charset=utf-8");
			header("refresh:{$time};url={$url}");
			echo($msg);
		}
		exit();
	}
	else
	{
		$str = "<meta http-equiv='Content-Type' content='text/html; charset=utf-8' /><meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if ($time != 0)
			$str .= $msg;
		exit($str);
	}
}

/*
 * 加载模板
 */
function load_tpl($tpl, $open_token = true)
{
	$tpl = trim($tpl);
	if (!file_exists($tpl))
	{
		if (APP_DEBUG)
			die('template file: '.$tpl.' not exists. ');
		else
			die('template file not exists. ');
	}
	$cache_file = RUNTIME_CACHE.$GLOBALS['_Module'].DS.md5($tpl).'.php';
	if (!file_exists($cache_file) || filemtime($cache_file) < filemtime($tpl) || $open_token)
	{
		$content = file_get_contents($tpl);
		$content = str_replace("\r", '', $content);
		$content = str_replace("\n", '', $content);
		$token_key = substr(SITE_URL, 0, -1).$_SERVER['REQUEST_URI'];
		foreach ($_REQUEST as $k => $v)
		{
			if ($k == HIDDEN_TOKEN_NAME)
				continue;
			$token_key .= $k;
		}
		$token_key = md5($token_key);
		if ($open_token)
		{
			if (!isset($_SESSION[$token_key]) || !isset($_SESSION[HIDDEN_TOKEN_NAME]) || !isset($_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]]))
			{
				$val = md5(microtime());
				if (!isset($_SESSION[HIDDEN_TOKEN_NAME]) || !isset($_REQUEST[HIDDEN_TOKEN_NAME]))
				{
					$_SESSION[HIDDEN_TOKEN_NAME] = $token_key;
				}
				$_SESSION[$token_key] = $val;
			}
			$content = preg_replace('/<form(.*?)>(.*?)<\/form>/i', '<form$1><input type="hidden" value="'.$_SESSION[$_SESSION[HIDDEN_TOKEN_NAME]].'" name="'.HIDDEN_TOKEN_NAME.'"/>$2</form>', $content);
		}

		//parse include
		/*
			如下
			<{include="Index/test"}> 这里的action是Index,对应的action的方法method是test，而且模板也是test.html
			<{include="Index/test/test_view"}> 这里的action是Index,对应的action的方法method是test，而且模板是test_view.html
		*/
			$left = C('template-left_delimiter');
			$right = C('template-right_delimiter');
			$ret = preg_match_all('/'.$left.'\s*include\s*=\s*"(.*?)"'.$right.'/i', $content, $match);
			if ($ret)
			{
				foreach ($match[1] as $k => $v)
				{
					$tArr = explode('/', $v);
					$tCount = count($tArr);
					if ($tCount == 3)
						$content = str_ireplace($match[0][$k], '<?php require_once(load_tpl(APP_TPL."'.$tArr[0].'".\'/\'."'.$tArr[2].'".\'.'.C('template-suffix').'\')); ?>', $content);
					elseif ($tCount == 2)
						$content = str_ireplace($match[0][$k], '<?php require_once(load_tpl(APP_TPL."'.$tArr[0].'".\'/\'."'.$tArr[1].'".\'.'.C('template-suffix').'\')); ?>', $content);
					unset($tArr);
				}
			}

			$content = preg_replace('/'.$left.'\s*\$(.*?)'.$right.'/i', '<?php echo \$${1}; ?>', $content);
			$content = preg_replace('/'.$left.'\s*u(.*?)'.$right.'/i', '<?php echo U${1}; ?>', $content);
			$content = preg_replace('/'.$left.'\s*if\s*(.*?)\s*'.$right.'/i', '<?php if(${1}) { ?>', $content);
			$content = preg_replace('/'.$left.'\s*else\s*if\s*(.*?)\s*'.$right.'/i', '<?php } elseif(${1}) { ?>', $content);
			$content = preg_replace('/'.$left.'\s*else\s*'.$right.'/i', '<?php } else { ?>', $content);
			$content = preg_replace('/'.$left.'\s*\/if\s*'.$right.'/i', '<?php } ?>', $content);
			$content = preg_replace('/'.$left.'\s*loop(.*?)\s*'.$right.'/i', '<?php foreach${1} { ?>', $content);
			$content = preg_replace('/'.$left.'\s*\/loop\s*'.$right.'/i', '<?php } ?>', $content);
			$content = preg_replace('/'.$left.'\s*foreach(.*?)\s*'.$right.'/i', '<?php foreach${1} { ?>', $content);
			$content = preg_replace('/'.$left.'\s*\/foreach\s*'.$right.'/i', '<?php } ?>', $content);
			$content = preg_replace('/'.$left.'\s*(.*?)'.$right.'/i', '<?php echo ${1}; ?>', $content);
			$content = compress_html($content);
			file_put_contents($cache_file, '<?php defined(\'IN_FW\') or die(\'deny\'); ?> '.$content);
		}
		return $cache_file;
	}

/*
 * 字符串去除格式
 */
function compress_html($string) {
	$string = str_replace("\r\n", '', $string);
	$string = str_replace("\n", '', $string);
	$string = str_replace("\t", '', $string);
	$pattern = array (
		"/[\s]+/",
		"/<!--[\\w\\W\r\\n]*?-->/",
		"'/\*[^*]*\*/'"
		);
	$replace = array (
		" ",
		"",
		""
		);
	return preg_replace($pattern, $replace, $string);
}

/*
 * 验证码
 */
function check_code($name)
{
	if (!isset($_SESSION['code']))
		return false;
	$s_code = $_SESSION['code'];
	unset($_SESSION['code']);
	return (strtolower(trim($_REQUEST[$name])) == $s_code);
}

/*
 * 验证字符串 有中文有没有中文的
 */
function get_word($str, $chinese = true)
{
	if ($chinese)
	{
		if (preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u', $str))
			return $str;
		else
			return false;
	}
	else
	{
		if (preg_match('/^[A-Za-z0-9_]+$/i', $str))
			return $str;
		else
			return false;
	}
}

/*
 * 验证URL 
 */
function get_link($str, $chinese = true)
{
	if ($chinese)
	{
		if (preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9_\-\:\.\%\#\@\!\&\*\+\?\,\/]+$/u', $str))
			return $str;
		else
			return false;
	}
	else
	{
		if (preg_match('/^[A-Za-z0-9_\-\:\.\%\#\@\!\&\*\+\?\,\/]+$/i', $str))
			return $str;
		else
			return false;
	}
}

/*
 * 验证数据
 */
function check_data($data, $type = 'post')
{
	if ('post' == $type)
	{
		foreach ($data as $v)
		{
			if (!isset($_POST[$v]))
				return false;
		}
	}
	else
	{
		foreach ($data as $v)
		{
			if (!isset($_GET[$v]))
				return false;
		}
	}
	return true;
}

/*
 * 注册当页面加载完毕时调用的方法
 */
function shutdown_function($req)
{
	$e = error_get_last();
	//remove some error like E_WARNING E_NOTICE and so on
	if (!is_null($e))
	{
		//错误类型如果为重要的才报出来
		if (in_array($e['type'], array(1,4, 16, 32, 64, 128, 256, 4096))){
			if (APP_DEBUG)
			{
				die('info: '.$e['message'].' , in file:'.$e['file'].' , line:'.$e['line']);
			}
			else 
			{
				header("Content-type: text/html; charset=utf-8");
				die('服务器异常，请稍后访问，或者通知服务器管理员，邮箱：'.ADMIN_EMAIL.' 谢谢合作！');
			}
		}
	}
	//here to rename compile file
	$key = getFileKey();
	$compileed_file = RUNTIME_CACHE.$GLOBALS['_Module'].DS.$key.'_pre.php';
	$compileed_file2 = RUNTIME_CACHE.$GLOBALS['_Module'].DS.$key.'_finish.php';
	if (file_exists($compileed_file))
	{
		file_put_contents($compileed_file, "<?php\n".file_get_contents($compileed_file));
		rename($compileed_file, $compileed_file2);
	}
	$GLOBALS['_endTime'] = microtime(TRUE);
	if (MEMORY_LIMIT_ON) $GLOBALS['_endUseMems'] = memory_get_usage();
	if (APP_DEBUG){
		$tem_str = bl_debug($GLOBALS);
		if(empty($tem_str))
			debuginfo();//这是最原先的调试信息  没有改版后的详细
		else
			echo $tem_str;
		unset($tem_str);
	}
}

/*
 * 删除文件
 */
function unlinkres($file)
{
	if (stripos($file, 'http') !== false)
		$file = getpath($file, false);
	if (file_exists($file) && is_file($file))
		return @unlink($file);
	return true;
}

/*
 * 调用时可以调用
 */
function vd($s, $exit = 1)
{
	echo '<pre>';
	var_dump($s);
	echo '</pre>';
	$exit && exit();
}

/*
 * 调用时可以调用
 */
function pr($s, $exit = 1)
{
	echo '<pre>';
	print_r($s);
	echo '</pre>';
	$exit && exit();
}

/**
 * 截取字符串
 *
 * @param string $string
 * @param int $length
 * @param string $dot
 * @param string $charset
 * @return string
 */
function cutstr($string, $length, $dot = ' ...', $charset = 'UTF-8')
{
	if (strlen($string) <= $length) {
		return $string;
	}
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
	$strcut = '';
	if (strtolower($charset) == 'utf-8') {
		$n = $tn = $noc = 0;
		while ($n < strlen($string)) {
			$t = ord($string[$n]);
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif (224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
	} else {
		for ($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}
	$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	return $strcut.$dot;
}

/**
 * 取消HTML代码
 *
 * @param string $string
 * @return string
 */
function shtmlspecialchars($string)
{
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

/**
 * 格式化文件大小
 *
 * @param int $size
 * @return string
 */
function sizecount($size)
{
	if ($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif ($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif ($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

/**
 * 截取字符串（支持html）
 *
 * @param string $string
 * @param int $count
 * @param string $dot
 * @param int $start
 * @param string $tags 以|分开多个html标签
 * @param float $zhfw 用来修正中英字宽参数
 * @param string $charset
 * @return string
 */
function cutStringForHtml($string, $count = 0, $dot = "...", $start = 0, $tags = "span", $zhfw = 0.9, $charset = "utf-8")
{
	//author: lael
	//blog: http://hi.baidu.com/lael80

	$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

	$zhre['utf-8'] = "/[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$zhre['gb2312'] = "/[\xb0-\xf7][\xa0-\xfe]/";
	$zhre['gbk'] = "/[\x81-\xfe][\x40-\xfe]/";
	$zhre['big5'] = "/[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

	//下面代码还可以应用到关键字加亮、加链接等，可以避免截断HTML标签发生
	//得到标签位置
	$tpos = array();
	preg_match_all("/<(" . $tags . ")([\s\S]*?)>|<\/(" . $tags . ")>/ism", $string, $match);
	$mpos = 0;
	for ($j = 0; $j < count($match[0]); $j++) {
		$mpos = strpos($string, $match[0][$j], $mpos);
		$tpos[$mpos] = $match[0][$j];
		$mpos += strlen($match[0][$j]);
	}
	ksort($tpos);

	//根据标签位置解析整个字符
	$sarr = array();
	$bpos = 0;
	$epos = 0;
	foreach ($tpos as $k => $v) {
		$temp = substr($string, $bpos, $k - $epos);
		if (!empty($temp)) {
			array_push($sarr, $temp);
		}
		array_push($sarr, $v);
		$bpos = ($k + strlen($v));
		$epos = $k + strlen($v);
	}
	$temp = substr($string, $bpos);
	if (!empty($temp)) {
		array_push($sarr, $temp);
	}

	//忽略标签截取字符串
	$bpos = $start;
	$epos = $count;
	for ($i = 0; $i < count($sarr); $i++) {
		if (preg_match("/^<([\s\S]*?)>$/i", $sarr[$i]))
			continue; //忽略标签

		preg_match_all($re[$charset], $sarr[$i], $match);

		for ($j = $bpos; $j < min($epos, count($match[0])); $j++) {
			if (preg_match($zhre[$charset], $match[0][$j]))
				$epos -= $zhfw; //计算中文字符
		}

		$sarr[$i] = "";
		for ($j = $bpos; $j < min($epos, count($match[0])); $j++) {//截取字符
			$sarr[$i] .= $match[0][$j];
		}
		$bpos -= count($match[0]);
		$bpos = max(0, $bpos);
		$epos -= count($match[0]);
		$epos = round($epos);
	}

	//返回结果
	$slice = join("", $sarr); //自己可以加个清除空html标签的东东
	if ($slice != $string) {
		return $slice . $dot;
	}
	return $slice;
}

/**
 * 写日志
 *
 * @param string $type
 * @param string $content
 * @param string $fileType 日志文件类型
 * @param string $putType  写入方式 
 */
function SL($type, $content, $fileType,$num=0, $putType = FILE_APPEND)
{
	$fileDir = RUNTIME_LOGS . date('Y-m-d') . DIRECTORY_SEPARATOR;
	if (!is_dir($fileDir)) {
		mkdir($fileDir, 0755, true);
	}
	$content = '[' . date('Y-m-d H:i:s') . ']=======fileErrorType:'.$fileType.'=======type:'.$type.'=======content:'.$content;
	if ($putType) {
		@file_put_contents($fileDir . 'SOULENCE_LOG_SOULENCE.log', $content . "\n", $putType);
	} else {
		@file_put_contents($fileDir . 'SOULENCE_LOG_SOULENCE.log', $content . "\n");
	}
}

/*
 * 改造KV数组，用于PDO传参
 */
function dbparam($r)
{
	$rNew = array();
	foreach ($r as $k => $v) {
		$rNew[":{$k}"] = $v;
	}
	return $rNew;
}

/*
 * 检查数据格式，并转化成可用的
 * $sMode里面大写的表示严格检查，在$bRemoveEmpty时过滤掉检查失败的，否则马上返回false
 */
function validone($x, $sFormat) {
	switch ($sFormat{0}) {
		case 'n': // 大于0的整数
		case 'N':
			$x = max(0, (int)$x);
			if (strtoupper($sFormat{0}) === $sFormat{0} && !$x) { return false; }
			else { return $x; }
			break;
		case 'i': // 整数
		case 'I':
			if (strtoupper($sFormat{0}) === $sFormat{0} && !is_numeric($x)) { return false; }
			else { return (int)$x; }
			break;
		case 'f': //
		case 'F':
			if (strtoupper($sFormat{0}) === $sFormat{0} && !is_numeric($x)) { return false; }
			else { return (float)$x; }
			break;
		case 'd': // YYYY-mm-dd
		case 'D':
			if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $x, $r)) { return $r[0]; }
			elseif (strtoupper($sFormat{0}) === $sFormat{0}) { return false; }
			else { return '0000-00-00'; }
			break;
		case 't': // YYYY-mm-dd HH:ii:ss日期
		case 'T':
			if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $x, $r)) { return $r[0]; }
			elseif (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/', $x, $r)) { return $r[1].':00'; }
			elseif (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $x, $r)) { return $r[1].' 00:00:00'; }
			elseif (strtoupper($sFormat{0}) === $sFormat{0}) { return false; }
			else { return '0000-00-00 00:00:00'; }
			break;
		case 'e': // 枚举类型，只支持英文半角字符
		case 'E':
			$rEnum = explode(',', trim(substr($sFormat, 1), '[]'));
			if (in_array($x, $rEnum)) { return $x; }
			elseif (strtoupper($sFormat{0}) === $sFormat{0}) { return false; }
			else { return $rEnum[0]; }
			break;
		case 's': // 任意非空白字符串
		case 'S':
			if (strtoupper($sFormat{0}) === $sFormat{0} && !$x) { return false; }
			else { return $x; }
			break;
		case 'g': // 网页提交的gbk非空白字符串
		case 'G':
			$x = trim(stripslashes($x));
			$x = iconv('GBK', 'UTF-8', $x); // charset to utf8
			if (strtoupper($sFormat{0}) === $sFormat{0} && !$x) { return false; }
			else { return $x; }
			break;
		case 'u': // 网页提交的utf8非空白字符串
		case 'U':
			$x = trim(stripslashes($x));
			$x = preg_replace_callback("/%u([0-9a-f]{3,4})/i", create_function(
				'$r', 'return html_entity_decode("&#x{$r[1]};", null, "UTF-8");'
			), $x);
			if (strtoupper($sFormat{0}) === $sFormat{0} && !$x) { return false; }
			else { return $x; }
			break;
		case 'r': // 网页提交的数组，第二个字母是G的话，从gbk转成utf8
		case 'R':
			$x = stripslashes_plus($x);
			if ('G' == strtoupper($sFormat{1})) { $x = iconv_plus($x, 'GBK', 'UTF-8'); }
			if (strtoupper($sFormat{0}) === $sFormat{0} && (!is_array($x) || !sizeof($x))) { return false; }
			else { return is_array($x) ? $x : array(); }
			break;
		default:
			return $x;
			break;
	}
	return false;
}
/* validone() 的数组用法
 */
function validate($rData, $sMode, $bRemoveEmpty=false) {
	$rVal = array();
	foreach (explode('|', $sMode) as $sField) {
		$r = explode(':', $sField);
		if (2 < sizeof($r)) { list($sKey, $sFormat, $sKeyNew) = $r; }
		else { list($sKey, $sFormat) = $r; $sKeyNew = $sKey; }
		if (!isset($rData[$sKey])) { $rData[$sKey] = false; }
		/// validone ///
		if (false !== ($x = validone($rData[$sKey], $sFormat))) {
			$rVal[$sKeyNew] = $x;
		}
		elseif ($bRemoveEmpty) {
			continue;
		}
		else {
			return false;
		}
	}
	return $rVal;
}

/* 从多个数组中得到第一个isset过的值
 * 如：isset_plus('sz,pg', $_POST, $_GET, $_SESSION)
 */
function isset_plus() {
	$rArgs = func_get_args();
	if (2 > sizeof($rArgs)) { return false; }
	$rKeys = explode(',', array_shift($rArgs));
	if (1 > sizeof($rKeys)) { return false; }
	$rRsp = array();
	foreach ($rKeys as $sKey) {
		foreach ($rArgs as $r) {
			if (!is_array($r)) { continue; }
			if (isset($r[$sKey])) {
				$rRsp[$sKey] = $r[$sKey];
				break;
			}
		}
	}
	return $rRsp;
}

/* iconv数组递归
 */
function iconv_plus($x, $sFr, $sTo) {
	if (is_string($x)) {
		return iconv($sFr, $sTo, $x);
	}
	elseif (is_array($x)) {
		$t = array();
		foreach ($x as $k => $v) {
			$t[iconv_plus($k, $sFr, $sTo)] = iconv_plus($v, $sFr, $sTo);
		}
		return $t;
	}
	else {
		return $x;
	}
}

/* stripslashes数组递归
 */
function stripslashes_plus($s) {
	if (is_array($s)) {
		$r = array();
		foreach ($s as $k => $v) { $r[stripslashes($k)] = stripslashes_plus($v); }
		return $r;
	}
	else {
		return stripslashes($s);
	}
}
