<?php

class Users extends MY_Controller{

    function __construct()
    {
        parent::__construct();
    }
    
    function behavior_post()
    {
        $webkey = $this->post('webkey');
        $behavior = $this->post('behavior');
        $data = array(
        	'uid' => $this->_user['id'],
        	'webkey' => $webkey
        );
        // if($this->db->where($data)->count_all_results('user_behavior')){
        // 	$ret = $this->db->where($data)->update('user_behavior', array('behavior'=>json_encode($behavior)));
        // }else{
        // 	$data['behavior'] = json_encode($behavior);
	       //  $ret = $this->db->insert("user_behavior", $data);
        // }
        // $this->response($ret?1:0);

        $this->load->model('user_model');
        $data = $this->user_model->get_behavior(1);
        $this->response($data);
    }
    
}