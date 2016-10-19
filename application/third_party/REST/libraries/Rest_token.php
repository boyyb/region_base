<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rest_token
{
    protected $_ci;

    function __construct()
    {
        $this->_ci =& get_instance();
    }

    //API获取token
    public function create_token()
    {
        $token_name = config_item('rest_token_param_name');
        //other app header
        $token = $this->_ci->input->get_post($token_name, true);
        $token = $token ? $token : $this->_ci->input->server($token_name, true);
        $token = $token ? $token : $this->_ci->input->get_request_header($token_name);
        //local proxy token
//        $token = $token ? $token : $this->_ci->input->cookie($token_name, true);
        $token = $token ? $token : $this->_ci->session->userdata($token_name);
        $row = false;
        if ($token) {
            $row = $this->_get_token($token);
            return $row;
        }
        //var_dump($token);
        //exit;
        $token = $this->_generate_token();

        if ($token) {
            $row = $this->_insert_token($token);
//            $this->_ci->input->set_cookie($token_name, $token);
            $this->_ci->session->set_userdata($token_name, $token);
        }

        return $row;

    }

    //注销token
    public function destroy_token($token)
    {
        if (!$this->_exists_token($token)) {
            return false;
        }
        return $this->_delete_token($token);
    }

    //REST_Controller 返回token和user信息
    public function get_token($token)
    {
        return $this->_get_token($token);
    }

    //用户登录,由base推送过来(Sync)，将user json,level
    public function set_user($token, $user_row = array(), $level = 1, $ip = '')
    {
        if (!$this->_exists_token($token)) {
            return false;
        }
        $data = array('user' => zh_json_encode($user_row), 'level' => $level, 'ip' => $ip);
        return $this->_update_token($token, $data);
    }

    /* Private Data Methods */

    private function _generate_token()
    {
        do {
            $salt = md5(uniqid('', true) . '_' . mt_rand());
            $local_api = config_item('local_api');
            $salt_en = API_encode($local_api, $salt);
            $token = $local_api . '_' . $salt_en;
        } while ($this->_exists_token($token));

        return $token;
    }

    private function _exists_token($token)
    {
        return $this->_ci->db
            ->where('token', $token)
            ->count_all_results('tokens') > 0;
    }

    private function _insert_token($token)
    {
        if (strlen($token) < 40) {
            return false;
        }
        $data = array('level' => 1);
        $data['token'] = $token;
        $data['ip'] = $this->_ci->input->ip_address();
        $data['create_time'] = time();
        $data['last_activity'] = time();

        if (!$this->_ci->db->insert('tokens', $data)) {
            return false;
        }
        return $data;
    }

    private function _update_token($token, $data = array())
    {
        $data['last_activity'] = time();
        return $this->_ci->db
            ->where('token', $token)
            ->update('tokens', $data);
    }

    private function _delete_token($token)
    {
        return $this->_ci->db
            ->where('token', $token)
            ->delete('tokens');
    }

    private function _get_token($token)
    {
        if (strlen($token) < 40) {
            return false;
        }
        $is_salts = false;
        if (config_item('rest_token_auto_insert')) {
            $is_salts = true;
        } else {
            if (substr_count($token, '_') > 1) {
                list($app, $salt) = explode('_', $token, 2);
                $salts = API_decode($app, $salt);
                if (empty($salts)) {
                    return false;
                }
                $is_salts = true;
            }
        }

        $row = $this->_ci->db
            ->where('token', $token)
            ->get('tokens')
            ->row_array();

        if (!isset($row) && $is_salts) {
            return $this->_insert_token($token);//自动注册
        }
        if ($row && (time() - $row['last_activity']) > 60) {
            $this->_update_token($token);
        }

        if (mt_rand(1, 10) == '1') {// 1/10的几率执行
            $this->_ci->db
                ->where(array('last_activity <' => time() - 3600, 'level <' => 9))
                ->where("user is null")
                ->delete('tokens');//删除1小时未活动和user is null的token
            $this->_ci->db
                ->where(array('last_activity <' => time() - 8 * 3600, 'level <' => 9))
                ->delete('tokens');//删除超过8小时未活动的token
        }

        return $row;
    }
}