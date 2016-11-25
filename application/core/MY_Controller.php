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
    protected $date = '';
    protected $definite_time = '';
    protected $env_type = '';
    protected $env_param = array();
    protected $museum = array();
    protected $texture = array();
    protected $unit = array();
    protected $env_type_arr = array(
        "cabinet"=>"展柜",
        "hall"=>"展厅",
        "storeroom"=>"库房"
    );
    
    function __construct(){
    
        parent::__construct();
        $this->load->helper(array("calculate"));
        $this->load->config("texture");
        $this->texture = config_item("texture");
        $this->unit = config_item("unit");
        $this->definite_time = $this->get("definite_time");
        if(!$this->definite_time){
            $this->definite_time = "yesterday";
        }
        $env_type = $this->get("env_type");
        if(!$env_type){
            $env_type = "cabinet";
        }
        $this->env_type = $this->env_type_arr[$env_type];
        $env_param = $this->get("env_param");
        if(!$env_param){
            $env_param = "temperature,humidity";
        }
        $this->env_param = explode(",",$env_param);
        if($this->definite_time){
            switch ($this->definite_time){
                case "yesterday": //昨天
                    $yes = date("Ymd",strtotime("-1 day"));
                    $this->date = "D".$yes;
                    break;
                case "before_yes": //前天
                    $by = date("Ymd",strtotime("-2 day"));
                    $this->date = "D".$by;
                    break;
                case "week": //本周
                    $this->date = "W".date("Y").date("W");
                    break;
                case "month": //本月
                    $this->date = "M".date("Y").date("m");
                    break;
                default:
                    $this->date = "D".$this->definite_time;
            }
        }
        $museum = $this->db->select("id,name")->get("museum")->result_array();
        foreach ($museum as $value){
            $this->museum[$value["id"]] = $value["name"];
        }
    }
    

}