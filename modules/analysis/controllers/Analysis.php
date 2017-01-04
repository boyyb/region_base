<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 11:01
 */
class Analysis extends MY_Controller{ //按时间对比
    private $btime=null;
    private $etime=null;
    private $mid=null;
    private $legend = array();
    private $dates = array();
    function __construct()
    {
        parent::__construct();
        $this->btime = $this->get("btime");
        $this->etime = $this->get("etime");
        $this->mid = $this->get("mids");
        if(!$this->mid || !$this->btime || !$this->etime){
            $this->response(array("error"=>"缺少必要参数"));
        }
        $this->dates = array('D'.$this->btime,'D'.$this->etime);
        $this->legend = array(substr($this->btime,0,4)."-".substr($this->btime,4,2).'-'.substr($this->btime,6,2),substr($this->etime,0,4)."-".substr($this->etime,4,2).'-'.substr($this->etime,6,2));
    }
    
    public function all_compliance_get(){ //达标率 雷达图
        $datas = $this->depart_table();
        if(sizeof($this->env_param) > 1) { //雷达图
            $indicator_compliance = array(array("name"=>"平均达标率","max"=>100));
            $indicator = array(
                "temperature" => array("name"=>"温度","max"=>100),
                "humidity" => array("name"=>"湿度","max"=>100),
                "light" => array("name"=>"光照","max"=>100),
                "uv" => array("name"=>"紫外","max"=>100),
                "voc" => array("name"=>"有机挥发物","max"=>100)
            );
            foreach ($indicator as $param => $value) {
                if (in_array($param,$this->env_param)) {
                    $indicator_compliance[] = $value;
                }
            }
            $rs = array("legend" => $this->legend, "indicator" => $indicator_compliance, "data" => $datas["compliance"]);
        }else{
            $params = config_item("params");
            $ydata = array($params[$this->env_param[0]]);
            $rs = array("legend"=>$this->legend,"ydata"=>$ydata,"xdata"=>$datas["compliance"]);
        }
        $this->response($rs);
    }

    public function all_scatter_get(){ //离散系数 雷达图
        $datas = $this->depart_table();
        if(sizeof($this->env_param) > 1) { //雷达图
            $indicator_scatter = array(array("name" => "平均离散系数"));
            $indicator = array(
                "temperature" => array("name" => "温度"),
                "humidity" => array("name" => "湿度"),
                "light" => array("name" => "光照"),
                "uv" => array("name" => "紫外"),
                "voc" => array("name" => "有机挥发物")
            );
            $scatters = array();
            foreach ($datas["scatter"] as $data){
                if($data["value"]){
                    $scatters[] = max($data["value"]);
                }
            }
            $max = $scatters?max($scatters):0;
            $indicator_scatter[0]["max"] = intval($max)+1;
            foreach ($indicator as $param => $value) {
                if (in_array($param,$this->env_param)) {
                    $value["max"] = intval($max)+1;
                    $indicator_scatter[] = $value;
                }
            }
            $rs = array("legend"=>$this->legend,"indicator"=>$indicator_scatter,"data"=>$datas["scatter"]);
        }else{
            $params = config_item("params");
            $ydata = array($params[$this->env_param[0]]);
            $rs = array("legend"=>$this->legend,"ydata"=>$ydata,"xdata"=>$datas["scatter"]);
        }
        $this->response($rs);
    }

    public function analysis_counts_get(){ //环境数量获取
        $counts_rs = array();
        $env_type = $this->get("env_type");
        if(!$env_type){
            $this->response(array("error"=>"请选择环境类型"));
        }
        if(!$this->db->field_exists("count_".$env_type,"data_base")){
            $this->response(array("error"=>"字段错误：".$env_type));
        }
        $counts = $this->db->select("count_".$env_type)->where("mid",$this->mid)->get("data_base")->row_array();
        if(!$counts){
            $this->response(array("error"=>"找不到数据"));
        }
        foreach ($this->legend as $date){
            $counts_rs[] = array("name"=>$date,"count"=>$counts["count_".$env_type]);//环境数量
        }
        $this->response($counts_rs);
    }

    private function detail_standard(){
        $params = '';
        $suffix = array("total","abnormal");
        $data_flag = $data_standard = array();
        foreach ($this->env_param as $param){
            $params .= ",".$param."_total".",".$param."_abnormal";
        }
        $data_compliance = $this->db->select("id,date".$params)
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex")
            ->result_array();
        foreach ($data_compliance as $value){
            if($value["date"]){
                foreach ($this->env_param as $param){
                    foreach ($suffix as $s){
                        $data_flag[$value["date"]][$s][] = $value[$param."_".$s];
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

    private function detail_standard_env(){
        $params = '';
        $data_standard = array();
        foreach ($this->env_param as $param){
            $params .= ",".$param."_total".",".$param."_abnormal";
        }
        $data_compliance = $this->db->select("id,date".$params)
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex_env")
            ->result_array();
        foreach ($data_compliance as $value){
            if($value["date"]){
                $abnormal_count = $total_count = 0;
                foreach ($this->env_param as $param){
                    $abnormal_count += $value[$param."_abnormal"];
                    $total_count += $value[$param."_total"];
                }
                if($total_count) {
                    $data_standard[$value["date"]][] = round(($total_count - $abnormal_count) / $total_count, 4);
                }
            }
        }
        return $data_standard;
    }
    
    public function analysis_compliance_get(){ //达标率统计概况
        $museum_standard = array();
        $x_standard = array("99.5%(含)~100%","99%(含)~99.5%","95%(含)~99%","<95%");
        $data_standard = $this->detail_standard_env();
        foreach ($this->dates as $date){
            $new_date = substr($date,1,4)."-".substr($date,5,2).'-'.substr($date,7,2);
            if(array_key_exists($date, $data_standard)){
                $data = array();//达标率柱状图数据
                $count_all = $count1 = $count2 = $count3 = $count4 = 0;//达标率柱状图数据
                foreach ($data_standard[$date] as $value) {
                    if ($value >= 0.995 && $value <= 1) {
                        $count1 ++;
                    }
                    if ($value >= 0.99 && $value < 0.995) {
                        $count2 ++;
                    }
                    if ($value >= 0.95 && $value < 0.99) {
                        $count3 ++;
                    }
                    if ($value < 0.95) {
                        $count4 ++;
                    }
                    $count_all ++;
                }


                if ($count_all) {
                    $data[] = round(($count1 / $count_all),4)*100;
                    $data[] = round(($count2 / $count_all),4)*100;
                    $data[] = round(($count3 / $count_all),4)*100;
                    $data[] = round(($count4 / $count_all),4)*100;
                }else{
                    $data = array(0,0,0,0);
                }
                $museum_standard[] = array("name"=>$new_date,"data"=>$data);
            }else{
                $museum_standard[] = array("name"=>$new_date,"data"=>array(0,0,0,0));
            }
            
        }
        $this->response(array("xdata"=>$x_standard,"legend"=>$this->legend,"data"=>$museum_standard));
    }

    private function data_scatter(){
        $data = $this->db->select("date,scatter_temperature,scatter_humidity")
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex")
            ->result_array();
        $datas["scatter_temperature"] = $datas["scatter_humidity"] = array();
        foreach ($data as $value){
            $datas["scatter_temperature"][$value["date"]] = $value["scatter_temperature"];
            $datas["scatter_humidity"][$value["date"]] = $value["scatter_humidity"];
        }

        return $datas;
    }

    private function data_scatter_env(){
        $data = $this->db->select("date,scatter_temperature,scatter_humidity")
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex_env")
            ->result_array();
        $datas["scatter_temperature"] = $datas["scatter_humidity"] = array();
        foreach ($data as $value){
            if($value["scatter_temperature"] != null) {
                $datas["scatter_temperature"][$value["date"]][] = $value["scatter_temperature"];
            }
            if($value["scatter_humidity"] != null) {
                $datas["scatter_humidity"][$value["date"]][] = $value["scatter_humidity"];
            }
        }

        return $datas;
    }
    
    public function analysis_temperature_get(){ //稳定性统计概况-温度
        $museum_temperature = array();
        $x_temperature = array("0%~4%(含)","4%~6%(含)","6%~7%(含)",">7%");
        $data_scatter = $this->data_scatter_env();
        $temperature = $data_scatter["scatter_temperature"];
        foreach ($this->dates as $date){
            $new_date = substr($date,1,4)."-".substr($date,5,2).'-'.substr($date,7,2);
            if(array_key_exists($date,$temperature)){
                $data = array();//温度离散系数 柱状图数据
                $count_all = $count1 = $count2 = $count3 = $count4 = 0;
                foreach ($temperature[$date] as $value) {
                    if ($value >= 0 && $value <= 0.04) {
                        $count1++;
                    }
                    if ($value > 0.04 && $value <= 0.06) {
                        $count2++;
                    }
                    if ($value > 0.06 && $value <= 0.07) {
                        $count3++;
                    }
                    if ($value > 0.07) {
                        $count4++;
                    }
                    $count_all ++;
                }
                if ($count_all) {
                    $data[] = round(($count1 / $count_all),4)*100;
                    $data[] = round(($count2 / $count_all),4)*100;
                    $data[] = round(($count3 / $count_all),4)*100;
                    $data[] = round(($count4 / $count_all),4)*100;
                }else{
                    $data = array(0,0,0,0);
                }
                $museum_temperature[] = array("name"=>$new_date,"data"=>$data);
            }else{
                $museum_temperature[] = array("name"=>$new_date,"data"=>array(0,0,0,0));
            }
        }
        $this->response(array("xdata"=>$x_temperature,"legend"=>$this->legend,"data"=>$museum_temperature));
    }

    public function analysis_humidity_get(){ //稳定性统计概况-湿度
        $museum_humidity = array();
        $x_humidity = array("0%~2%(含)","2%~3%(含)","3%~3.5%(含)",">3.5%");
        $data_scatter = $this->data_scatter_env();
        $humidity = $data_scatter["scatter_humidity"];
        foreach ($this->dates as $date){
            $new_date = substr($date,1,4)."-".substr($date,5,2).'-'.substr($date,7,2);
            if(array_key_exists($date,$humidity)){
                $data = array();//湿度离散系数 柱状图数据
                $count_all = $count1 = $count2 = $count3 = $count4 = 0;
                foreach ($humidity[$date] as $value) {
                    if ($value >= 0 && $value <= 0.02) {
                        $count1++;
                    }
                    if ($value > 0.02 && $value <= 0.03) {
                        $count2++;
                    }
                    if ($value > 0.03 && $value <= 0.035) {
                        $count3++;
                    }
                    if ($value > 0.035) {
                        $count4++;
                    }
                    $count_all ++;
                }
                if ($count_all) {
                    $data[] = round(($count1 / $count_all),4)*100;
                    $data[] = round(($count2 / $count_all),4)*100;
                    $data[] = round(($count3 / $count_all),4)*100;
                    $data[] = round(($count4 / $count_all),4)*100;
                }else{
                    $data = array(0,0,0,0);
                }
                $museum_humidity[] = array("name"=>$new_date,"data"=>$data);
            }else{
                $museum_humidity[] = array("name"=>$new_date,"data"=>array(0,0,0,0));
            }
        }
        $this->response(array("xdata"=>$x_humidity,"legend"=>$this->legend,"data"=>$museum_humidity));
    }


    private function humidity_scatter(){
        $arr = array("1","2","3","12");
        $datas = $rs = array();
        $data = $this->db->select("date,average,standard")
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->where_in("param",$arr)
            ->get("data_envtype_param")
            ->result_array();
        foreach ($data as $value){
            if($value["average"] != 0){
                $datas[$value["date"]][] = $value;
            }
        }

        foreach ($datas as $date => $values){
            $sum = array();
            foreach ($values as $value){
                $scatter = round($value["standard"] / $value["average"],4);
                if ($scatter){
                    $sum[] = $scatter;
                }
            }
            if(!empty($sum)){
                $rs[$date] = round(array_sum($sum) / sizeof($sum) , 4);
            }
        }
        return $rs;
    }

    private function depart_table(){
        $dates_exist = array();
        $data = array(
            "compliance"=>array(),
            "scatter"=>array()
        );
        $params = array_keys(config_item("params"));
        $humidity_scatter = $this->humidity_scatter();
        $data_complex = $this->db->select("*")
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex")
            ->result_array();
        foreach ($this->dates as $date){
            foreach ($data_complex as $item) {
                if($item["date"] == $date){
                    $dates_exist[] = $date;
                    $standard = $scatter = array();
                    $standard_count = $scatter_count = 0;
                    foreach ($params as $param){
                        if(in_array($param,$this->env_param)){
                            if($item[$param."_total"]){
                                $standard[] = round(($item[$param."_total"] - $item[$param."_abnormal"])/$item[$param."_total"],4)*100;
                                $standard_count ++;
                            }else{
                                $standard[] = 0;
                            }
                            if($param == "humidity" && array_key_exists($date, $humidity_scatter)) {
                                $scatter[] = $humidity_scatter[$date]*100;
                                $scatter_count ++;
                            }else if($item["scatter_".$param]){
                                $scatter[] = $item["scatter_".$param]*100;
                                $scatter_count ++;
                            }else{
                                $scatter[] = 0;
                            }
                        };
                    }

                    if(sizeof($this->env_param) > 1) {
                        $average_standard = $standard_count?round(array_sum($standard)/$standard_count,2):0;
                        $average_scatter = $scatter_count?round(array_sum($scatter)/$scatter_count,2):0;
                        array_unshift($standard, $average_standard);
                        array_unshift($scatter, $average_scatter);
                    }
                    $date = substr($date,1,4)."-".substr($date,5,2).'-'.substr($date,7,2);
                    $data["compliance"][] = array("name"=>$date,"value"=>$standard);
                    $data["scatter"][] = array("name"=>$date,"value"=>$scatter);
                    break;
                }
            }
        }

        $diff = array_diff($this->dates,$dates_exist);
        if(sizeof($this->env_param) > 1){
            $values = array();
            foreach ($this->env_param as $p){
                $values[] = 0;
            }
            $values[] = 0;
        }else{
            $values[] = 0;
        }
        foreach ($diff as $date){
            $date = substr($date,1,4)."-".substr($date,5,2).'-'.substr($date,7,2);
            $data["compliance"][] =  $data["scatter"][] = array("name"=>$date,"value"=>$values);
        }
        return $data;

    }
    
}