<?php
defined('IN_FW') or die('deny');
date_default_timezone_set('PRC');
//Soulence framework create time 2013-03-28 by soulence
if(defined('APP_DEBUG') && APP_DEBUG===true){
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 'On');
}
else{
	ini_set('display_errors', 'Off');
	error_reporting(0);
}

//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(TRUE);
$GLOBALS['_SQLCount'] = 0;
$GLOBALS['_FileCount'] = 1;
$GLOBALS['_Module'] = (isset($_REQUEST['mkd']) && !empty($_REQUEST['mkd']))?ucfirst(trim($_REQUEST['mkd'])):'Index';

//这里需要看一下是否有定义项目名称
if (!defined('APP_NAME') && isset($_REQUEST['app']) && !empty($_REQUEST['app']))
	define('APP_NAME', trim($_REQUEST['app']));
// 记录内存初始使用
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if (MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();

//////////////////////////////////////////////用户应用常量的定义开始，建议不要修改常量这里//////////////////////
if (!defined('SITE_URL'))
{
	$host = trim($_SERVER['HTTP_HOST']);
	$script_name = explode('/', substr(trim($_SERVER['SCRIPT_NAME']),1));
	array_pop($script_name);$str_url = '';
	if(!empty($script_name))
		$str_url = implode('/', $script_name).'/';
	if (count(explode('.', $host)) > 2)
		define('SITE_URL', 'http://'.$host.'/'.$str_url);
	else
		define('SITE_URL', 'http://www.'.$host.'/'.$str_url);
	unset($host,$script_name,$str_url);
}

defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).DS);
defined('APP_NAME') or define('APP_NAME', 'App');
defined('APP_COMMON') or define('APP_COMMON', APP_PATH.APP_NAME.DS.'Common'.DS);
defined('APP_LIB') or define('APP_LIB', APP_PATH.APP_NAME.DS.'Lib'.DS);
defined('APP_ACTION') or define('APP_ACTION', APP_PATH.APP_NAME.DS.'Lib'.DS.'Action'.DS);
defined('APP_CLASS') or define('APP_CLASS', APP_PATH.APP_NAME.DS.'Lib'.DS.'Class'.DS);
defined('APP_MODEL') or define('APP_MODEL', APP_PATH.APP_NAME.DS.'Lib'.DS.'Model'.DS);
defined('APP_TPL') or define('APP_TPL', APP_PATH.APP_NAME.DS.'Tpl'.DS);
defined('APP_UPLOAD') or define('APP_UPLOAD', APP_PATH.APP_NAME.DS.'uploads'.DS);
defined('APP_CSS') or define('APP_CSS', SITE_URL.'Static/css/');
defined('APP_IMAGE') or define('APP_IMAGE', SITE_URL.'Static/images/');
defined('APP_JS') or define('APP_JS', SITE_URL.'Static/js/');
defined('RUNTIME_PATH') or define('RUNTIME_PATH', APP_PATH.APP_NAME.DS.'Runtime'.DS);
defined('RUNTIME_CACHE') or define('RUNTIME_CACHE', APP_PATH.APP_NAME.DS.'Runtime'.DS.'Cache'.DS);
defined('RUNTIME_DATA') or define('RUNTIME_DATA', APP_PATH.APP_NAME.DS.'Runtime'.DS.'Data'.DS);
defined('RUNTIME_LOGS') or define('RUNTIME_LOGS', APP_PATH.APP_NAME.DS.'Runtime'.DS.'Logs'.DS);
defined('RUNTIME_SESS') or define('RUNTIME_SESS', APP_PATH.APP_NAME.DS.'Runtime'.DS.'Session'.DS);
defined('CONFIG_FILE') or define('CONFIG_FILE', FRAMEWORK_PATH.'config.php');
defined('FONTS_PATH') or define('FONTS_PATH', FRAMEWORK_PATH.'fonts'.DS);
defined('ADMIN_EMAIL') or define('ADMIN_EMAIL', 'soulence@126.com');
defined('CREATE_DEMO') or define('CREATE_DEMO', true);
defined('OPEN_TOKEN') or define('OPEN_TOKEN', true);
defined('CACHE_TYPE') or define('CACHE_TYPE','m');
defined('CACHE_TIME_OUT') or define('CACHE_TIME_OUT',3600);
defined('CACHE_CLASS_FILE') or define('CACHE_CLASS_FILE',true);
defined('HIDDEN_TOKEN_NAME') or define('HIDDEN_TOKEN_NAME', 'token_name');
//////////////////////////////////////////////用户应用常量的定义结束//////////////////////

/////////////////////////////////////////////Soulence framework 常量的定义开始，建议不要修改常量这里///////////////////////////
defined('FRAMEWORK_PATH') or define('FRAMEWORK_PATH', dirname(str_replace("\\", DS, __FILE__)).DS);
defined('SYS_COMMON_PATH') or define('SYS_COMMON_PATH', FRAMEWORK_PATH.'Common'.DS);
defined('SYS_CLASS_PATH') or define('SYS_CLASS_PATH', FRAMEWORK_PATH.'Class'.DS);
/////////////////////////////////////////////framework 常量的定义结束///////////////////////////

//////////////////////////////////////////////一些安全方面常量的定义开始，建议不要修改常量这里//////////////////////////
//是否开启安全模式，默认开启
defined('OPEN_SAFE_MODEL') or define('OPEN_SAFE_MODEL', true);
// 是否调试模式
defined('APP_DEBUG') or define('APP_DEBUG', false);
//////////////////////////////////////////////一些安全方面常量的定义结束//////////////////////////

require_once(SYS_COMMON_PATH.'functions.php');

//是否开启调试信息
if(APP_DEBUG===true){
    import(SYS_CLASS_PATH.'Phpdebug.php');
}

//文件缓存
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

$compiled_file = RUNTIME_CACHE.$GLOBALS['_Module'].DS.$key.'_finish.php';
if (file_exists($compiled_file) && !APP_DEBUG)
{
	define('LOAD_COMMON', true);
	require_once($compiled_file);
}

if (!spl_autoload_register('my_autoload'))
	die('register auto load class function failed');

register_shutdown_function('shutdown_function', $_REQUEST);
///////////////////////////////////execute core class start//////////////////////
Application::run();
///////////////////////////////////execute core class end//////////////////////
