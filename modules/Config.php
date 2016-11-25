<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends MY_Controller {
    function index_nologin() {
    	$app_config = app_config();
    	$result = array();
        $result['region_no'] = isset($app_config['region_no']) ? $app_config['region_no'] : '';
        $result['app_name'] = isset($app_config['app_name']) ? $app_config['app_name'] : '';
        $result['region_name'] = isset($app_config['region_name']) ? $app_config['region_name'] : '';
        $result['map_name'] = isset($app_config['map_name']) ? $app_config['map_name'] : '';
        $result['user'] = $this->_user;
        $result['ip'] = $this->input->ip_address();
        $result['token'] = $this->_token;
        
        $this->response($result);
    }

}