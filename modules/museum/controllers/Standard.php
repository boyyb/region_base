<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 12:57
 */
class Standard extends MY_Controller{ //博物馆详情-达标与稳定概况

    private $mid = 0;
    private $count = 0;
    private $total = 0;
    private $abnormal = 0;
    private $standard_data = array();
    private $texture_total = array();
    private $texture_abnormal = array();
    function __construct(){
        parent::__construct();
        $this->mid = $this->get("mid");
        $this->handle();
    }

    private function handle(){
        $standard = $this->db->select("c.*,e.material_humidity,e.material_light")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$this->mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_compliance c")
            ->result_array();
        $abnormal = $total = 0;
        $this->count = count($standard);
        $texture_type = $texture_total = $texture_abnormal = array();
        $standard1 = $standard2 = $standard3 = $standard4 = 0;
        foreach ($this->texture as $k => $t){
            foreach ($t as $p=>$t1){
                $texture_type[$k] = $p;
                $texture_total[$k] = $texture_abnormal[$k] = 0;
            }
        }
        foreach ($standard as $item) {
            $total_items = $abnormal_items = 0;
            foreach ($this->env_param as $param){
                $total_item = $item[$param."_total"];
                $abnormal_item = $item[$param."_abnormal"];
                if($total_item){
                    $total_items += $total_item;
                    $total += $total_item;
                }
                if($abnormal_item){
                    $abnormal += $abnormal_item;
                    $abnormal_items += $abnormal_item;
                }
            }

            $standard_percent_item = round(($total_items - $abnormal_items) / $total_items,4);
            if($standard_percent_item>=0.995 && $standard_percent_item<=1){
                $standard1 ++;
            }elseif ($standard_percent_item>=0.99 && $standard_percent_item<0.995){
                $standard2 ++;
            }elseif ($standard_percent_item>=0.95 && $standard_percent_item<0.99){
                $standard3 ++;
            }else{
                $standard4++;
            }

            foreach ($texture_type as $k => $value){
                if($k == $item["material_humidity"]){
                    $texture_total[$item["material_humidity"]] += $item[$texture_type[$item["material_humidity"]]."_total"];
                    $texture_abnormal[$item["material_humidity"]] += $item[$texture_type[$item["material_humidity"]]."_abnormal"];
                }else if($k == $item["material_light"]){
                    $texture_total[$item["material_light"]] += $item[$texture_type[$item["material_light"]]."_total"];
                    $texture_abnormal[$item["material_light"]] += $item[$texture_type[$item["material_light"]]."_abnormal"];
                }else if(!in_array($value,array("humidity","light"))){
                    $texture_total[$k] += $item[$value."_total"];
                    $texture_abnormal[$k] += $item[$value."_abnormal"];
                }

            }

        }
        $this->total = $total;
        $this->abnormal = $abnormal;
        $this->texture_total = $texture_total;
        $this->texture_abnormal = $texture_abnormal;
        $this->standard_data = array(
            array("name"=>"99.5%(含)~100%","value"=>$standard1),
            array("name"=>"99%(含)~99.5%","value"=>$standard2),
            array("name"=>"95%(含)~99%","value"=>$standard3),
            array("name"=>"<95%","value"=>$standard4)
        );
    }


    public function museum_general_get(){ //博物馆总体概况
        $standard_percent = round(($this->total - $this->abnormal) / $this->total,2); //达标率
        $scatter = $this->db->select("scatter_temp,scatter_humidity,is_wave_abnormal,is_value_abnormal")
            ->where("date >=",$this->start_time)
            ->where("date <=",$this->end_time)
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->get("data_complex")
            ->row_array();
        $scatter["standard_percent"] = $standard_percent;
        $scatter["env_count"] = $this->count;
        $this->response($scatter);
    }

    public function standard_percent_get(){ //达标率统计概况
        $this->response($this->standard_data);
    }

    public function temperature_scatter_get(){ //稳定性统计概况-温度
        $temperature_scatter1 = $temperature_scatter2 = $temperature_scatter3 = $temperature_scatter4 = 0;
        $env_complex = $this->db->select("c.temperature_scatter")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$this->mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_complex c")
            ->result_array();

        foreach ($env_complex as $complex){
            $temperature_scatter = $complex["temperature_scatter"];
            if($temperature_scatter){
                if($temperature_scatter>0 && $temperature_scatter<=0.04){
                    $temperature_scatter1 ++;
                }elseif ($temperature_scatter>0.04 && $temperature_scatter<=0.06){
                    $temperature_scatter2 ++;
                }elseif ($temperature_scatter>0.06 && $temperature_scatter<=0.07){
                    $temperature_scatter3 ++;
                }elseif ($temperature_scatter>0.075){
                    $temperature_scatter4 ++;
                }
            }
        }
        $temperature_scatter_data = array(
            array("name"=>"0%~4%(含)","value"=>$temperature_scatter1),
            array("name"=>"4%~6%(含)","value"=>$temperature_scatter2),
            array("name"=>"6%~7%(含)","value"=>$temperature_scatter3),
            array("name"=>">7.5%","value"=>$temperature_scatter4)
        );
        $this->response($temperature_scatter_data);
    }

    public function humidity_scatter_get(){ //稳定性统计概况-湿度
        $humidity_scatter1 = $humidity_scatter2 = $humidity_scatter3 = $humidity_scatter4 = 0;
        $env_complex = $this->db->select("c.humidity_scatter")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$this->mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_complex c")
            ->result_array();

        foreach ($env_complex as $complex){
            $humidity_scatter = $complex["humidity_scatter"];
            if($humidity_scatter){
                if($humidity_scatter>0 && $humidity_scatter<=0.02){
                    $humidity_scatter1 ++;
                }elseif ($humidity_scatter>0.02 && $humidity_scatter<=0.03){
                    $humidity_scatter2 ++;
                }elseif ($humidity_scatter>0.03 && $humidity_scatter<=0.035){
                    $humidity_scatter3 ++;
                }elseif ($humidity_scatter>0.04){
                    $humidity_scatter4 ++;
                }
            }
        }
        $humidity_scatter_data = array(
            array("name"=>"0%~2%(含)","value"=>$humidity_scatter1),
            array("name"=>"2%~3%(含)","value"=>$humidity_scatter2),
            array("name"=>"3%~3.5%(含)","value"=>$humidity_scatter3),
            array("name"=>">4%","value"=>$humidity_scatter4)
        );
        $this->response($humidity_scatter_data);
    }

    public function env_list_get(){ //环境指标数据列表
        $params = $list = array();
        $env_param = $this->db->select("*")
            ->where("date >=",$this->start_time)
            ->where("date <=",$this->end_time)
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->get("data_envtype_param")
            ->result_array();
        foreach ($env_param as $value){
            $params[$value["param"]] = $value;
        }
        foreach ($this->texture as $k => $arr){
            $data = array();
            foreach ($arr as $p=>$v){
                $data["type"] = $p;
                if(!empty($v)){
                    $data["texture"] = implode("、",$v);
                }
            }
            if(array_key_exists($k, $params)){
                $data["max"] = $params[$k]["max"];
                $data["min"] = $params[$k]["min"];
                $data["max2"] = $params[$k]["max2"];
                $data["min2"] = $params[$k]["min2"];
                $data["middle"] = $params[$k]["middle"];
                $data["average"] = $params[$k]["average"];
                $data["standard"] = $params[$k]["standard"];
                $data["count_abnormal"] = $params[$k]["count_abnormal"];
                $data["distance"] = $data["max"] - $data["min"];
                $data["standard_percent"] = $this->texture_total[$k]?round(($this->texture_total[$k] - $this->texture_abnormal[$k]) / $this->texture_total[$k],2):0;
            }
            $list[] = $data; //数据列表
        }
        $this->response($list);
    }
}