<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Base REST Controller Library
 *
 * @package Controller
 * @category Libraries
 * @property CI_DB_query_builder $db
 * @property CI_Config $config
 * @property CI_Controller $controller
 * @property CI_Model $model
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Output $output
 */
class REST_Controller extends CI_Controller
{
    protected $_raw_args = array();

    protected $_method = null;//get|post|put|delete
    protected $_token = null;//length>40 string
    protected $_user = null;//token绑定用户
    protected $_ip = null;//客户端ip

    protected $_start_time = '';
    protected $_end_time = '';

    protected $_log_insert_id = '';

    public function __construct()
    {
        parent::__construct();
        $this->_ip = $this->input->ip_address();
        $this->_start_time = microtime(true);

        $this->_check_white_list_auth();//检查白名单

        if (config_item('force_https') && !is_https()) {
            $this->response(array('error' => '只允许 https 访问'), 401);
        }

        $this->_method = $this->_detect_method();//解析http动作

        if (!in_array($this->_method, config_item('allowed_http_methods'))) {
            $this->response(array('error' => 'method不在允许的methods内'), 401);
        }

        $this->_parse_raw();//解析原始数据流

        // Checking for Token?
        if (config_item('rest_token_enable')) {
            $this->_detect_api_token();
        }
        // log
        if (config_item('rest_logging_enable')) {
            $this->_log_request();
        }

    }

    /**
     * Destructor function
     * @author Chris Kacerguis
     */
    public function __destruct()
    {
        $this->_end_time = microtime(true);
        if (config_item('rest_logging_enable')) {
            $this->_log_access_time();
        }
    }

    /**
     * 自定义映射关系
     * @param $object_called
     * @param $arguments
     */
    public function _remap($object_called, $arguments)
    {
        $controller_method = $object_called . '_' . $this->_method;

        if (config_item('rest_token_enable') and (!$this->_token || strlen($this->_token) < 40)) {
            $this->response(array('error' => '没有找到合法的access_token'), 401);
        }
        $check_token_user = TRUE;

        if (method_exists($this, $object_called . '_' . $this->_method . '_nologin')) {//不需要验证token登录的,无_user
            $controller_method = $object_called . '_' . $this->_method . '_nologin';
            $check_token_user = false;
        } else if (method_exists($this, $object_called . '_' . $this->_method)) {
            $controller_method = $object_called . '_' . $this->_method;
        } else if (method_exists($this, $object_called . '_nologin')) {//不区别get/post,并无_user
            $controller_method = $object_called . '_nologin';
            $check_token_user = false;
        } else if (method_exists($this, $object_called)) {//不区别get/post
            $controller_method = $object_called;
        } else {//默认到index
            if (method_exists($this, 'index_' . $this->_method . '_nologin')) {//不需要验证token登录的index
                $controller_method = 'index_' . $this->_method . '_nologin';
                $check_token_user = false;
            } else if (method_exists($this, 'index_' . $this->_method)) {//不需要验证token登录的index
                $controller_method = 'index_' . $this->_method;
                $check_token_user = false;
            } else if (method_exists($this, 'index_nologin')) {//不区别get/post,并无_user
                $controller_method = 'index_nologin';
                $check_token_user = false;
            } else if (method_exists($this, 'index')) {//不区别get/post
                $controller_method = 'index';
            }
            array_unshift($arguments, $object_called);
        }

//        var_dump($controller_method);

        $this->_check_token_user($check_token_user);//检查token是否绑定用户

        if (!method_exists($this, $controller_method)) {
            $this->response(array('error' => '没有找到方法:' . $controller_method), 404);
        }

        try {
            call_user_func_array(array($this, $controller_method), $arguments);
        } catch (Exception $ex) {
            // If the method doesn't exist, then the error will be caught and an error response shown
            $this->response(array(
                'error' => '执行错误', 'message' => $ex->getMessage()
            ), 500);
        }
    }

    /**
     * 输出内容
     * @param null $data
     * @param null $http_code
     * @param bool|false $continue
     */
    public function response($data = null, $http_code = null, $continue = false)
    {
        if ($http_code !== NULL) {
            $http_code = (int)$http_code;
        }
        $output = NULL;
        if ($data === NULL && $http_code === NULL) {
            $http_code = 404;
        } elseif ($data !== NULL) {
            if (is_array($data) && isset($data['error']) && config_item('debug')) {
                $data['_token'] = $this->_token;
                $data['_get_args'] = $this->get();
                $data['_post_args'] = $this->post();
            }
            $output = zh_json_encode($data);
        }
        $http_code > 0 || $http_code = 200;
        $this->output->set_status_header($http_code);
        $content_type = 'json';
        if ($this->_method == 'get' && $callback = $this->get('callback')) {
            $output = $callback . "(" . $output . ");";
            $content_type = 'js';
        }

        $this->output->set_content_type($content_type);
        $this->output->set_output($output);
        if ($continue === FALSE) {
            $this->output->_display();
            exit;
        }
    }

    /**
     * @access public
     * @param array|NULL $data Data to output to the user
     * @param int|NULL $http_code HTTP status code
     */
    public function set_response($data = NULL, $http_code = NULL)
    {
        $this->response($data, $http_code, TRUE);
    }

    /**
     * 获取token
     */
    protected function _detect_api_token()
    {
        $token_name = config_item('rest_token_param_name');

        $token = $this->input->get_post($token_name, true);
        $token = $token ? $token : $this->input->server($token_name, true);
        $token = $token ? $token : $this->input->get_request_header($token_name);

        if ($token) {
            $this->_token = $token;
        }
    }

    /**
     * 解析http动作
     * @return string
     */
    protected function _detect_method()
    {
        $method = $this->input->server('REQUEST_METHOD');

        // Determine whether the 'enable_emulate_request' setting is enabled
        if (config_item('enable_emulate_request')) {
            $method = $this->input->post('_method');
            if ($method === NULL) {
                $method = $this->input->server('HTTP_X_HTTP_METHOD_OVERRIDE');
            }
        }
        if (empty($method)) {
            // Get the request method as a lowercase string
            $method = $this->input->method();
        }
        $method = strtolower($method);

        return in_array($method, config_item('allowed_http_methods')) ? $method : 'get';
    }

    /**
     * 解析流数据(raw)
     */
    protected function _parse_raw()
    {
        $input_stream = file_get_contents('php://input');
        if ($input_stream != '' && $input_stream{0} == '{') {
            $this->_raw_args = json_decode($input_stream, true);
        } else {
            parse_str($input_stream, $this->_raw_args);
        }
    }

    // 获取传递参数 FUNCTION

    /**
     * 获取get参数
     * @param null $key
     * @param bool|true $xss_clean
     * @return mixed
     */
    public function get($key = null, $xss_clean = true)
    {
        return $this->input->get($key, $xss_clean);
    }

    /**
     * 取得post数据，支持获取流数据(raw)
     * @param null $key
     * @param bool|true $xss_clean
     * @return array|mixed|string
     */
    public function post($key = null, $xss_clean = true)
    {
        if ($key == null) {
            return array_merge($this->input->post($key, $xss_clean), $this->_raw_args);
        }
        $post = $this->input->post($key, $xss_clean);
        if (!isset($post) && isset($this->_raw_args[$key])) {
            return $this->_xss_clean($this->_raw_args[$key], $xss_clean);
        }
        return $post;
    }

    /**
     * 先查询get,如没有找到则找post
     * @param string|null $key
     * @param bool|true $xss_clean
     * @return array|mixed|string
     */
    public function get_post($key = null, $xss_clean = true)
    {
        if ($key == null) {
            return array_merge($this->get($key, $xss_clean), $this->post($key, $xss_clean));
        }
        $get = $this->get($key, $xss_clean);
        return isset($get) ? $get : $this->post($key, $xss_clean);
    }

    /**
     * 先查询post,如没有找到则找get
     * @param string|null $key
     * @param bool|true $xss_clean
     * @return array|mixed|string
     */
    public function post_get($key = null, $xss_clean = true)
    {
        if ($key == null) {
            return array_merge($this->post($key, $xss_clean), $this->get($key, $xss_clean));
        }
        $post = $this->post($key, $xss_clean);
        return isset($post) ? $post : $this->get($key, $xss_clean);
    }

    /**
     * Process to protect from XSS attacks.
     * @param  string $value The input.
     * @param  boolean $xss_clean Do clean or note the input.
     * @return string
     */
    protected function _xss_clean($value, $xss_clean)
    {
        is_bool($xss_clean) || $xss_clean = true;
        return $xss_clean === TRUE ? $this->security->xss_clean($value) : $value;
    }

    /**
     * IP白名单, config-> rest_ip_white_list
     */
    protected function _check_white_list_auth()
    {
        $white_list = config_item('rest_ip_white_list');
        if (!$white_list || count($white_list) == 0) {
            return;
        }
        array_push($white_list, '127.0.0.1', '0.0.0.0');
        foreach ($white_list AS &$ip) {
            $ip = trim($ip);
        }
        if (!in_array($this->input->ip_address(), $white_list)) {
            $this->response(array('error' => 'IP 不在允许的白名单内'), 401);
        }
    }

    /**
     * 记录日志
     */
    protected function _log_request()
    {
        $data = array(
            'uri' => $this->uri->uri_string(),
            'method' => $this->_method,
            'params' => $this->get_post() ? json_encode($this->get_post()) : null,
            'token' => isset($this->_token) ? $this->_token : '',
            'ip_address' => $this->input->ip_address(),
            'start_time' => $this->_start_time,
            'user' => isset($this->_user) ? zh_json_encode($this->_user) : "",
        );
        $this->db->insert(config_item('rest_logs_table'), $data);

        $this->_log_insert_id = $this->db->insert_id();
    }

    /**
     * 记录controller运行时间
     * @return mixed
     */
    protected function _log_access_time()
    {
        $payload = array();
        $payload['exec_time'] = $this->_end_time - $this->_start_time;

        return $this->db->update(config_item('rest_logs_table'), $payload, array('id' => $this->_log_insert_id));
    }

    /**
     * 检查token是否登录
     * @param bool|true $check_token_user
     */
    protected function _check_token_user($check_token_user = true)
    {
        if (!config_item('rest_token_enable')) {
            return;
        }
        $this->load->library('rest_token');
        $row = $this->rest_token->get_token($this->_token);

        if (!$row || !isset($row['token'])) {
            $this->response(array('error' => 'access_token无效'), 401);
        }
        if (isset($row['user'])) {
            $this->_user = json_decode($row['user'], true);
        }
        $this->_ip = $row['ip'];

        if ($check_token_user && !$this->_user) {
            $this->response(array('error' => '没有登录'), 403);
        }

    }

}