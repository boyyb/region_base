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

    


}