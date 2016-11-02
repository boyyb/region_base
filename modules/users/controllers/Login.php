<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/10/28
 * Time: 14:59
 */
class Login extends MY_Controller{
    
    function index_post_nologin()
    {
        $result = array();
        $user = $this->post('user');
        $pwd = $this->post('pwd');
        $ip = $this->post('ip');
//        print_r($_REQUEST);
        if (empty($user) || empty($pwd)) {
            $this->response(array('error' => '请输入用户名和密码'));
        }
        $m = M('user');
        $row = $m->find("username='" . $user . "'");
        if (!$row) {
            $this->response(array('error' => '不存在此用户'));
        }

        if ($row['password'] != md5($user . $pwd)) {
            $this->response(array('error' => '密码错误'));
        }

        $ip = $this->_ip;
        if($ip){
            $str = substr($ip,0,7);
            if($str == "192.168"||$ip == "127.0.0.1"){
                $this->log_in($row);
            }else{
                //$this->check_ip($row,$ip);
            }
        }
    }

    function log_in($row){
        //$m = M('user');
        //$m->login_count($row['id']);//记录登录次数
        //$permissions = $m->get_permissions($row['id'], $row['role_ids']);//array('环境监测', '系统管理', '环境监测.概览', '环境监测.概览.新增');
        $user_row = array(
            'id' => $row['id'],
            'username' => $row['username'],
            'level' => $row['level'],
            'real_name' => $row['real_name'],
            //'permissions' => $permissions,
            'data_scope' => '',
            'token' => $this->_token,
            'token_level' => 1,
            'ip'=>$this->_ip,
        );

        $api_hosts = config_item('api_hosts');
        $api_list = array();
        foreach ($api_hosts as $app => $api) {
            $api_list[] = array('post/' . $app . '/sync/login', array('auth_code' => API_encode($app, $user_row)));
        }
        $result['results'] = API($api_list);//批量发出

        $result ['msg'] = $row['username'] . '登录成功';
        //$result['permissions'] = $permissions;
        $result['is_login'] = true;

        $this->response($result);
    }
    
}