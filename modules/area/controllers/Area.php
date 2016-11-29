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

    private function detail_standard_get(){ //区域详情达标率
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
            $data_standard[$k] = $total?round(($total - $abnormal) / $total,2):0;
        }

        return $data_standard;
    }

    private function data_scatter_get(){
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

        return $datas;
    }


    public function general_all_get(){ //区域详情-达标与稳定概况
        $data_standard = $this->detail_standard_get();
        $data_scatter = $this->data_scatter_get();
        $general_standard = $this->general_one($data_standard,"standard");
        $general_scatter_temp = $this->general_one($data_scatter["scatter_temperature"],"scatter");
        $general_scatter_humidity = $this->general_one($data_scatter["scatter_humidity"],"scatter");
        $this->response(array("standard_scatter"=>$general_standard,"temperature_scatter"=>$general_scatter_temp,"humidity_scatter"=>$general_scatter_humidity));
    }

    protected function general_one($data,$type){
        $calculate = calculate($data);
        $rs = array();
        $rs["less"] = $rs["equal"] = $rs["more"] = 0;
        $rs["attention"] = array();
        $rs["all"] = count($data);
        $rs["standard"] = $calculate["standard"];
        $rs["average"] = $calculate["average"];
        $rs["max"] = max($data)*1.1;
        foreach ($data as $k => $value){
            $value = $value?$value:0;
            $rs["museum"][] = array("mid"=>$k,"name"=>$this->museum[$k],"data"=>$value,"distance"=>$value - $calculate["average"]);
            $z = $calculate["standard"]?($value - $calculate["average"]) / $calculate["standard"]:0;
            if($type == "standard" && $z < -2){
                $rs["attention"][] = $this->museum[$k];
            }elseif ($type == "scatter" && $z > 2){
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
        $arr_minmax = array();
        $waves = $waves_abnormal = $waves_status = $waves_abnormal_status = array();
        if (in_array($this->definite_time,array("week","month"))){ // 算本周or本月日波动，取最小值和最大值
            switch ($this->definite_time){
                case "week":
                    $day_num = date("w");
                    $date_arr = array();
                    while ($day_num - 1 > 0){
                        $date_arr[] = "D".date("Ymd",strtotime("-".($day_num-1)." day"));
                        $day_num --;
                    }
                    if(!empty($date_arr)){
                        $datas = $this->db->select("mid,wave,wave_status,param")
                            ->where("env_type",$this->env_type)
                            ->where_in("date",$date_arr)
                            ->get("data_envtype_param")
                            ->result_array();

                    }
                    break;
                case "month":
                    $date = "D".date("Ym")."%";
                    $datas = $this->db->select("mid,wave,wave_status,param")
                        ->where("env_type",$this->env_type)
                        ->where("date like",$date)
                        ->get("data_envtype_param")
                        ->result_array();
            }
            if(isset($datas) && !empty($datas)){
                foreach ($datas as $data){
                    if($data["wave"]){
                        list($w1,$w2,$w3,$w4) = explode(",",$data["wave"]);
                        $arr = sprintf("%04d",decbin($data["wave_status"]));
                        $waves[$data["mid"]][$data["param"]][] = $w1;
                        $waves[$data["mid"]][$data["param"]][] = $w2;
                        $waves_abnormal[$data["mid"]][$data["param"]][] = $w3;
                        $waves_abnormal[$data["mid"]][$data["param"]][] = $w4;
                        $waves_status[$data["mid"]][$data["param"]][] = $arr[0];
                        $waves_status[$data["mid"]][$data["param"]][] = $arr[1];
                        $waves_abnormal_status[$data["mid"]][$data["param"]][] = $arr[2];
                        $waves_abnormal_status[$data["mid"]][$data["param"]][] = $arr[3];
                    }
                }
            }
        }
        $all =  $this->db->select("p.*")
            ->join("data_envtype_param p","p.mid=m.id")
            ->where("p.date",$this->date)
            ->where("p.env_type",$this->env_type)
            ->get("museum m")
            ->result_array();
        $data_tables = array();
        foreach ($all as $item) {
            $arr_minmax[$item["param"]][] = $item["max"];
            $arr_minmax[$item["param"]][] = $item["min"];
            $arr = array(
                "mid"=>$item["mid"],
                "museum"=>$this->museum[$item["mid"]],
                "depid"=>$item["id"],
                "max"=>$item["max"],
                "min"=>$item["min"],
                "distance"=>$item["max"] - $item["min"],
                "middle"=>$item["middle"],
                "average"=>$item["average"],
                "count_abnormal"=>$item["count_abnormal"],
                "standard"=>$item["standard"],
                "compliance"=>$item["compliance"]
            );
            $data_tables[$item["param"]]["xdata"][] = $arr["museum"];
            $data_tables[$item["param"]]["ydistance"][] = $arr["distance"];
            $data_tables[$item["param"]]["ycompliance"][] = $arr["compliance"];
            $data_tables[$item["param"]]["ycount_abnormal"][] = $arr["count_abnormal"];
            $data_tables[$item["param"]]["yaverage"][] = $arr["average"];
            $data_tables[$item["param"]]["ystandard"][] = $arr["standard"];
            if($item["wave"]){
                list($w1,$w2,$w3,$w4) = explode(",",$item["wave"]);
                $data_tables[$item["param"]]["ywave"]["base"][] = $w1;
                $data_tables[$item["param"]]["ywave"]["add"][] = $w2 - $w1;
                $data_tables[$item["param"]]["ywave_normal"]["base"][] = $w3;
                $data_tables[$item["param"]]["ywave_normal"]["add"][] = $w4 - $w3;
                $arr["wave"] = array($w1,$w2);
                $arr["wave_normal"] = array($w3,$w4);
                if($item["wave_status"] !== null){
                    $arr["wave_status"] = sprintf("%04d",decbin($item["wave_status"]));
                }
            }else{
                if(array_key_exists($item["mid"],$waves) && array_key_exists($item["param"],$waves[$item["mid"]])){
                    $w1 = min($waves[$item["mid"]][$item["param"]]);
                    $w2 = max($waves[$item["mid"]][$item["param"]]);
                    $w3 = min($waves_abnormal[$item["mid"]][$item["param"]]);
                    $w4 = max($waves_abnormal[$item["mid"]][$item["param"]]);
                    $data_tables[$item["param"]]["ywave"]["base"][] = $w1;
                    $data_tables[$item["param"]]["ywave"]["add"][] = $w2 - $w1;
                    $data_tables[$item["param"]]["ywave_normal"]["base"][] = $w3;
                    $data_tables[$item["param"]]["ywave_normal"]["add"][] = $w4 - $w3;
                    $arr["wave"] = array($w1,$w2);
                    $arr["wave_normal"] = array($w3,$w4);
                    $status = "";
                    $arr_wave = array($w1,$w2);
                    $arr_wave_normal = array($w3,$w4);
                    foreach ($arr_wave as $wave){
                        $k = array_search($wave, $waves[$item["mid"]][$item["param"]]);
                            if(($k || $k == 0) && array_key_exists($k,$waves_status[$item["mid"]][$item["param"]])){
                                $status .= $waves_status[$item["mid"]][$item["param"]][$k];
                            }

                    }
                    foreach ($arr_wave_normal as $wave){
                        $k = array_search($wave, $waves_abnormal[$item["mid"]][$item["param"]]);
                            if(($k || $k == 0) && array_key_exists($k,$waves_abnormal_status[$item["mid"]][$item["param"]])){
                                $status .= $waves_abnormal_status[$item["mid"]][$item["param"]][$k];
                            }
                    }
                    $arr["wave_status"] = $status;
                }
            }

            $texture_data[$item["param"]]["list"][] = $arr;

        }
        foreach ($texture_data as $param=>$value){
            if(array_key_exists($param, $arr_minmax)){
                $texture_data[$param]["left"] = min($arr_minmax[$param])*0.9;
                $texture_data[$param]["right"] = max($arr_minmax[$param])*1.1;
            }
        }

        //print_r($texture_data);exit;

        foreach ($texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
                $data["table"] = array_key_exists($k,$data_tables)?$data_tables[$k]:array();
                $data["unit"] = $this->unit[$param];
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

    public function abnormal_get(){ //异常值获取
        $depid = $this->get("depid");
        $abnormal = array();
        if($depid){
            $abnormal = $this->db->select("date,time,equip_no,val")
                     ->where("depid",$depid)
                     ->get("data_abnormal")
                     ->result_array();
        }
        $this->response($abnormal);
    }

    public function wave_abnormal_get(){ //日波动异常获取
        $depid = $this->get("depid");
        $type = $this->get("type");
        $abnormal = array();
        if($depid && ($type || $type == 0)){
            $abnormal = $this->db->select("date,env_name,val")
                ->where("depid",$depid)
                ->where("type",$type)
                ->get("data_wave_abnormal")
                ->result_array();
        }
        $this->response($abnormal);
    }

    public function analysis_get(){
        $mids = $this->get("mids");
        if(!$mids){
            $this->response();
        }
        $x_standard = array("99.5%(含)~100%","99%(含)~99.5%","95%(含)~99%","<95%");
        $x_temperature = array("0%~4%(含)","4%~6%(含)","6%~7%(含)",">7.5%");
        $x_humidity = array("0%~2%(含)","2%~3%(含)","3%~3.5%(含)",">4%");
        $museum_standard = $museum_temperature = $museum_humidity = $counts_arr = $counts_rs = $legend = array();
        $mid_arr = explode(",",$mids);
        $counts = $this->db->select("mid,count_showcase")->get("data_base")->result_array();
        foreach ($counts as $count){
            $counts_arr[$count["mid"]] = $count["count_showcase"];
        }
        $data_standard = $this->detail_standard_get();
        $data_scatter = $this->data_scatter_get();
        $temperature = $data_scatter["scatter_temperature"];
        $humidity = $data_scatter["scatter_humidity"];
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid, $data_standard)){
                $data = array();//达标率柱状图数据
                if($data_standard[$mid] >= 0.995 && $data_standard[$mid]<= 1){
                    $data[] = $data_standard[$mid];
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid] >= 0.99 && $data_standard[$mid]< 0.995){
                    $data[] = $data_standard[$mid];
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid] >= 0.95 && $data_standard[$mid]< 0.99){
                    $data[] = $data_standard[$mid];
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid]< 0.95){
                    $data[] = $data_standard[$mid];
                }else{
                    $data[] = 0;
                }
                $museum_standard[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid,$temperature)){
                $data = array();//温度离散系数 柱状图数据
                if($temperature[$mid] > 0 && $temperature[$mid]<= 0.04){
                    $data[] = $temperature[$mid];
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid] >0.04 && $temperature[$mid]<= 0.06){
                    $data[] = $temperature[$mid];
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid] >0.06 && $temperature[$mid]<= 0.07){
                    $data[] = $temperature[$mid];
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid]> 0.075){
                    $data[] = $temperature[$mid];
                }else{
                    $data[] = 0;
                }
                $museum_temperature[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid,$humidity)){
                $data = array();//湿度离散系数 柱状图数据
                if($humidity[$mid] > 0 && $humidity[$mid]<= 0.02){
                    $data[] = $humidity[$mid];
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid] >0.02 && $humidity[$mid]<= 0.03){
                    $data[] = $humidity[$mid];
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid] >0.03 && $humidity[$mid]<= 0.035){
                    $data[] = $humidity[$mid];
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid]> 0.04){
                    $data[] = $humidity[$mid];
                }else{
                    $data[] = 0;
                }
                $museum_humidity[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid, $counts_arr)){
                $counts_rs[] = array("name"=>$this->museum[$mid],"count"=>$counts_arr[$mid]);//展柜数量
            }

            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        $indicator_compliance = array(
            array("name"=>"全参数平均达标率","max"=>1),
            array("name"=>"温度","max"=>1),
            array("name"=>"湿度","max"=>1),
            array("name"=>"光照","max"=>1),
            array("name"=>"紫外","max"=>1),
            array("name"=>"有机挥发物","max"=>1)
        );
        $indicator_scatter = array(
            array("name"=>"全参数平均离散系数","max"=>0.15),
            array("name"=>"温度","max"=>0.15),
            array("name"=>"湿度","max"=>0.15),
            array("name"=>"光照","max"=>0.15),
            array("name"=>"紫外","max"=>0.15),
            array("name"=>"有机挥发物","max"=>0.15)
        );
        $datas = $this->depart_table($mid_arr);
        $rs = array(
            "compliance"=>array("xdata"=>$x_standard,"legend"=>$legend,"data"=>$museum_standard),
            "temperature"=>array("xdata"=>$x_temperature,"legend"=>$legend,"data"=>$museum_temperature),
            "humidity"=>array("xdata"=>$x_humidity,"legend"=>$legend,"data"=>$museum_humidity),
            "counts"=>$counts_rs,
            "all_compliance"=>array("legend"=>$legend,"indicator"=>$indicator_compliance,"data"=>$datas["compliance"]),
            "all_scatter"=>array("legend"=>$legend,"indicator"=>$indicator_scatter,"data"=>$datas["scatter"])
        );
        $this->response($rs);

    }

    private function depart_table($mid_arr = array()){
        $data = array();
        $data_complex = $this->db->select("c.*")
            ->join("data_complex c","c.mid=m.id")
            ->where("c.env_type",$this->env_type)
            ->where("c.date",$this->date)
            ->where_in("c.mid",$mid_arr)
            ->get("museum m")
            ->result_array();
        foreach ($mid_arr as $mid){
            foreach ($data_complex as $item) {
                if($item["mid"] == $mid){
                    $standard = $scatter = array();
                    $standard[] = $item["temperature_total"]?round(($item["temperature_total"] - $item["temperature_abnormal"])/$item["temperature_total"]):0;
                    $standard[] = $item["humidity_total"]?round(($item["humidity_total"] - $item["humidity_abnormal"])/$item["humidity_total"]):0;
                    $standard[] = $item["light_total"]?round(($item["light_total"] - $item["light_abnormal"])/$item["light_total"]):0;
                    $standard[] = $item["uv_total"]?round(($item["uv_total"] - $item["uv_abnormal"])/$item["uv_total"]):0;
                    $standard[] = $item["voc_total"]?round(($item["voc_total"] - $item["voc_abnormal"])/$item["voc_total"]):0;
                    $scatter[] = $item["scatter_temperature"]?$item["scatter_temperature"]:0;
                    $scatter[] = $item["scatter_humidity"]?$item["scatter_humidity"]:0;
                    $scatter[] = $item["scatter_light"]?$item["scatter_light"]:0;
                    $scatter[] = $item["scatter_uv"]?$item["scatter_uv"]:0;
                    $scatter[] = $item["scatter_voc"]?$item["scatter_voc"]:0;
                    $average_standard = round(array_sum($standard)/sizeof($standard),2);
                    $average_scatter = round(array_sum($scatter)/sizeof($scatter),2);
                    array_unshift($standard,$average_standard);
                    array_unshift($scatter,$average_scatter);
                    $data["compliance"][] = array("name"=>$this->museum[$mid],"value"=>$standard);
                    $data["scatter"][] = array("name"=>$this->museum[$mid],"value"=>$scatter);
                    break;
                }
            }
        }
        return $data;

    }

}