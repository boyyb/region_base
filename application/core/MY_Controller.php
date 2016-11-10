<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');
require APPPATH . 'third_party/REST/core/REST_Controller.php';

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
 *
 */
class MY_Controller extends REST_Controller
{
    protected $start_time = '';
    protected $end_time = '';
    protected $definite_time = '';
    protected $env_type = '';
    protected $env_param = array();
    protected $museum = array();
    protected $texture = array();
    
    function __construct(){
    
        parent::__construct();
        $this->load->helper(array("calculate"));
        $this->load->config("texture");
        $this->texture = config_item("texture");
        $this->start_time = $this->get("start_time");// 20160101
        $this->end_time = $this->get("end_time");
        $this->definite_time = $this->get("definite_time");
        $this->env_type = $this->get("env_type");
        $env_param = $this->get("env_param");
        $this->env_param = explode(",",$env_param);
        if($this->definite_time){
            switch ($this->definite_time){
                case "yesterday": //昨天
                    $this->start_time = $this->end_time = date("Ymd",time() - 24*60*60);
                    break;
                case "before_yes": //前天
                    $this->start_time = $this->end_time = date("Ymd",time() - 24*60*60*2);
                    break;
                case "week": //本周
                    $day_num = date("w");
                    $this->start_time = date("Ymd",time() - 24*60*60*($day_num-1));
                    $this->end_time = date("Ymd",time() + 24*60*60*(7-$day_num));
                    break;
                case "month": //本月
                    $this->start_time = date("Ym")."01";
                    $this->end_time = date("Ym").date("t");
                    break;
            }
        }
        $museum = $this->db->select("id,name")->get("museum")->result_array();
        foreach ($museum as $value){
            $this->museum[$value["id"]] = $value["name"];
        }
    }
    

}