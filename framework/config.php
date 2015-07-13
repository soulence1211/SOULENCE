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
