<?php
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).DS.'..'.DS);//项目地址
define('APP_NAME', 'index');//当前项目名称   这个可根据自己项目来定   设置好后访问该文件，首先运行会自动创建该项目
define('FRAMEWORK_PATH', APP_PATH.'framework'.DS);//指定框架文件地址
define('CONFIG_FILE', APP_PATH.APP_NAME.DS.'Conf'.DS.'config.php');//指定配置文件地址  不指定则会使用框架里面的配置
define('IN_FW', true);//这个是用来防止直接访问PHP文件的
define('CACHE_TYPE','f');//缓存类型  f表示文件缓存   m表示memcache缓存  默认是m
//打开安全模式需要更多的运行时间，但是这是非常重要的，默认值为true
define('OPEN_SAFE_MODEL', true);//是否需要提交的参数安全过滤   建议设置为true  安全方面考虑
define('CACHE_TIME_OUT', 8);//查询库缓存时间  如果小于10秒则不会缓存
define('CACHE_CLASS_FILE',false);//是否生成缓存类文件  建议项目运行以后设置为true 开发时设置为false
define('CREATE_DEMO', true);//是否检查默认方法是否存在   建议项目创建时设置为true 创建后可设置为false  

if(isset($_REQUEST['debug']) && trim($_REQUEST['debug'])=='on'){
	define('APP_DEBUG', true);//开启调试模式
}else{
	define('APP_DEBUG', false);//关闭调试模式
}
require(FRAMEWORK_PATH.'Framework.php');//运行框架
