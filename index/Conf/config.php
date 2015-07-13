<?php
defined('IN_FW') or die('deny');

return array(
	'db' => array(
		'dsn' => 'mysql:host=127.0.0.1;dbname=mocha_v2;charset=utf8',
		'un'  => 'soulence',
		'pw'  => 'soulence1211',
		'pre' => '',//表前缀
	),

	'session' => array(
		'timeout' => 7*1440  //表示SESSION的过期时间为30分钟
	),

	'memcache' => array(
		'host' => '127.0.0.1',
		'port' => '11211',
		'timeout' => 1,//设置超时
	),

	'template' => array(
		'left_delimiter'  => '{-',//模板左标签
		'right_delimiter' => '-}',//模板右标签
		'suffix'          => 'tpl',//模板的后缀
		'is_smarty'       => true,//配置模板使用是否为smarty；默认框架自定义的；不过框架自定义模板支持功能较少，如果是比较复杂的项目，建议使用smarty
		'cache'           => false,//是否开启模板缓存；另：缓存时间是在index.php常量里面设置的 它是全局
	),

	'order_timeout'     =>  90,//表示礼券的过期时间为90天

	'mobile_sms' => array(
		'account_sid'   => 'aaf98f8949d575140149e0b06019065a', // 主帐号
		'account_token' => '0133a30f04ce4e84ac53cfc9315fcd4f', // 主帐号Token
		'server_ip'     => 'sandboxapp.cloopen.com', // 请求服务器IP，开头不需要写https://；('app.cloopen.com')
		'server_port'   => '8883', // 请求服务器端口
		'rest_version'  => '2013-12-26', // REST版本号
		'sms_app_id'    => '8a48b55149e0e7a20149e11cfbfa0052', // 应用Id
		'sms_tpl_id'    => array(
			'8643', // 【摩方网】验证码：{1}，请于{2}分钟内输入。
		),
	),

	'alipay' => array(
		'partner'       		=> '2088211116843513',
		'private_key_path'      => APP_PATH.APP_NAME.DS.'Conf'.DS.'rsa_private_key.pem',
		'ali_public_key_path'   => APP_PATH.APP_NAME.DS.'Conf'.DS.'alipay_public_key.pem',
		'key'           		=> 'ckvqsllqszrqdnych53mf0dvi8j1ugmu',
		'seller_email'  		=> 'zhifubao@mo-fang.com',
		'notify_url'    		=> 'http://api.jtuntech.com/event/2015/mocha/App/index.php?act=Buy&mod=AlipayNotify',
		'sign_type'     		=> strtoupper('RSA'),
		'input_charset' 		=> strtolower('utf-8'),
		'cacert' 				=> APP_PATH.APP_NAME.DS.'Conf'.DS.'cacert.pem',
		'transport'     		=> 'http'
	),
);
