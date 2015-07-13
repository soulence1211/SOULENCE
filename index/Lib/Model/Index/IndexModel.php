<?php
/*
 *  auther: soulence
 *	这是系统自动创建的Model，你可以修改
 */
class IndexModel extends Model
{
	/*
     * 第一种方法  这个方法一般是用于有很多公用方法
     * 如果公用方法用得不多，直接使用第二种方法即可
	 */
	public function index(){
		$obj = new UserClass();
		return $obj->index();
	}

	public function index2(){
		$obj = Mysql::getInstance();
		$sql = 'SELECT * FROM user_info WHERE ID=?';
		return $obj->query($sql,array(1=>5));
	}
}