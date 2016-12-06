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
        $this->mid = $this->get("mid");
        if(!$this->mid || !$this->btime || !$this->etime){
            $this->response();
        }
        $this->dates = array('D'.$this->btime,'D'.$this->etime);
        $this->legend = array(substr($this->btime,0,4)."-".substr($this->btime,4,2).'-'.substr($this->btime,6,2),substr($this->etime,0,4)."-".substr($this->etime,4,2).'-'.substr($this->etime,6,2));
    }
    
    public function all_compliance_get(){ //达标率 雷达图
        $indicator_compliance = array(
            array("name"=>"全参数平均达标率","max"=>100),
            array("name"=>"温度","max"=>100),
            array("name"=>"湿度","max"=>100),
            array("name"=>"光照","max"=>100),
            array("name"=>"紫外","max"=>100),
            array("name"=>"有机挥发物","max"=>100)
        );
        $datas = $this->depart_table();
        $this->response(array("legend"=>$this->legend,"indicator"=>$indicator_compliance,"data"=>$datas["compliance"]));
    }

    public function all_scatter_get(){ //离散系数 雷达图
        $indicator_scatter = array(
            array("name"=>"全参数平均离散系数","max"=>15),
            array("name"=>"温度","max"=>15),
            array("name"=>"湿度","max"=>15),
            array("name"=>"光照","max"=>15),
            array("name"=>"紫外","max"=>15),
            array("name"=>"有机挥发物","max"=>15)
        );
        $datas = $this->depart_table();
        $this->response(array("legend"=>$this->legend,"indicator"=>$indicator_scatter,"data"=>$datas["scatter"]));
    }

    public function analysis_counts_get(){ //展柜数量获取
        $counts_rs = array();
        $counts = $this->db->select("count_showcase")->where("mid",$this->mid)->get("data_base")->row_array();
        if(!$counts){
            $this->response();
        }
        foreach ($this->legend as $date){
            $counts_rs[] = array("name"=>$date,"count"=>$counts["count_showcase"]);//展柜数量
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
            $data_standard[$k] = $total?round(($total - $abnormal) / $total,2):0;
        }

        return $data_standard;
    }
    
    public function analysis_compliance_get(){ //达标率统计概况
        $museum_standard = array();
        $x_standard = array("99.5%(含)~100%","99%(含)~99.5%","95%(含)~99%","<95%");
        $data_standard = $this->detail_standard();
        foreach ($this->dates as $date){
            if(array_key_exists($date, $data_standard)){
                $data = array();//达标率柱状图数据
                if($data_standard[$date] >= 0.995 && $data_standard[$date]<= 1){
                    $data[] = $data_standard[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$date] >= 0.99 && $data_standard[$date]< 0.995){
                    $data[] = $data_standard[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$date] >= 0.95 && $data_standard[$date]< 0.99){
                    $data[] = $data_standard[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($data_standard[$date]< 0.95){
                    $data[] = $data_standard[$date]*100;
                }else{
                    $data[] = 0;
                }
                $date = substr($date,1,8);
                $date = substr($date,0,4)."-".substr($date,4,2).'-'.substr($date,6,2);
                $museum_standard[] = array("name"=>$date,"data"=>$data);
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
    
    public function analysis_temperature_get(){ //稳定性统计概况-温度
        $museum_temperature = array();
        $x_temperature = array("0%~4%(含)","4%~6%(含)","6%~7%(含)",">7.5%");
        $data_scatter = $this->data_scatter();
        $temperature = $data_scatter["scatter_temperature"];
        foreach ($this->dates as $date){
            if(array_key_exists($date,$temperature)){
                $data = array();//温度离散系数 柱状图数据
                if($temperature[$date] > 0 && $temperature[$date]<= 0.04){
                    $data[] = $temperature[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$date] >0.04 && $temperature[$date]<= 0.06){
                    $data[] = $temperature[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$date] >0.06 && $temperature[$date]<= 0.07){
                    $data[] = $temperature[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($temperature[$date]> 0.075){
                    $data[] = $temperature[$date]*100;
                }else{
                    $data[] = 0;
                }
                $date = substr($date,1,8);
                $date = substr($date,0,4)."-".substr($date,4,2).'-'.substr($date,6,2);
                $museum_temperature[] = array("name"=>$date,"data"=>$data);
            }
        }
        $this->response(array("xdata"=>$x_temperature,"legend"=>$this->legend,"data"=>$museum_temperature));
    }

    public function analysis_humidity_get(){ //稳定性统计概况-湿度
        $museum_humidity = array();
        $x_humidity = array("0%~2%(含)","2%~3%(含)","3%~3.5%(含)",">4%");
        $data_scatter = $this->data_scatter();
        $humidity = $data_scatter["scatter_humidity"];
        foreach ($this->dates as $date){
            if(array_key_exists($date,$humidity)){
                $data = array();//湿度离散系数 柱状图数据
                if($humidity[$date] > 0 && $humidity[$date]<= 0.02){
                    $data[] = $humidity[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$date] >0.02 && $humidity[$date]<= 0.03){
                    $data[] = $humidity[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$date] >0.03 && $humidity[$date]<= 0.035){
                    $data[] = $humidity[$date]*100;
                }else{
                    $data[] = 0;
                }
                if($humidity[$date]> 0.04){
                    $data[] = $humidity[$date]*100;
                }else{
                    $data[] = 0;
                }
                $date = substr($date,1,8);
                $date = substr($date,0,4)."-".substr($date,4,2).'-'.substr($date,6,2);
                $museum_humidity[] = array("name"=>$date,"data"=>$data);
            }
        }
        $this->response(array("xdata"=>$x_humidity,"legend"=>$this->legend,"data"=>$museum_humidity));
    }

    private function depart_table(){
        $data = array(
            "compliance"=>array(),
            "scatter"=>array()
        );
        $data_complex = $this->db->select("*")
            ->where("env_type",$this->env_type)
            ->where("mid",$this->mid)
            ->where_in("date",$this->dates)
            ->get("data_complex")
            ->result_array();
        foreach ($this->dates as $date){
            foreach ($data_complex as $item) {
                if($item["date"] == $date){
                    $standard = $scatter = array();
                    $standard[] = $item["temperature_total"]?round(($item["temperature_total"] - $item["temperature_abnormal"])/$item["temperature_total"])*100:0;
                    $standard[] = $item["humidity_total"]?round(($item["humidity_total"] - $item["humidity_abnormal"])/$item["humidity_total"])*100:0;
                    $standard[] = $item["light_total"]?round(($item["light_total"] - $item["light_abnormal"])/$item["light_total"])*100:0;
                    $standard[] = $item["uv_total"]?round(($item["uv_total"] - $item["uv_abnormal"])/$item["uv_total"])*100:0;
                    $standard[] = $item["voc_total"]?round(($item["voc_total"] - $item["voc_abnormal"])/$item["voc_total"])*100:0;
                    $scatter[] = $item["scatter_temperature"]?$item["scatter_temperature"]*100:0;
                    $scatter[] = $item["scatter_humidity"]?$item["scatter_humidity"]*100:0;
                    $scatter[] = $item["scatter_light"]?$item["scatter_light"]*100:0;
                    $scatter[] = $item["scatter_uv"]?$item["scatter_uv"]*100:0;
                    $scatter[] = $item["scatter_voc"]?$item["scatter_voc"]*100:0;
                    $average_standard = round(array_sum($standard)/sizeof($standard),2);
                    $average_scatter = round(array_sum($scatter)/sizeof($scatter),2);
                    array_unshift($standard,$average_standard);
                    array_unshift($scatter,$average_scatter);
                    $date = substr($date,1,8);
                    $date = substr($date,0,4)."-".substr($date,4,2).'-'.substr($date,6,2);
                    $data["compliance"][] = array("name"=>$date,"value"=>$standard);
                    $data["scatter"][] = array("name"=>$date,"value"=>$scatter);
                    break;
                }
            }
        }
        return $data;

    }
    
}