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
        if (empty($user) || empty($pwd)) {
            $this->response(array('error' => '请输入用户名和密码'));
        }
        $row = $this->db->where(array('username'=>$user))->get("user")->row_array();
        if (!$row) {
            $this->response(array('error' => '不存在此用户'));
        }

        if ($row['password'] != md5($user . $pwd)) {
            $this->response(array('error' => '密码错误'));
        }

        
        $this->load->model('user_model');
        //记录登录次数
        $this->user_model->login_count($row['id']);

        // 登录成功
        $result['is_login'] = true;
        $result ['msg'] = $row['username'] . '登录成功';
        // 权限
        $row['permissions'] = $this->user_model->get_permissions($row['id'], $row['role_ids']);
        // 同步
        $result['results'] = $this->_sync($row);
        $result['permissions'] = $row['permissions'];
        $result['token'] = $this->_token;

        $this->response($result);
    }

    // 子系统登录同步
    function _sync($row){
        $user_row = array(
            'id' => $row['id'],
            'username' => $row['username'],
            'level' => $row['level'],
            'real_name' => $row['real_name'],
            'permissions' => $row['permissions'],
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
        return API($api_list);//批量发出
    }
    
}