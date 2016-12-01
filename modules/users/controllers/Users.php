<?php

class Users extends MY_Controller{

    function __construct()
    {
        parent::__construct();
    }
    

    /**
    * 添加用户行为
    */
    function behavior_post()
    {
        $webkey = $this->post('webkey');
        $behavior = $this->post('behavior');
        $data = array(
        	'uid' => $this->_user['id'],
        	'webkey' => $webkey
        );
        if($this->db->where($data)->count_all_results('user_behavior')){
        	$ret = $this->db->where($data)->update('user_behavior', array('behavior'=>json_encode($behavior)));
        }else{
        	$data['behavior'] = json_encode($behavior);
	        $ret = $this->db->insert("user_behavior", $data);
        }
        $this->response($ret?1:0);
    }


    /**
    * 获取用户行为
    */
    public function behavior_get(){
        $webkey = $this->get('webkey');

        $this->load->model('user_model');
        $behavior = $this->user_model->get_behavior($this->_user['id'], $webkey);

        if($webkey){
            $behavior = isset($behavior[$webkey]) ? $behavior[$webkey] : '';
        }

        $this->response($behavior);
    }
    
}