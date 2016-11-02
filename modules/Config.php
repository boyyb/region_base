<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Config extends MY_Controller {
    function index_nologin() {
        $result = array('app_name' => app_config('app_name'));
        $result['user'] = $this->_user;
        $result['token'] = $this->_token;
        $result['ip'] = $this->input->ip_address();
        $result['region_name'] = app_config('region_name');
        
        $this->response($result);
    }

}