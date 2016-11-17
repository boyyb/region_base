<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 11:01
 */

class Area extends MY_Controller{

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
            ->join("data_complex c","c.mid=m.id")
            ->where("c.env_type",$this->env_type)
            ->where("c.date",$this->date)
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

    public function data_scatter_get($flag = false){ //温湿度稳定系数
        $data = $this->db->select("c.mid,c.scatter_temperature,c.scatter_humidity")
            ->join("museum m","m.id=c.mid")
            ->where("c.date",$this->date)
            ->where("c.env_type",$this->env_type)
            ->get("data_complex c")
            ->result_array();
        $datas["scatter_temperature"] = $datas["scatter_humidity"] = array();
        foreach ($data as $value){
            $datas["scatter_temperature"][$value["mid"]] = $value["scatter_temperature"];
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
        $general_scatter_temp = $this->general_one($data_scatter["scatter_temperature"]);
        $general_scatter_humidity = $this->general_one($data_scatter["scatter_humidity"]);
        $this->response(array("standard"=>$general_standard,"scatter_temperature"=>$general_scatter_temp,"scatter_humidity"=>$general_scatter_humidity));
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
            $z = $calculate["standard"]?($value - $calculate["average"]) / $calculate["standard"]:0;
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
        $texture_data = $rs = array();
        if($this->env_type == "展厅"){
            $texture = $this->texture["common"]+$this->texture["zt"];
        }else{
            $texture = $this->texture["common"]+$this->texture["zgkf"]+$this->texture["hh"];
        }

        $all =  $this->db->select("p.*")
            ->join("data_envtype_param p","p.mid=m.id")
            ->where("p.date",$this->date)
            ->where("p.env_type",$this->env_type)
            ->get("museum m")
            ->result_array();

        foreach ($all as $item) {
            $arr = array(
                "mid"=>$item["mid"],
                "museum"=>$this->museum[$item["mid"]],
                "max"=>$item["max"],
                "min"=>$item["min"],
                "distance"=>$item["max"] - $item["min"],
                "middle"=>$item["middle"],
                "average"=>$item["average"],
                "count_abnormal"=>$item["count_abnormal"],
                "standard"=>$item["standard"],
                "compliance"=>100*$item["compliance"]."%"
            );
            if($item["wave"]){
                list($w1,$w2,$w3,$w4) = explode(",",$item["wave"]);
                $arr["wave"] = $w1." - ".$w2;
                $arr["wave_normal"] = $w3." - ".$w4;
                if($item["wave_status"] !== null){
                    $arr["wave_status"] = sprintf("%04d",decbin($item["wave_status"]));
                }
            }

            $texture_data[$item["param"]][] = $arr;
        }

        //print_r($texture_data);exit;

        foreach ($texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
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