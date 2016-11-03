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
    protected $texture = array();
   
    
    function __construct()
    {
        parent::__construct();
        $this->load->helper(array("calculate"));
        $this->load->config("texture");
        $this->texture = config_item("texture");
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


    public function general_all_post(){ //区域详情-达标与稳定概况
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

    public function param_details_post(){ //区域详情-环境指标统计详情
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

    public function museum_general_post($mid = ''){ //博物馆详情-达标与稳定概况
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
        foreach ($this->texture as $k => $t){
            foreach ($t as $p=>$t1){
                $texture_type[$k] = $p;
                $texture_total[$k] = $texture_abnormal[$k] = 0;
            }
        }

        foreach ($standard as $item) {
            foreach ($this->env_param as $param){
                if($item[$param."_total"]){
                    $total += $item[$param."_total"];
                }
                if($item[$param."_abnormal"]){
                    $abnormal += $item[$param."_abnormal"];
                }
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
        $scatter["standard_percent"] = $standard_percent;

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
                $data["middle"] = $params[$k]["middle"];
                $data["average"] = $params[$k]["average"];
                $data["standard"] = $params[$k]["standard"];
                $data["count_abnormal"] = $params[$k]["count_abnormal"];
                $data["distance"] = $data["max"] - $data["min"];
                $data["standard_percent"] = $texture_total[$k]?round(($texture_total[$k] - $texture_abnormal[$k]) / $texture_total[$k],2):0;
            }
            $list[] = $data;
        }
        print_r($list);
    }

}