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

        // 登录成功
        $result['is_login'] = true;
        // 用户行为记录
        $this->load->model('user_model');
        $result['behavior'] = $this->user_model->get_behavior($row['id']);

        $this->response($result);
    }
    
}