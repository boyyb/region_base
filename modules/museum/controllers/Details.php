<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 11:34
 */
class Details extends MY_Controller{ //数据统计详情

    private $mid = 0;
    function __construct()
    {
        parent::__construct();
        $this->mid = $this->get("mid");
    }

    public function museum_details_get(){ //达标率、稳定系数统计概况
        $standard_arr = $names_arr = $eid_arr = $data_pid = array();
        $names = $this->db->select("sourceid,name,id,pid")->where("mid",$this->mid)->get("data_env")->result_array();
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
            ->where("e.mid",$this->mid)
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->get("data_env_compliance c")
            ->result_array();

        $scatter = $this->db->select("c.*,e.sourceid,e.pid")
            ->join("data_env e","e.id=c.eid")
            ->where("e.env_type",$this->env_type)
            ->where("e.mid",$this->mid)
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

        $standard_pencent = $this->deal($standard_arr, $names_arr, $data_pid, $eid_arr);//达标率

        $temperature_scatter_arr = $humidity_scatter_arr = array();
        foreach ($scatter as $s){
            $temperature_scatter_arr[$s["sourceid"]] = $s["temperature_scatter"];
            $humidity_scatter_arr[$s["sourceid"]] = $s["humidity_scatter"];
        }
        $temperature_scatter = $this->deal($temperature_scatter_arr, $names_arr, $data_pid, $eid_arr);//温度-离散系数
        $humidity_scatter = $this->deal($humidity_scatter_arr, $names_arr, $data_pid, $eid_arr);//湿度-离散系数
        $this->response(array("standard_pencent"=>$standard_pencent,"temperature_scatter"=>$temperature_scatter,"humidity_scatter"=>$humidity_scatter));
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
    
    public function museum_details_envnotexture_get(){ //环境指标统计详情（不区分材质）
        $names_arr = $eid_arr = $data_pid = $result = $data = array();
        $names = $this->db->select("sourceid,name,id,pid")->where("mid",$this->mid)->get("data_env")->result_array();
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
            ->where("e.mid",$this->mid)
            ->where("p.date >=",$this->start_time)
            ->where("p.date <=",$this->end_time)
            ->where_in("p.param",$param)
            ->get("data_env_param p")
            ->result_array();
        foreach ($env_details as $v){
            $data[$v["param"]][] = $v;
        }
        foreach ($param as $p){
            if(array_key_exists($p, $data)){
                $result[$p] = $this->deal_envnotexture($data[$p],$names_arr,$data_pid,$eid_arr);
            }
        }

        $this->response($result);
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

    

}