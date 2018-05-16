<?php
namespace app\admin\model;

use \think\Config;
use think\Db;
use \think\Model;
use \think\Session;


/**
 * 角色权限
 *
 * @author chengbin
 */
class AuthAccess extends Admin
{

    /**
     * @param $uid
     * @return array
     *
     * 以 13330613321 为例:
     * $role_id = 2
     * $rule_ids = [3,1]
     * return : ['admin/index/index','admin/index']
     *
     *
     */
    public function getRuleVals( $uid )
    {
        //根据用户的 id,获取到 用户的 角色id(role_id).
        $role_id = model('User')->where(['id'=>$uid])->value('role_id');
        //根据用户的 角色id ,获取到用户拥有的 权限id s(可以有很多的权限).
        $rule_ids = model('AuthAccess')->where(['role_id'=>$role_id])->column('rule_id');
        //根据 权限ids ,获取到用户可以进入的路径.
        return model('AuthRule')->where('id', 'in', $rule_ids)->column('rule_val');
    }

    public function getIds( $uid )
    {
        $role_id = model('User')->where(['id'=>$uid])->value('role_id');
        return model('AuthAccess')->where(['role_id'=>$role_id])->column('rule_id');
    }

    public function saveData( $role_id, $data )
    {
        if(empty($data)) {
            return info(lang('Save success'), 1);
        }
        Db::startTrans();
        try{
            $this->where(['role_id'=>$role_id])->delete();
            $insertData = [];
            foreach($data as $val) {
                $insertData[] = ['role_id'=>$role_id, 'rule_id'=>$val];
            }
            $this->insertAll( $insertData );
            Db::commit();
        }catch (\Exception $e) {
            Db::rollback();
        }
        return info(lang('Save success'), 1);
    }
}
