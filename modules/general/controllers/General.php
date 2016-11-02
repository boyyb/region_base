<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/10/31
 * Time: 10:24
 */
class General extends MY_Controller{
    protected $start_time = '';
    protected $end_time = '';
    protected $definite_time = '';
    protected $env_type = '';
    protected $env_param = array();
    protected $museum = array();
   
    
    function __construct()
    {
        parent::__construct();
        $this->load->helper(array("calculate"));
        $this->start_time = $this->post("start_time");// 20160101
        $this->end_time = $this->post("end_time");
        $this->definite_time = $this->post("definite_time");
        $this->env_type = $this->post("env_type");
        $this->env_param = $this->post("env_param");//array
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

    public function detail_standard_post($flag = false){ //区域详情达标率
        $params = '';
        $suffix = array("total","abnormal");
        $data_flag = $data_standard = array();
        foreach ($this->env_param as $param){
            $params .= ",c.".$param."_total".",c.".$param."_abnormal";
        }
        $data_compliance = $this->db->select("m.id,c.id as cid".$params)
                                    ->join("data_env e","e.mid=m.id")
                                    ->join("data_env_compliance c","c.eid=e.id")
                                    ->where("e.env_type",$this->env_type)
                                    ->where("c.date >=",$this->start_time)
                                    ->where("c.date <=",$this->end_time)
                                    ->get("museum m")
                                    ->result_array();
        //echo $this->db->last_query();exit;
        foreach ($data_compliance as $value){
            if($value["id"]){
                foreach ($this->env_param as $param){
                    foreach ($suffix as $s){
                        $data_flag[$value["id"]][$s][] = $value[$param."_".$s];
                    }
                }
            }
        }

        foreach ($data_flag as $k => $value){
                $total = array_sum($value["total"]);
                $abnormal = array_sum($value["abnormal"]);
                $data_standard[$k] = round(($total - $abnormal) / $total,2);
        }

        if($flag){
            return $data_standard;
        }

        $this->response($data_standard);
    }

    public function data_scatter_post($flag = false){ //昨日 前日 温湿度稳定系数
        $data = $this->db->select("c.mid,c.scatter_temp,c.scatter_humidity")
                         ->join("museum m","m.id=c.mid")
                         ->where("c.date >=",$this->start_time)
                         ->where("c.date <=",$this->end_time)
                         ->where("c.env_type",$this->env_type)
                         ->get("data_complex c")
                         ->result_array();
        $datas = array();
        foreach ($data as $value){
            $datas["scatter_temp"][$value["mid"]] = $value["scatter_temp"];
            $datas["scatter_humidity"][$value["mid"]] = $value["scatter_humidity"];
        }
        if($flag){
            return $datas;
        }
        $this->response($datas);
    }


    public function general_all_post(){
        $data_standard = $this->detail_standard_post(true);
        $data_scatter = $this->data_scatter_post(true);
        $general_standard = $this->general_one($data_standard);
        $general_scatter_temp = $this->general_one($data_scatter["scatter_temp"]);
        $general_scatter_humidity = $this->general_one($data_scatter["scatter_humidity"]);
        $this->response(array("standard"=>$general_standard,"scatter_temp"=>$general_scatter_temp,"scatter_humidity"=>$general_scatter_humidity));
    }

    protected function general_one($data){
        $calculate = calculate($data);
        $rs = array();
        $rs["less"] = $rs["equal"] = $rs["more"] = 0;
        $rs["attention"] = array();
        $rs["all"] = count($data);
        $rs["standard"] = $calculate["standard"];
        $rs["average"] = $calculate["average"];
        foreach ($data as $k => $value){
            $rs["museum"][] = array("name"=>$this->museum[$k],"data"=>$value,"distance"=>$value - $calculate["average"]);
            $z = $value - $calculate["average"] / $calculate["standard"];
            if($z < -2){
                $rs["attention"][] = $this->museum[$k];
            }
            if($value < $calculate["average"]){
                $rs["less"] ++;
            }elseif ($value == $calculate["average"]){
                $rs["equal"] ++;
            }else{
                $rs["more"] ++;
            }
        }

        return $rs;
    }


}