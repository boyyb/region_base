<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/10/31
 * Time: 10:24
 */
class General extends MY_Controller{

    function __construct()
    {
        parent::__construct();
    }

    public function detail_standard_get($flag = false){ //区域详情达标率
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

    public function data_scatter_get($flag = false){ //昨日 前日 温湿度稳定系数
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


    public function general_all_get(){ //区域详情-达标与稳定概况
        $data_standard = $this->detail_standard_get(true);
        $data_scatter = $this->data_scatter_get(true);
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
            $z = ($value - $calculate["average"]) / $calculate["standard"];
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

    public function param_details_get(){ //区域详情-环境指标统计详情
        $texture_data = $compliance = $standard= $rs = array();
        $params = array("temperature","humidity","light","uv","voc");
        $all =  $this->db->select("p.*")
                         ->join("data_envtype_param p","p.mid=m.id")
                         ->where("p.date >=",$this->start_time)
                         ->where("p.date <=",$this->end_time)
                         ->where("p.env_type",$this->env_type)
                         ->get("museum m")
                         ->result_array();

        $env = $this->db->select("c.*,e.mid")
            ->join("data_env_compliance c","c.eid=e.id")
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->where("e.env_type",$this->env_type)
            ->get("data_env e")
            ->result_array();

        foreach ($env as $value){
            $compliance[$value["mid"]][] = $value;
        }

        foreach ($compliance as $mid => $value){
            $standard[$mid] = array_key_exists($mid, $standard)?$standard[$mid]:array();
            foreach ($value as $v) {
                foreach ($params as $p) {
                    if($v[$p."_total"]){
                        $standard[$mid][$p."_total"] = array_key_exists($p."_total", $standard[$mid])?$standard[$mid][$p."_total"]:0;
                        $standard[$mid][$p."_total"] += $v[$p."_total"];
                    }
                    if($v[$p."_abnormal"]){
                        $standard[$mid][$p."_abnormal"] = array_key_exists($p."_abnormal", $standard[$mid])?$standard[$mid][$p."_abnormal"]:0;
                        $standard[$mid][$p."_abnormal"] += $v[$p."_abnormal"];
                    }
                }
            }

        }

        foreach ($all as $item) {
            $texture_data[$item["param"]][] = array(
                "mid"=>$item["mid"],
                "museum"=>$this->museum[$item["mid"]],
                "max"=>$item["max"],
                "min"=>$item["min"],
                "max2"=>$item["max2"],
                "min2"=>$item["min2"],
                "distance"=>$item["max"] - $item["min"],
                "middle"=>$item["middle"],
                "average"=>$item["average"],
                "count_abnormal"=>$item["count_abnormal"],
                "standard"=>$item["standard"]
            );
        }

        //print_r($texture_data);exit;

        foreach ($this->texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
                if($data){
                    foreach ($data as $key => $value){
                        if(array_key_exists($param."_total",$standard[$value["mid"]]) && $total = $standard[$value["mid"]][$param."_total"]){
                            $abnormal = array_key_exists($param."_abnormal",$standard[$value["mid"]])?$standard[$value["mid"]][$param."_abnormal"]:0;
                            $data[$key]["standard_percent"] = round(($total - $abnormal) / $total,2);
                        }
                    }
                }
                if(!empty($tt)){
                    $rs[$param][] = array(
                                            "texture"=>implode("、",$tt),
                                            "data"=>$data
                                          );
                }else{
                    $rs[$param] = $data;
                }
            }
        }

        $this->response($rs);
    }

    public function museum_general_get($mid = ''){ //博物馆详情-达标与稳定概况
        $standard = $this->db->select("c.*,e.material_humidity,e.material_light")
                             ->join("data_env e","e.id=c.eid")
                             ->where("e.env_type",$this->env_type)
                             ->where("e.mid",$mid)
                             ->where("c.date >=",$this->start_time)
                             ->where("c.date <=",$this->end_time)
                             ->get("data_env_compliance c")
                             ->result_array();
        $abnormal = $total = 0;

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
        $standard_percent = round(($total - $abnormal) / $total,2); //达标率

        $scatter = $this->db->select("scatter_temp,scatter_humidity,is_wave_abnormal,is_value_abnormal")
                            ->where("date >=",$this->start_time)
                            ->where("date <=",$this->end_time)
                            ->where("env_type",$this->env_type)
                            ->where("mid",$mid)
                            ->get("data_complex")
                            ->row_array();
        $scatter["standard_percent"] = $standard_percent; //博物馆总体概况

        $params = $list = array();
        $env_param = $this->db->select("*")
            ->where("date >=",$this->start_time)
            ->where("date <=",$this->end_time)
            ->where("env_type",$this->env_type)
            ->where("mid",$mid)
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
                $data["standard_percent"] = $texture_total[$k]?round(($texture_total[$k] - $texture_abnormal[$k]) / $texture_total[$k],2):0;
            }
            $list[] = $data; //数据列表
        }

        $standard_data = array( //达标率统计概况
            array("name"=>"99.5%(含)~100%","value"=>$standard1),
            array("name"=>"99%(含)~99.5%","value"=>$standard2),
            array("name"=>"95%(含)~99%","value"=>$standard3),
            array("name"=>"<95%","value"=>$standard4)
        );
        $env_count = count($standard); //环境数量

        $temperature_scatter1 = $temperature_scatter2 = $temperature_scatter3 = $temperature_scatter4 = 0;
        $humidity_scatter1 = $humidity_scatter2 = $humidity_scatter3 = $humidity_scatter4 = 0;
        $env_complex = $this->db->select("c.temperature_scatter,c.humidity_scatter")
                                ->join("data_env e","e.id=c.eid")
                                ->where("e.env_type",$this->env_type)
                                ->where("e.mid",$mid)
                                ->where("c.date >=",$this->start_time)
                                ->where("c.date <=",$this->end_time)
                                ->get("data_env_complex c")
                                ->result_array();

        foreach ($env_complex as $complex){
            $temperature_scatter = $complex["temperature_scatter"];
            $humidity_scatter = $complex["humidity_scatter"];
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
        $temperature_scatter_data = array( //稳定性统计概况-温度
            array("name"=>"0%~4%(含)","value"=>$temperature_scatter1),
            array("name"=>"4%~6%(含)","value"=>$temperature_scatter2),
            array("name"=>"6%~7%(含)","value"=>$temperature_scatter3),
            array("name"=>">7.5%","value"=>$temperature_scatter4)
        );
        $humidity_scatter_data = array( //稳定性统计概况-湿度
            array("name"=>"0%~2%(含)","value"=>$humidity_scatter1),
            array("name"=>"2%~3%(含)","value"=>$humidity_scatter2),
            array("name"=>"3%~3.5%(含)","value"=>$humidity_scatter3),
            array("name"=>">4%","value"=>$humidity_scatter4)
        );

        print_r($temperature_scatter_data);
        print_r($humidity_scatter_data);
        //$this->response($standard_data);
    }

    public function museum_details_get($mid){
        $standard_arr = $names_arr = $eid_arr = $data_pid = array();
        $names = $this->db->select("sourceid,name,id,pid")->where("mid",$mid)->get("data_env")->result_array();
        foreach ($names as $name){
            if($name["sourceid"]){
                $names_arr[$name["sourceid"]] = $name["name"];
                $eid_arr[$name["sourceid"]] = $name["id"];
                $data_pid[$name["sourceid"]] = $name["pid"];
            }
        }
        $standard = $this->db->select("c.*,e.material_humidity,e.material_light,e.sourceid,e.pid")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_compliance c")
            ->result_array();

        $scatter = $this->db->select("c.*,e.sourceid,e.pid")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_complex c")
            ->result_array();

        foreach ($standard as $s){
            $total= $abnormal = 0;
            foreach ($this->env_param as $p){
                if($s[$p."_total"]){
                    $total += $s[$p."_total"];
                }
                if($s[$p."_abnormal"]){
                    $abnormal += $s[$p."_abnormal"];
                }
            }
            if($total){
                $standard_arr[$s["sourceid"]] = round(($total - $abnormal) / $total,2);
            }
        }

//        $standard_pencent = $this->deal($standard_arr, $names_arr, $data_pid, $eid_arr);//达标率
//
//        //print_r($standard_pencent);
//        $temperature_scatter_arr = $humidity_scatter_arr = array();
//        foreach ($scatter as $s){
//            $temperature_scatter_arr[$s["sourceid"]] = $s["temperature_scatter"];
//            $humidity_scatter_arr[$s["sourceid"]] = $s["humidity_scatter"];
//        }
//        $temperature_scatter = $this->deal($temperature_scatter_arr, $names_arr, $data_pid, $eid_arr);//温度-离散系数
//        $humidity_scatter = $this->deal($humidity_scatter_arr, $names_arr, $data_pid, $eid_arr);//湿度-离散系数
//        //print_r($humidity_scatter);



    }

    public function museum_details_envnotexture_get($mid){
        $names_arr = $eid_arr = $data_pid = array();
        $names = $this->db->select("sourceid,name,id,pid")->where("mid",$mid)->get("data_env")->result_array();
        foreach ($names as $name){
            if($name["sourceid"]){
                $names_arr[$name["sourceid"]] = $name["name"];
                $eid_arr[$name["sourceid"]] = $name["id"];
                $data_pid[$name["sourceid"]] = $name["pid"];
            }
        }
        $param = array("temperature","uv","voc");
        $env_details = $this->db->select("e.sourceid,p.*")
            ->join("data_env e","e.id = p.eid" )
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$mid)
            ->where("p.date >=",$this->start_time)
            ->where("p.date <=",$this->end_time)
            ->where_in("p.param",$param)
            ->get("data_env_param p")
            ->result_array();
        $data = $this->deal_envnotexture($env_details,$names_arr,$data_pid,$eid_arr);
        print_r($data);
    }

    private function deal_envnotexture($arr,$names_arr,$data_pid,$eid_arr){
        $data = array();
        foreach ($arr as $detail){
            //$env_details_rs[$detail["param"]][] =
            if($this->env_type == "展柜"){ //展柜要显示上级环境
                if($detail["sourceid"] && array_key_exists($detail["sourceid"],$data_pid)){
                    $data["zg"][$data_pid[$detail["sourceid"]]]["name"] = $names_arr[$data_pid[$detail["sourceid"]]];
                    $data["zg"][$data_pid[$detail["sourceid"]]]["eid"] = $eid_arr[$data_pid[$detail["sourceid"]]];
                    $data["zg"][$data_pid[$detail["sourceid"]]]["data"][] = array(
                        "name"=>$names_arr[$detail["sourceid"]],
                        "eid"=>$eid_arr[$detail["sourceid"]] ,
                        "max" => $detail["max"],
                        "min" => $detail["min"],
                        "max2" => $detail["max2"],
                        "min2" => $detail["min2"],
                        "middle" => $detail["middle"],
                        "average" => $detail["average"],
                        "standard" => $detail["standard"],
                        "count_abnormal" => $detail["count_abnormal"],
                        "distance" => $detail["max"] - $detail["min"]
                    );
                }

            }else{
                $data["ztkf"][] = array(
                    "name"=>$names_arr[$detail["sourceid"]],
                    "eid"=>$eid_arr[$detail["sourceid"]] ,
                    "max" => $detail["max"],
                    "min" => $detail["min"],
                    "max2" => $detail["max2"],
                    "min2" => $detail["min2"],
                    "middle" => $detail["middle"],
                    "average" => $detail["average"],
                    "standard" => $detail["standard"],
                    "count_abnormal" => $detail["count_abnormal"],
                    "distance" => $detail["max"] - $detail["min"]
                );
            }
        }
        return $data;
    }

    private function deal($arr,$names_arr,$data_pid,$eid_arr){ //数据处理
        $calculate = calculate($arr);
        $more = $less = $equal = 0;
        $attention = $data = array();
        foreach ($arr as $k => $s){
            $z = ($s - $calculate["average"]) / $calculate["standard"];
            if($z < -2){
                $attention[] = $names_arr[$k];
            }
            if($this->env_type == "展柜"){ //展柜要显示上级环境
                if(array_key_exists($k,$data_pid)){
                    $data["zg"][$data_pid[$k]]["name"] = $names_arr[$data_pid[$k]];
                    $data["zg"][$data_pid[$k]]["eid"] = $eid_arr[$data_pid[$k]];
                    $data["zg"][$data_pid[$k]]["data"][] = array(
                        "name"=>$names_arr[$k],
                        "data"=>$s,
                        "eid"=>$eid_arr[$k] ,
                        "distance"=>$s - $calculate["average"]
                    );
                }

            }else{
                $data["ztkf"][] = array(
                    "name"=>$names_arr[$k],
                    "data"=>$s,
                    "eid"=>$eid_arr[$k] ,
                    "distance"=>$s - $calculate["average"]
                );
            }
            if($s > $calculate["average"]){
                $more++;
            }elseif ($s == $calculate["average"]){
                $equal++;
            }else{
                $less++;
            }
        }

        $result = array(
            "more"=>$more,
            "less"=>$less,
            "equal"=>$equal,
            "all"=>count($arr),
            "attention"=>$attention,
            "standard"=>$calculate["standard"],
            "average"=>$calculate["average"],
            "data"=>$data
        );
        return $result;
    }

}