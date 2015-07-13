<?php
defined('IN_FW') or die('deny');

class Demo
{
	public function run()
	{
		$this->create_conf();
		$this->create_action();
		$this->create_model();
		$this->create_tpl();
	}

	public function create_conf()
	{
		if (file_exists(APP_ACTION.$GLOBALS['_Module'].DS.'IndexAction.php'))
			return;
		$str = <<<EOT
<?php
defined('IN_FW') or die('deny');

return 
array(
	'db' => array(
		'dsn' => 'mysql:host=127.0.0.1;dbname=mkapp;charset=utf8',
		'un' => 'soulence',
		'pw' => 'soulence1211',
		'pre' => '',//表前缀
	),

	'session' => array(
		'timeout' => 30  //表示SESSION的过期时间为30分钟
	),

	'memcache' => array(
		'host' => '127.0.0.1',
		'port' => '11211',
		'timeout' => 1,//设置超时
	),

	'template' =>array(
		'left_delimiter'       =>   '{{',//模板左标签
		'right_delimiter'      =>   '}}',//模板右标签
		'suffix'			   =>   'tpl',//模板的后缀
		'is_smarty'            =>   false,//配置模板使用是否为smarty  默认框架自定义的 不过框架自定义模板支持功能较少，如果是比较复杂的项目，建议使用smarty
		'cache'				   =>   false,//是否开启模板缓存    //缓存时间是在index.php常量里面设置的 它是全局
	),
);
EOT;
		file_put_contents(APP_PATH.APP_NAME.DS.'Conf'.DS.'config.php', $str);
	}

	public function create_action()
	{
		if (file_exists(APP_ACTION.$GLOBALS['_Module'].DS.'IndexAction.php'))
			return;
		$str = <<<EOT
<?php
/**
 *  auther: soulence
 *	这是系统自动创建的controller，你可以修改
 */
class IndexAction extends Action
{
	public function Index()
	{
		\$this->set('val', 'hello world');
		\$this->set('tarr', array('soulence框架追求安全', 'soulence框架追求可扩展', 'soulence框架最求简单', 'soulence框架追求高效'));
		\$this->display();//它默认是到/Tpl/Index/Index/Index.tpl  
		//\$this->display('getCode');//它是到/Tpl/Index/Index/getCode.tpl
		//\$this->display('User/getCode');//它则是到 /Tpl/Index/User/getCode.tpl  这种指定只支持使用smarty引擎
		//\$this->display('User/User/getCode');//它则是到/Tpl/User/User/getCode.tpl  这种指定只支持使用smarty引擎
		//\$user = A('User',array());//访问同一文件夹中的类 它就是实例化 new /Lib/Action/Index/UserAction() 第二个参数为实例化是否有参数
		//\$user->Index();//对应的方法
		//\$user = A('User\\\\User');//访问其它文件夹中的类 它就是实例化 new /Lib/Action/User/UserAction()
		//\$user->Index();//对应的方法
		//\$user = M();//访问与之对应的Model 它默认就是实例化 new /Lib/Model/Index/IndexModel() 
		//\$user->Index();//对应的方法
		//\$user = M('User',array());//访问与之对应的Model 它默认就是实例化 new /Lib/Model/Index/UserModel() 第二个参数为实例化是否有参数
		//\$user->Index();//对应的方法
		//\$user = M('User\\\\User');//访问其它文件夹中的odel 它就是实例化 new /Lib/Model/User/UserAction()
		//\$user->Index();//对应的方法
	}
}
EOT;
		file_put_contents(APP_ACTION.$GLOBALS['_Module'].DS.'IndexAction.php', $str);
	}

	public function create_model()
	{
		if (file_exists(APP_MODEL.$GLOBALS['_Module'].DS.'IndexModel.php'))
			return;
		$str = <<<EOT
<?php
/*
 *  auther: soulence
 *	这是系统自动创建的Model，你可以修改
 */
class IndexModel extends Model
{
}
EOT;
		file_put_contents(APP_MODEL.$GLOBALS['_Module'].DS.'IndexModel.php', $str);
	}

	public function create_tpl()
	{
		if (file_exists(APP_TPL.'Index'.DS.'Index'.DS.'Index.'.C('template-suffix')))
			return;
		$str = <<<EOT
<html>
<head>
	<title>框架测试页面内容</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
</head>
<body>
	<div style="margin 100px auto"><h2>{{\$val}}</h2></div>
	<div style="margin 100px auto"><h2>{{if (1 == 1)}}IF 语句的测试1{{/if}}</h2></div>
	<div style="margin 100px auto"><h2>{{if (1 == 2)}}IF 语句的测试2{{/if}}</h2></div>
	<div style="margin 100px auto">
		<h2>
			<h1>第一种循环例子</h1>
			{{ foreach (\$tarr as \$t)}}
			{{\$t}}<br/>
			{{/foreach}}
			<h1>第二种循环例子</h1>
			{{ loop (\$tarr as \$t) }}
			{{\$t}}<br/>
			{{/loop}}
		</h2>
	</div>
</body>
</html>
EOT;
		file_put_contents(APP_TPL.'Index'.DS.'Index'.DS.'Index.'.C('template-suffix'), $str);
	}
}
