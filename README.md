 简介  
auther : soulence
email  : soulence@126.com
Q   Q  : 526649676

自己写的PHP框架简单介绍    本框架名称为:SOULENCE
SOULENCE框架是一个免费开源的，快速、简单的面向对象的 轻量级PHP开发框架，实现了开发所需的功能，实现MVC模式，实现rewrite的伪静态处理

SOULENCE框架功能说明
1.支持MVC的php简易框架 MVC支持-基于多层模型（M）、视图（V）、控制器（C）的设计模式
2.支持url rewrite的伪静态处理和U的函数url封装
3.使用纯PDO封装mysql操作，仅需要一个query函数支持insert、update、select等等操作，并且query方法支持自动生成缓存，避免多次数据库操作，默认查询结果缓存在文件中,不过建议查询使用query方法,增加修改删除统一使用DDLExecute方法，分页使用limitQuery方法，获取记录数使用CountRows方法，PDO支持"?"赋值和变量名":变量名"赋值方法，更好的有郊防止SQL注入
注：2015年05月07日以后query方法只支持查询，不再支持插入和更新操作，queryer查询多个SQL语句，DDLExecutees执行多条插入或更新SQL语句，CountRowses执行多个进度数量SQL语句
4.封装有cache类库，相同的操作方式方法，不同的缓存模式，支持file、memory两种模式缓存，可以通过CACHE_TYPE常量进行配置，f表示文件缓存，m表示memcache缓存
5.封装有分页操作的类库
6.封装有图像操作类库如水印、图像压缩、验证码生成等等操作方法
7.action父类支持error、success、timeout提示操作方法
8.action父类支持模板自动渲染编译display、以及避免重复提交的form表单的token校验防止csrf等问题
9.在application类中，session如何有memcache的环境下建议使用memcache存储模式，提升效率
10.封装了上传upload类库，避免上传漏洞，统一处理逻辑
11.去除debug模式下，支持runtime文件生成，可以合并多个需要加载的php文件，避免加载太多文件，提升效率
12.公共函数functions提供一些有用的方法如getIP来获取真实IP、读写cookie、读取配置、url路径和file路径转换函数、文件导入import函数、url封装U函数、跳转location函数、get_word/get_link取得一些特殊文本或者链接数据、vd方法是打印参数的详细信息,getpval方法它是接收参数的，它可$_GET $_POST $_SERVER $_ENV $_SESSION $_COOKIE参数，默认它是接收$_GET $_POST参数，是什么提交的参数就会使用什么方式接收参数，不会使用$_REQUEST方法 ，还有其它非常实用的方法，可自己去看，里面都有注释
13.模板引擎虽然是简单的正则编译，但是也支持常用的if、循环foreach、loop、赋值、include包含公共模板等操作，如果是比较复杂的项目建议使用smarty模板引擎，只需要在配置文件里面设置模板引擎为smarty即可，框架里面已经包含了smarty模板引擎 
14.安全性
SOULENCE框架在系统层面提供了众多的安全特性，确保你的网站和产品安全无忧。这些特性包括：
*  表单自动验证
*  强制数据类型转换
*  输入数据过滤
*  表单令牌验证
*  防SQL注入
*  图像上传检测
15.实例化类和模型都有特殊方法   A() M()方法
16.更好的错误及性能调试功能，可以通过debug=on来设置是否显示错误及性能详细信息
<?php
/**
 *  auther: soulence
 *	这是系统自动创建的controller，你可以修改
 */
class IndexAction extends Action
{
	public function Index()
	{
		$this->set('val', 'hello world');
		$this->set('tarr', array('soulence框架追求安全', 'soulence框架追求可扩展', 'soulence框架最求简单', 'soulence框架追求高效'));
		$this->display();//它默认是到/Tpl/Index/Index/Index.tpl  
		//$this->display('getCode');//它是到/Tpl/Index/Index/getCode.tpl
		//$this->display('User/getCode');//它则是到 /Tpl/Index/User/getCode.tpl  这种指定只支持使用smarty引擎
		//$this->display('User/User/getCode');//它则是到/Tpl/User/User/getCode.tpl  这种指定只支持使用smarty引擎
		//$user = A('User',array());//访问同一文件夹中的类 它就是实例化 new /Lib/Action/Index/UserAction() 第二个参数为实例化是否有参数,实例化有多少个参数数组里面就对应多个值，最多实例化支持8个参数
		//$user->Index();//对应的方法
		//$user = A('User\\User');//访问其它文件夹中的类 它就是实例化 new /Lib/Action/User/UserAction()
		//$user->Index();//对应的方法
		//$user = M();//访问与之对应的Model 它默认就是实例化 new /Lib/Model/Index/IndexModel() 
		//$user->Index();//对应的方法
		//$user = M('User',array());//访问与之对应的Model 它默认就是实例化 new /Lib/Model/Index/UserModel() 第二个参数为实例化是否有参数
		//$user->Index();//对应的方法
		//$user = M('User\\User');//访问其它文件夹中的odel 它就是实例化 new /Lib/Model/User/UserAction()
		//$user->Index();//对应的方法
	}
}
16.可扩展性  SOULENCE框架的可扩展性非常强 完全可以加入一些自己想要的任何功能
扩展目录为:/项目名称/Lib/Class/ 下  里面加入项目需要的类  再调用直接类的方法  可以自行加载   

使用注意事项
1.开启memcache扩展
2.linux平台，php支持5.3.9及以上   其实PHP5.3.X都能运行  但为了安全方面建议使用PHP5.3.9及以上
3.开启web服务器nginx或apache等的url rewrite功能


入口文件index.php简单说明，一般都会有如下配置，也建议使用如下配置，然后再访问一下当前的index.php 文件，首先访问，它会自动帮你创建好你的项目，然后直接在里面进行开发就OK
<?php
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).DS.'..'.DS);//项目地址
define('APP_NAME', 'index');//当前项目名称   这个可根据自己项目来定   设置好后访问该文件，首先运行会自动创建该项目
define('FRAMEWORK_PATH', APP_PATH.'framework'.DS);//指定框架文件地址
define('CONFIG_FILE', APP_PATH.APP_NAME.DS.'Conf'.DS.'config.php');//指定配置文件地址  不指定则会使用框架里面的配置
define('IN_FW', true);//这个是用来防止直接访问PHP文件的
define('CACHE_TYPE','f');//缓存类型  f表示文件缓存   m表示memcache缓存  默认是f
//打开安全模式需要更多的运行时间，但是这是非常重要的，默认值为true
define('OPEN_SAFE_MODEL', true);//是否需要提交的参数安全过滤   建议设置为true  安全方面考虑
define('CACHE_TIME_OUT', 10);//查询库缓存时间  如果小于10秒则不会缓存
define('CACHE_CLASS_FILE',false);//是否生成缓存类文件  建议项目运行以后设置为true 开发时设置为false
define('CREATE_DEMO', true);//是否检查默认方法是否存在   建议项目创建时设置为true 创建后可设置为false  

if(isset($_REQUEST['debug']) && trim($_REQUEST['debug'])=='on'){
	define('APP_DEBUG', true);//开启调试模式
}else{
	define('APP_DEBUG', false);//关闭调试模式
}
require(FRAMEWORK_PATH.'Framework.php');//运行框架

其它说明:
    这个框架是单一入口  访问方式为  index.php?mkd=Index&act=Index&mod=Index 这个为默认的  如果是这个路径可以不传参数
    mkd:它表示对应的哪个文件夹      Lib->Action->{mkd}
    act:它表示对应的哪个类          Lib->Action->class {act} extends Action   所有的类都必须继承Action这个类
    mod:它表示对应类中的哪个方法    Lib->Action->{act}->function {mod}(){}


项目建议：index.php这个地址最好单独放一个文件夹，然后把项目和框架文件放到其它文件夹  通过常量 APP_PATH 指定地址项目路径和框架文件路径
如：
  |--Test文件夹
     |--framework 框架文件夹
     |--Index     项目文件夹
     |--App       HTTP可访问文件夹
	|-- index.php  这个是入口文件地址  里面的内容如上28行到47行的内容
这样的好处就是外部只能访问App文件夹里面的内容，而我们的代码都是在Index里面，这样外部人员就无法访问到我们的代码


 商业友好的开源协议

SOULENCE遵循Apache2开源协议发布。鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。