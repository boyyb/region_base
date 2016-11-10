<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class user_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }


    /**
    * 获取用户行为记录
    * @param $uid [int][必选] 用户ID
    * @param $webkey [string][可选] 页面关键字
    * @return [数组] 用户行为记录列表
    */
    public function get_behavior($uid, $webkey='')
    {
        $this->db->where(array('uid'=>$uid));

        if($webkey){
            $this->db->where(array('webkey'=>$webkey));
        }

        $data = $this->db->get('user_behavior')->result_array();
        $behavior = array();
        foreach ($data as $d) {
            $behavior[$d['webkey']] = json_decode($d['behavior'], true);
        }
        return $behavior;
    }

}