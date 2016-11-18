<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class user_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();
    }


    /**
    * 获取用户行为记录
    * @param $uid [int][必选] 用户ID
    * @param $webkey [string][可选] 页面关键字
    * @return [数组] 用户行为记录列表
    */
    public function get_behavior($uid, $webkey='')
    {
        $this->db->where(array('uid'=>$uid));

        if($webkey){
            $this->db->where(array('webkey'=>$webkey));
        }

        $data = $this->db->get('user_behavior')->result_array();
        $behavior = array();
        foreach ($data as $d) {
            $behavior[$d['webkey']] = json_decode($d['behavior'], true);
        }
        return $behavior;
    }





    function get_user($uid = 0)
    {
        static $_users = array();
        if (isset($_users[$uid])) {
            return $_users[$uid];
        }
        $list = $this->fetAll();
        foreach ($list as $row) {
            $_users[$row['id']] = $row;
        }

        if (isset($_users[$uid])) {
            return $_users[$uid];
        }
        return false;
    }

    function get_permissions($uid = 0, $role_ids = '')
    {
        if ($uid == 1) {
            return 'administrator';
        }
        if ($role_ids == '') {
            $user = $this->get_user($uid);
            if (!$user) {
                return '';
            }
            $role_ids = $user['role_ids'];
        }
        return $this->_get_permissions($role_ids);

    }

    function _get_permissions($role_ids)
    {
        if($role_ids==''){
            return '';
        }
        $m = M('role');
        $list = $m->fetAll(" id in (" . $role_ids . ")");
        $permissions = array();
        foreach ($list as $row) {
            if (isset($row['permissions']) && $row['permissions'] != '') {
                $tmp = explode(',', $row['permissions']);
                $permissions = array_merge($tmp, $permissions);
            }
        }
        $permissions = array_unique($permissions);
        if (count($permissions) == 0) {
            return '';
        }
        $permissions = join(',', $permissions);
        return $permissions;
    }

    function get_role_names($role_ids)
    {
        $rm = M('roles/role');

        $role_names = array();
        if ($role_ids != '') {
            $role_tmp = explode(',', $role_ids);
            foreach ($role_tmp as $role_id) {
                $role = $rm->get_role($role_id);
                if ($role) {
                    $role_names[] = $role['name'];
                }
            }
        }
        return join(',', $role_names);

    }

    function login_count($user_id)
    {
        $m = M('user_login');
        $today_time = strtotime(date('Y-m-d'));
        $count = $m->count("user_id='" . $user_id . "' and login_time>" . $today_time);
        if ($count == 0) {
            $m->add(array('user_id' => $user_id, 'login_time' => time()));
        }
    }

}