<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Loader;
use think\Session;
use think\Request;
use think\Url;
use app\common\tools;


/**
* 后台controller基础类
* @author aierui github  https://github.com/Aierui
 *
* @version 1.0 
*/
class Admin extends Common
{
	protected $uid = 0;
	protected $role_id = 0;

	function _initialize()
	{
		parent::_initialize();

        //检查 session 中是否有 userinfo 信息来判断是否已经登录
        if( !Session::has('userinfo', 'admin') ) {
			$this->error(lang('Please login first'), url('admin/Login/index'));
		}
		$userRow = Session::get('userinfo', 'admin');
		//验证权限,是否有进入这个路径的权限.
		$request = Request::instance();
		$rule_val = $request->module().'/'.$request->controller().'/'.$request->action();
		$this->uid = $userRow['id'];
		$this->role_id = $userRow['role_id'];
		if($userRow['administrator']!=1 && !$this->checkRule($this->uid, $rule_val)) {
		    //TODO
            //不知道这个 error 的具体的跳转的方法.
			$this->error(lang('Without the permissions page'));
		}
	}

	public function goLogin()
	{
		Session::clear();
		$this->redirect( url('admin/login/') );
	}

	public function checkRule($uid, $rule_val)
	{
		$authRule = Loader::model('AuthRule');

		//是否需要节点检查(用户是否有权限进来这个page)
		if(!$authRule->isCheck($rule_val)) {
		    //true 为 不需要检查
			return true;
		}
		$authAccess = Loader::model('AuthAccess');
		//检查节点是否在授权范围之内
		if(in_array($rule_val, $authAccess->getRuleVals($uid))){
			return true;
		}
		return false;
	}

	//执行该动作必须验证权限，否则抛出异常
	public function mustCheckRule( $rule_val = '' )
	{
		$userRow = Session::get('userinfo', 'admin');
		if( $userRow['administrator'] == 1 ) {
			return true;
		}
		if( empty($rule_val) ) {
			$request = Request::instance();
			$rule_val = $request->module().'/'.$request->controller().'/'.$request->action();
		}

		if(!model('AuthRule')->isCheck($rule_val)) {
			$this->error(lang('This action must be rule'));
		}
	}
}

