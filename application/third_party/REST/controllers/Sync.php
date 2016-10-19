<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 子系统同步登录与退出
 * Class Sync
 */
class Sync extends MY_Controller
{
    function index_get_nologin()
    {
        $this->response(array('msg' => 'ok'));
    }

    //子系统应用同步登录
    function login_post_nologin()
    {
        $auth_code = $this->post('auth_code');
        $local_api = config_item('local_api');
        $data = API_decode($local_api, $auth_code);
        if (empty($data)) {
            $this->response(array('error' => '解密失败!', 'local_api' => $local_api), 400);
        }
        if ($data['token'] != $this->_token) {
            $this->response(array(
                'error' => 'token!=rest_token',
                'token' => $data['token'],
                'rest_token' => $this->_token,
            ), 400);
        }
        unset($data['token']);
        $token_level = isset($data['token_level']) ? $data['token_level'] : 1;
        unset($data['token_level']);
        $this->load->library('rest_token');
        $ip = $this->input->ip_address();
        if ($data['ip']) {
            $ip = $data['ip'];
        }
        $r = $this->rest_token->set_user($this->_token, $data, $token_level, $ip);
        if (!$r) {
            $this->response(array('error' => '登录失败', 'local_api' => $local_api), 400);
        }
        $this->response(array("msg" => '登录成功!', 'local_api' => $local_api));
    }

    //子系统应用同步退出
    function logout_post()
    {
        $auth_code = $this->post('auth_code');
        $local_api = config_item('local_api');
        $data = API_decode($local_api, $auth_code);
        if (empty($data)) {
            $this->response(array('error' => '解密失败!', 'local_api' => $local_api), 400);
        }
        if ($data['token'] != $this->_token) {
            $this->response(array(
                'error' => 'token!=rest_token',
                'token' => $data['token'],
                'rest_token' => $this->_token,
            ), 400);
        }
        $this->load->library('rest_token');
        $r = $this->rest_token->destroy_token($this->_token);
        if (!$r) {
            $this->response(array('error' => '注销失败', 'local_api' => $local_api), 400);
        }
        $this->response(array("msg" => '注销成功!', 'local_api' => $local_api));
    }

}