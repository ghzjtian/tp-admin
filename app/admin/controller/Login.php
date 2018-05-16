<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Loader;
use think\Request;
use think\Url;
use think\Session;
use think\Config;

/**
* 登录
* @author aierui github  https://github.com/Aierui
* @version 1.0 
*/
class Login extends Common
{
	/**
	 * 后台登录首页
	 */
	public function index()
	{
		if( Session::has('userinfo', 'admin') ) {
			$this->redirect( url('admin/index/index') );
		}
		return view();
	}

	/**
	 * 登录验证
	 */
	public function doLogin()
	{
		if( !Request::instance()->isAjax() ) {
			return $this->success( lang('Request type error') );
		}

		$postData = input('post.');
		$captcha = $postData['captcha'];

		//验证码的用法 : https://www.kancloud.cn/manual/thinkphp5/154295
		if(!captcha_check($captcha)){
			return $this->error( lang('Captcha error') );
		};
		$loginData = array(
			'mobile'=>$postData['mobile'],
			'password'=>$postData['password']
		);
		//跳到 User 模型那里去验证
		$ret = Loader::model('User')->login( $loginData );
		if ($ret['code'] !== 1) {
			return $this->error( $ret['msg'] );
		}
		unset($ret['data']['password']);
		//保存用户的信息(数据库中保存的信息)到 session
		Session::set('userinfo', $ret['data'], 'admin');
		//保存登录的记录.
		Loader::model('LogRecord')->record( lang('Login succeed') );
		return $this->success($ret['msg'], url('admin/index/index'));
	}

	/**
	 * 退出登录
	 */
	public function out()
	{
		session::clear('admin');
		return $this->success('退出成功！', url('admin/login/index'));
	}
}