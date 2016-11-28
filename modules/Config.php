<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends MY_Controller {
    function index_nologin() {
        $result = array(
            'app_name' => '',
            'region_no' => '',
            'region_name' => '',
        );
    	$result = array_merge($result, app_config());
        $result['user'] = $this->_user;
        $result['ip'] = $this->input->ip_address();
        $result['token'] = $this->_token;
        
        $this->response($result);
    }

}