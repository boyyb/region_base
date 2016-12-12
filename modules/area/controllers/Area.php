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

    private function detail_standard(){ //区域详情达标率
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
            $data_standard[$k] = $total?round(($total - $abnormal) / $total,4):0;
        }

        return $data_standard;
    }

    private function data_scatter(){
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
        $data_standard = $this->detail_standard();
        $data_scatter = $this->data_scatter();
        $general_standard = $this->general_one($data_standard,"standard");
        $general_scatter_temp = $this->general_one($data_scatter["scatter_temperature"],"scatter");
        $general_scatter_humidity = $this->general_one($data_scatter["scatter_humidity"],"scatter");
        $this->response(array("standard_scatter"=>$general_standard,"temperature_scatter"=>$general_scatter_temp,"humidity_scatter"=>$general_scatter_humidity));
    }

    public function area_compliance_get(){ //区域详情-达标率
        $data_standard = $this->detail_standard();
        $general_standard = $this->general_one($data_standard,"standard");
        $this->response($general_standard);
    }

    public function temperature_scatter_get(){ //区域详情-温度稳定概况
        //$type = $this->get("type");
        $type = "temperature";
        if(!$type){
            $this->response(array("error"=>"缺少type参数"));
        }
        $data_scatter = $this->data_scatter();
        if(!array_key_exists("scatter_".$type, $data_scatter)){
            $this->response(array("error"=>"type参数错误"));
        }
        $scatter = $this->general_one($data_scatter["scatter_".$type],"scatter");
        $this->response($scatter);
    }

    public function humidity_scatter_get(){ //区域详情-湿度稳定概况
        if($this->env_type == "展厅"){
            $param = array("10");
        }else{
            $param = array("1","2","3","12");
        }
        $humidity = $this->db->select("p.mid,p.standard,p.average")
                             ->join("data_envtype_param p","m.id=p.mid")
                             ->where("p.date",$this->date)
                             ->where("p.env_type",$this->env_type)
                             ->where_in("p.param",$param)
                             ->get("museum m")
                             ->result_array();
        $data = $data_average = array();
        foreach ($humidity as $value){
            if($value["average"]){
                $data[$value["mid"]][] = round($value["standard"] / $value["average"],2);
            }
        }

        foreach ($data as $key => $value){
            $data_average[$key] = round(array_sum($value) / sizeof($value),2);
        }
        $scatter = $this->general_one($data_average,"scatter");
        $this->response($scatter);
    }

    protected function general_one($data,$type){
        $calculate = calculate($data);
        $rs = array();
        $rs["less"] = $rs["equal"] = $rs["more"] = 0;
        $rs["attention"] = array();
        $all = 0;
        $rs["standard"] = $calculate["standard"];
        $rs["average"] = $calculate["average"];
        if($type == "standard"){
            $rs["max"] = 1;
        }elseif ($type == "scatter"){
            $rs["max"] = $data?max($data)*1.1:0;
        }
        foreach ($this->museum as $k => $name){
            $value = (array_key_exists($k,$data) && $data[$k])?$data[$k]:0;
            $distance = 0;
            if($value){
                $all ++;
                $distance = $value - $calculate["average"];
                $z = $calculate["standard"]?($value - $calculate["average"]) / $calculate["standard"]:0;
                if($type == "standard" && $z < -2){
                    $rs["attention"][] = $this->museum[$k];
                }elseif ($type == "scatter" && $z > 2){
                    $rs["attention"][] = $this->museum[$k];
                }
            }
            $rs["museum"][] = array("mid"=>$k,"name"=>$name,"data"=>$value,"distance"=>$distance);
            if($value && $value < $calculate["average"]){
                $rs["less"] ++;
            }elseif ($value && $value == $calculate["average"]){
                $rs["equal"] ++;
            }else if($value){
                $rs["more"] ++;
            }
        }
        $rs["all"] = $all;
        return $rs;
    }

    public function param_table_get(){ //区域详情-环境指标统计详情-图表数据
        $k = $this->get("key"); //材质对应编号
        $table = $this->get("table"); //表类型
        if(!$k || !$table){
            $this->response(array("error"=>"缺少必要参数"));
        }
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
            $arr = array(
                "museum"=>$this->museum[$item["mid"]],
                "distance"=>$item["max"] - $item["min"],
                "average"=>$item["average"],
                "count_abnormal"=>$item["count_abnormal"],
                "standard"=>$item["standard"],
                "compliance"=>$item["compliance"]*100
            );
            $data_tables[$item["param"]]["xdata"][] = $arr["museum"];
            $data_tables[$item["param"]]["distance"][] = $arr["distance"];
            $data_tables[$item["param"]]["compliance"][] = $arr["compliance"];
            $data_tables[$item["param"]]["count_abnormal"][] = $arr["count_abnormal"];
            $data_tables[$item["param"]]["average"][] = $arr["average"];
            $data_tables[$item["param"]]["standard"][] = $arr["standard"];
            $arr["wave"] = $arr["wave_normal"] = array();
            if($item["wave"]){
                list($w1,$w2,$w3,$w4) = explode(",",$item["wave"]);
                $data_tables[$item["param"]]["wave"]["base"][] = $w1;
                $data_tables[$item["param"]]["wave"]["add"][] = $w2 - $w1;
                $data_tables[$item["param"]]["wave_normal"]["base"][] = $w3;
                $data_tables[$item["param"]]["wave_normal"]["add"][] = $w4 - $w3;
            }else{
                if(array_key_exists($item["mid"],$waves) && array_key_exists($item["param"],$waves[$item["mid"]])){
                    $w1 = min($waves[$item["mid"]][$item["param"]]);
                    $w2 = max($waves[$item["mid"]][$item["param"]]);
                    $w3 = min($waves_abnormal[$item["mid"]][$item["param"]]);
                    $w4 = max($waves_abnormal[$item["mid"]][$item["param"]]);
                    $data_tables[$item["param"]]["wave"]["base"][] = $w1;
                    $data_tables[$item["param"]]["wave"]["add"][] = $w2 - $w1;
                    $data_tables[$item["param"]]["wave_normal"]["base"][] = $w3;
                    $data_tables[$item["param"]]["wave_normal"]["add"][] = $w4 - $w3;
                }
            }
        }

        if(array_key_exists($k,$data_tables) && array_key_exists($table,$data_tables[$k])){
            $this->response(array("xdata"=>$data_tables[$k]["xdata"],"ydata"=>$data_tables[$k][$table]));
        }else{
            $this->response(array("error"=>"未找到数据"));
        }
    }

    public function param_detail_get(){ //区域详情-环境指标统计详情-tab页数据
        $param_get = $this->get("param");
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
                "compliance"=>$item["compliance"]*100
            );
            $arr["wave"] = $arr["wave_normal"] = array();
            if($item["wave"]){
                list($w1,$w2,$w3,$w4) = explode(",",$item["wave"]);
                if($item["wave_status"] !== null){
                    $wave_status= sprintf("%04d",decbin($item["wave_status"]));
                    if(strlen($wave_status) == 4){
                        $arr["wave"][] = array("data"=>$w1,"status"=>$wave_status[0]);
                        $arr["wave"][] = array("data"=>$w2,"status"=>$wave_status[1]);
                        $arr["wave_normal"][] = array("data"=>$w3,"status"=>$wave_status[2]);
                        $arr["wave_normal"][] = array("data"=>$w4,"status"=>$wave_status[3]);
                    }

                }
            }else{
                if(array_key_exists($item["mid"],$waves) && array_key_exists($item["param"],$waves[$item["mid"]])){
                    $w1 = min($waves[$item["mid"]][$item["param"]]);
                    $w2 = max($waves[$item["mid"]][$item["param"]]);
                    $w3 = min($waves_abnormal[$item["mid"]][$item["param"]]);
                    $w4 = max($waves_abnormal[$item["mid"]][$item["param"]]);
                    $arr_wave = array($w1,$w2);
                    $arr_wave_normal = array($w3,$w4);
                    foreach ($arr_wave as $wave){
                        $k = array_search($wave, $waves[$item["mid"]][$item["param"]]);
                        if(($k || $k == 0) && array_key_exists($k,$waves_status[$item["mid"]][$item["param"]])){
                            $status = $waves_status[$item["mid"]][$item["param"]][$k];
                            $arr["wave"][] = array("data"=>$wave,"status"=>$status);
                        }
                    }
                    foreach ($arr_wave_normal as $wave){
                        $k = array_search($wave, $waves_abnormal[$item["mid"]][$item["param"]]);
                        if(($k || $k == 0) && array_key_exists($k,$waves_abnormal_status[$item["mid"]][$item["param"]])){
                            $status = $waves_abnormal_status[$item["mid"]][$item["param"]][$k];
                            $arr["wave_normal"][] = array("data"=>$wave,"status"=>$status);
                        }
                    }
                }
            }

            $texture_data[$item["param"]]["list"][] = $arr;

        }
        foreach ($texture_data as $param=>$value){
            if(array_key_exists($param, $arr_minmax)){
                $texture_data[$param]["left"] = min($arr_minmax[$param]);//*0.9;
                $texture_data[$param]["right"] = max($arr_minmax[$param]);//*1.1;
            }
        }

        foreach ($texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
                $data["unit"] = $this->unit[$param];
                if(!empty($tt)){
                    $rs[$param][] = array(
                        "key"=>$k,
                        "texture"=>implode("、",$tt),
                        "data"=>$data
                    );
                }else{
                    $rs[$param] = array("data"=>$data,"key"=>$k);
                }
            }
        }
        if(array_key_exists($param_get, $rs)){
            $this->response($rs[$param_get]);
        }else{
            $this->response(array("error"=>"未找到数据"));
        }
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
                "compliance"=>$item["compliance"]*100
            );
            $data_tables[$item["param"]]["xdata"][] = $arr["museum"];
            $data_tables[$item["param"]]["ydistance"][] = $arr["distance"];
            $data_tables[$item["param"]]["ycompliance"][] = $arr["compliance"];
            $data_tables[$item["param"]]["ycount_abnormal"][] = $arr["count_abnormal"];
            $data_tables[$item["param"]]["yaverage"][] = $arr["average"];
            $data_tables[$item["param"]]["ystandard"][] = $arr["standard"];
            $arr["wave"] = $arr["wave_normal"] = array();
            if($item["wave"]){
                list($w1,$w2,$w3,$w4) = explode(",",$item["wave"]);
                $data_tables[$item["param"]]["ywave"]["base"][] = $w1;
                $data_tables[$item["param"]]["ywave"]["add"][] = $w2 - $w1;
                $data_tables[$item["param"]]["ywave_normal"]["base"][] = $w3;
                $data_tables[$item["param"]]["ywave_normal"]["add"][] = $w4 - $w3;
                if($item["wave_status"] !== null){
                    $wave_status= sprintf("%04d",decbin($item["wave_status"]));
                    if(strlen($wave_status) == 4){
                        $arr["wave"][] = array("data"=>$w1,"status"=>$wave_status[0]);
                        $arr["wave"][] = array("data"=>$w2,"status"=>$wave_status[1]);
                        $arr["wave_normal"][] = array("data"=>$w3,"status"=>$wave_status[2]);
                        $arr["wave_normal"][] = array("data"=>$w4,"status"=>$wave_status[3]);
                    }

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
                    $arr_wave = array($w1,$w2);
                    $arr_wave_normal = array($w3,$w4);
                    foreach ($arr_wave as $wave){
                        $k = array_search($wave, $waves[$item["mid"]][$item["param"]]);
                            if(($k || $k == 0) && array_key_exists($k,$waves_status[$item["mid"]][$item["param"]])){
                                $status = $waves_status[$item["mid"]][$item["param"]][$k];
                                $arr["wave"][] = array("data"=>$wave,"status"=>$status);
                            }
                    }
                    foreach ($arr_wave_normal as $wave){
                        $k = array_search($wave, $waves_abnormal[$item["mid"]][$item["param"]]);
                            if(($k || $k == 0) && array_key_exists($k,$waves_abnormal_status[$item["mid"]][$item["param"]])){
                                $status = $waves_abnormal_status[$item["mid"]][$item["param"]][$k];
                                $arr["wave_normal"][] = array("data"=>$wave,"status"=>$status);
                            }
                    }
                    //$arr["wave_status"] = $status;
                }
            }

            $texture_data[$item["param"]]["list"][] = $arr;
            $texture_data[$item["param"]]["mids"][] = $item["mid"];
        }

        foreach ($texture_data as $param=>$value){
            if(array_key_exists($param, $arr_minmax)){
                $texture_data[$param]["left"] = min($arr_minmax[$param]);//*0.9;
                $texture_data[$param]["right"] = max($arr_minmax[$param]);//*1.1;
            }
        }

        //print_r($texture_data);exit;

        foreach ($texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
                $all_mids = array_keys($this->museum);
                if($data && array_key_exists("mids",$data)){
                    $diff = array_diff($all_mids,$data["mids"]);
                }else{
                    $diff = $all_mids;
                }
                foreach ($diff as $mid){
                    $data["list"][] = array(
                        "mid"=>$mid,
                        "museum"=>$this->museum[$mid],
                        "empty"=>true
                    );
                }
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
            if(!$abnormal){
                $envtype = $this->db->select("date,mid,env_type,param")
                    ->where("id",$depid)
                    ->get("data_envtype_param")
                    ->row_array();
                if($envtype && $envtype["date"]){
                    $ids = $ids_arr = array();
                    if($envtype["date"][0] == "W"){
                        $day_num = date("w");
                        $date_arr = array();
                        while ($day_num - 1 > 0){
                            $date_arr[] = "D".date("Ymd",strtotime("-".($day_num-1)." day"));
                            $day_num --;
                        }
                        if(!empty($date_arr)){
                            $ids = $this->db->select("id")
                                ->where("mid",$envtype["mid"])
                                ->where("env_type",$envtype["env_type"])
                                ->where("param",$envtype["param"])
                                ->where_in("date",$date_arr)
                                ->get("data_envtype_param")
                                ->result_array();
                        }
                    }elseif ($envtype["date"][0] == "M"){
                        $date = "D".date("Ym")."%";
                        $ids = $this->db->select("id")
                            ->where("mid",$envtype["mid"])
                            ->where("env_type",$envtype["env_type"])
                            ->where("param",$envtype["param"])
                            ->where("date like",$date)
                            ->get("data_envtype_param")
                            ->result_array();
                    }

                    if($ids){
                        foreach ($ids as $id){
                            $ids_arr[] = $id["id"];
                        }
                        $abnormal = $this->db->select("date,env_name,val")
                            ->where_in("depid",$ids_arr)
                            ->where("type",$type)
                            ->get("data_wave_abnormal")
                            ->result_array();
                    }
                }
            }
        }
        $this->response($abnormal);
    }

//    public function analysis_get(){
//        $mids = $this->get("mids");
//        if(!$mids){
//            $this->response();
//        }
//        $x_standard = array("99.5%(含)~100%","99%(含)~99.5%","95%(含)~99%","<95%");
//        $x_temperature = array("0%~4%(含)","4%~6%(含)","6%~7%(含)",">7.5%");
//        $x_humidity = array("0%~2%(含)","2%~3%(含)","3%~3.5%(含)",">4%");
//        $museum_standard = $museum_temperature = $museum_humidity = $counts_arr = $counts_rs = $legend = array();
//        $mid_arr = explode(",",$mids);
//        $counts = $this->db->select("mid,count_showcase")->get("data_base")->result_array();
//        foreach ($counts as $count){
//            $counts_arr[$count["mid"]] = $count["count_showcase"];
//        }
//        $data_standard = $this->detail_standard();
//        $data_scatter = $this->data_scatter();
//        $temperature = $data_scatter["scatter_temperature"];
//        $humidity = $data_scatter["scatter_humidity"];
//        foreach ($mid_arr as $mid){
//            if(array_key_exists($mid, $data_standard)){
//                $data = array();//达标率柱状图数据
//                if($data_standard[$mid] >= 0.995 && $data_standard[$mid]<= 1){
//                    $data[] = $data_standard[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($data_standard[$mid] >= 0.99 && $data_standard[$mid]< 0.995){
//                    $data[] = $data_standard[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($data_standard[$mid] >= 0.95 && $data_standard[$mid]< 0.99){
//                    $data[] = $data_standard[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($data_standard[$mid]< 0.95){
//                    $data[] = $data_standard[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                $museum_standard[] = array("name"=>$this->museum[$mid],"data"=>$data);
//            }
//
//            if(array_key_exists($mid,$temperature)){
//                $data = array();//温度离散系数 柱状图数据
//                if($temperature[$mid] > 0 && $temperature[$mid]<= 0.04){
//                    $data[] = $temperature[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($temperature[$mid] >0.04 && $temperature[$mid]<= 0.06){
//                    $data[] = $temperature[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($temperature[$mid] >0.06 && $temperature[$mid]<= 0.07){
//                    $data[] = $temperature[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($temperature[$mid]> 0.075){
//                    $data[] = $temperature[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                $museum_temperature[] = array("name"=>$this->museum[$mid],"data"=>$data);
//            }
//
//            if(array_key_exists($mid,$humidity)){
//                $data = array();//湿度离散系数 柱状图数据
//                if($humidity[$mid] > 0 && $humidity[$mid]<= 0.02){
//                    $data[] = $humidity[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($humidity[$mid] >0.02 && $humidity[$mid]<= 0.03){
//                    $data[] = $humidity[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($humidity[$mid] >0.03 && $humidity[$mid]<= 0.035){
//                    $data[] = $humidity[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                if($humidity[$mid]> 0.04){
//                    $data[] = $humidity[$mid];
//                }else{
//                    $data[] = 0;
//                }
//                $museum_humidity[] = array("name"=>$this->museum[$mid],"data"=>$data);
//            }
//
//            if(array_key_exists($mid, $counts_arr)){
//                $counts_rs[] = array("name"=>$this->museum[$mid],"count"=>$counts_arr[$mid]);//展柜数量
//            }
//
//            if(array_key_exists($mid, $this->museum)){
//                $legend[] = $this->museum[$mid];
//            }
//        }
//        $indicator_compliance = array(
//            array("name"=>"全参数平均达标率","max"=>1),
//            array("name"=>"温度","max"=>1),
//            array("name"=>"湿度","max"=>1),
//            array("name"=>"光照","max"=>1),
//            array("name"=>"紫外","max"=>1),
//            array("name"=>"有机挥发物","max"=>1)
//        );
//        $indicator_scatter = array(
//            array("name"=>"全参数平均离散系数","max"=>0.15),
//            array("name"=>"温度","max"=>0.15),
//            array("name"=>"湿度","max"=>0.15),
//            array("name"=>"光照","max"=>0.15),
//            array("name"=>"紫外","max"=>0.15),
//            array("name"=>"有机挥发物","max"=>0.15)
//        );
//        $datas = $this->depart_table($mid_arr);
//        $rs = array(
//            "compliance"=>array("xdata"=>$x_standard,"legend"=>$legend,"data"=>$museum_standard),
//            "temperature"=>array("xdata"=>$x_temperature,"legend"=>$legend,"data"=>$museum_temperature),
//            "humidity"=>array("xdata"=>$x_humidity,"legend"=>$legend,"data"=>$museum_humidity),
//            "counts"=>$counts_rs,
//            "all_compliance"=>array("legend"=>$legend,"indicator"=>$indicator_compliance,"data"=>$datas["compliance"]),
//            "all_scatter"=>array("legend"=>$legend,"indicator"=>$indicator_scatter,"data"=>$datas["scatter"])
//        );
//        $this->response($rs);
//
//    }

    public function all_compliance_get(){ //达标率 雷达图
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $mid_arr = explode(",",$mids);
        $legend = array();
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        if(sizeof($this->env_param) > 1){ //雷达图
            $indicator_compliance = array(array("name"=>"全参数平均达标率","max"=>100));
            $indicator = array(
                "temperature" => array("name"=>"温度","max"=>100),
                "humidity" => array("name"=>"湿度","max"=>100),
                "light" => array("name"=>"光照","max"=>100),
                "uv" => array("name"=>"紫外","max"=>100),
                "voc" => array("name"=>"有机挥发物","max"=>100)
            );
            foreach ($this->env_param as $param){
                if(array_key_exists($param,$indicator)){
                    $indicator_compliance[] = $indicator[$param];
                }
            }
            $datas = $this->depart_table($mid_arr);
            $rs = array("legend"=>$legend,"indicator"=>$indicator_compliance,"data"=>$datas["compliance"]);
        }else{ //柱状图
            $params = config_item("params");
            $ydata = array($params[$this->env_param[0]]);
            $datas = $this->depart_table($mid_arr);
            $rs = array("legend"=>$legend,"ydata"=>$ydata,"xdata"=>$datas["compliance"]);
        }

        $this->response($rs);

    }

    public function all_scatter_get(){ //离散系数 雷达图
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $mid_arr = explode(",",$mids);
        $legend = array();
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        if(sizeof($this->env_param) > 1) { //雷达图
            $indicator_scatter = array(array("name" => "全参数平均离散系数", "max" => 15));
            $indicator = array(
                "temperature" => array("name" => "温度", "max" => 15),
                "humidity" => array("name" => "湿度", "max" => 15),
                "light" => array("name" => "光照", "max" => 15),
                "uv" => array("name" => "紫外", "max" => 15),
                "voc" => array("name" => "有机挥发物", "max" => 15)
            );
            foreach ($this->env_param as $param) {
                if (array_key_exists($param, $indicator)) {
                    $indicator_scatter[] = $indicator[$param];
                }
            }
            $datas = $this->depart_table($mid_arr);
            $rs = array("legend"=>$legend,"indicator"=>$indicator_scatter,"data"=>$datas["scatter"]);
        }else{
            $params = config_item("params");
            $ydata = array($params[$this->env_param[0]]);
            $datas = $this->depart_table($mid_arr);
            $rs = array("legend"=>$legend,"ydata"=>$ydata,"xdata"=>$datas["scatter"]);
        }
        $this->response($rs);
    }

    public function analysis_counts_get(){ //展柜数量获取
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $counts_arr = $counts_rs = array();
        $mid_arr = explode(",",$mids);
        $counts = $this->db->select("mid,count_showcase")->get("data_base")->result_array();
        foreach ($counts as $count){
            $counts_arr[$count["mid"]] = $count["count_showcase"];
        }
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid, $counts_arr)){
                $counts_rs[] = array("name"=>$this->museum[$mid],"count"=>$counts_arr[$mid]);//展柜数量
            }
        }
        $this->response($counts_rs);
    }

    public function analysis_compliance_get(){ //达标率统计概况
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $museum_standard = $legend = array();
        $mid_arr = explode(",",$mids);
        $x_standard = array("99.5%(含)~100%","99%(含)~99.5%","95%(含)~99%","<95%");
        $data_standard = $this->detail_standard();
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid, $data_standard)){
                $data = array();//达标率柱状图数据
                if($data_standard[$mid] >= 0.995 && $data_standard[$mid]<= 1){
                    $data[] = $data_standard[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid] >= 0.99 && $data_standard[$mid]< 0.995){
                    $data[] = $data_standard[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid] >= 0.95 && $data_standard[$mid]< 0.99){
                    $data[] = $data_standard[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$mid]< 0.95){
                    $data[] = $data_standard[$mid]*100;
                }else{
                    $data[] = 0;
                }
                $museum_standard[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        $this->response(array("xdata"=>$x_standard,"legend"=>$legend,"data"=>$museum_standard));
    }

    public function analysis_temperature_get(){ //稳定性统计概况-温度
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $museum_temperature = $legend = array();
        $mid_arr = explode(",",$mids);
        $x_temperature = array("0%~4%(含)","4%~6%(含)","6%~7%(含)",">7%");
        $data_scatter = $this->data_scatter();
        $temperature = $data_scatter["scatter_temperature"];
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid,$temperature)){
                $data = array();//温度离散系数 柱状图数据
                if($temperature[$mid] > 0 && $temperature[$mid]<= 0.04){
                    $data[] = $temperature[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid] >0.04 && $temperature[$mid]<= 0.06){
                    $data[] = $temperature[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid] >0.06 && $temperature[$mid]<= 0.07){
                    $data[] = $temperature[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$mid]> 0.07){
                    $data[] = $temperature[$mid]*100;
                }else{
                    $data[] = 0;
                }
                $museum_temperature[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        $this->response(array("xdata"=>$x_temperature,"legend"=>$legend,"data"=>$museum_temperature));
    }

    public function analysis_humidity_get(){ //稳定性统计概况-湿度
        $mids = $this->get("mids");
        if(!$mids){
            $this->response(array("error"=>"缺少mids"));
        }
        $museum_humidity = $legend = array();
        $mid_arr = explode(",",$mids);
        $x_humidity = array("0%~2%(含)","2%~3%(含)","3%~3.5%(含)",">3.5%");
        $data_scatter = $this->data_scatter();
        $humidity = $data_scatter["scatter_humidity"];
        foreach ($mid_arr as $mid){
            if(array_key_exists($mid,$humidity)){
                $data = array();//湿度离散系数 柱状图数据
                if($humidity[$mid] > 0 && $humidity[$mid]<= 0.02){
                    $data[] = $humidity[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid] >0.02 && $humidity[$mid]<= 0.03){
                    $data[] = $humidity[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid] >0.03 && $humidity[$mid]<= 0.035){
                    $data[] = $humidity[$mid]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$mid]> 0.035){
                    $data[] = $humidity[$mid]*100;
                }else{
                    $data[] = 0;
                }
                $museum_humidity[] = array("name"=>$this->museum[$mid],"data"=>$data);
            }

            if(array_key_exists($mid, $this->museum)){
                $legend[] = $this->museum[$mid];
            }
        }
        $this->response(array("xdata"=>$x_humidity,"legend"=>$legend,"data"=>$museum_humidity));
    }

    private function depart_table($mid_arr = array()){
        $mids_exist = array();
        $data = array(
            "compliance"=>array(),
            "scatter"=>array()
        );
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
                    $mids_exist[] = $mid;
                    $standard = $scatter = array();
                    if(in_array("temperature",$this->env_param)){
                        $standard[] = $item["temperature_total"]?round(($item["temperature_total"] - $item["temperature_abnormal"])/$item["temperature_total"],4)*100:0;
                        $scatter[] = $item["scatter_temperature"]?$item["scatter_temperature"]*100:0;
                    };
                    if(in_array("humidity",$this->env_param)){
                        $standard[] = $item["humidity_total"]?round(($item["humidity_total"] - $item["humidity_abnormal"])/$item["humidity_total"],4)*100:0;
                        $scatter[] = $item["scatter_humidity"]?$item["scatter_humidity"]*100:0;
                    }
                    if(in_array("light",$this->env_param)){
                        $standard[] = $item["light_total"]?round(($item["light_total"] - $item["light_abnormal"])/$item["light_total"],4)*100:0;
                        $scatter[] = $item["scatter_light"]?$item["scatter_light"]*100:0;
                    }
                    if(in_array("uv",$this->env_param)){
                        $standard[] = $item["uv_total"]?round(($item["uv_total"] - $item["uv_abnormal"])/$item["uv_total"],4)*100:0;
                        $scatter[] = $item["scatter_uv"]?$item["scatter_uv"]*100:0;
                    }
                    if(in_array("voc",$this->env_param)){
                        $standard[] = $item["voc_total"]?round(($item["voc_total"] - $item["voc_abnormal"])/$item["voc_total"],4)*100:0;
                        $scatter[] = $item["scatter_voc"]?$item["scatter_voc"]*100:0;
                    }
                    if(sizeof($this->env_param) > 1){
                        $average_standard = round(array_sum($standard)/sizeof($standard),2);
                        $average_scatter = round(array_sum($scatter)/sizeof($scatter),2);
                        array_unshift($standard,$average_standard);
                        array_unshift($scatter,$average_scatter);
                    }
                    $data["compliance"][] = array("name"=>$this->museum[$mid],"value"=>$standard);
                    $data["scatter"][] = array("name"=>$this->museum[$mid],"value"=>$scatter);
                    break;
                }
            }
        }
        $diff = array_diff($mid_arr,$mids_exist);
        if(sizeof($this->env_param) > 1){
            $values = array();
            foreach ($this->env_param as $p){
                $values[] = 0;
            }
            $values[] = 0;
        }else{
            $values[] = 0;
        }
        foreach ($diff as $value){
            $data["compliance"][] =  $data["scatter"][] = array("name"=>$this->museum[$value],"value"=>$values);
        }
        return $data;

    }

}