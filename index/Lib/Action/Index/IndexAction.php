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
		//$user = A('User',array());//访问同一文件夹中的类 它就是实例化 new /Lib/Action/Index/UserAction() 第二个参数为实例化是否有参数
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

	public function FaAllan()
	{
		$this->set('val', 'hello喂！');
		$this->set('tarr', array('数组1', '数组2', '数组3', '数组4'));
		$this->display('FaAllan');
	}
}
