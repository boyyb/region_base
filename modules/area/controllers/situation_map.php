<?php
class situation_map extends MY_Controller{

    function __construct(){
        parent::__construct();
    }
    protected function date_list($s,$e){
        $date=array();
        for($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }

    //环形图-达标率
    public function pie_standard_percent(){
        $env = $this->env_type = "展厅";
        $date_start = $this->start_time = 20161024;
        $date_end = $this->end_time = 20161025;
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");

        $sumstr = '';
        foreach ($param as $v) {
            $sumstr .= ",SUM({$v}_total),SUM({$v }_abnormal)";
        }
        $sumstr = substr($sumstr, 1);

        //获取各博物馆下的环境id
        $de_datas  = $this->db
            ->select("mid,id")
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_env")
            ->result_array();
        //var_dump($de_datas);
        foreach($de_datas as $v){
            $env_ids[$v['mid']][] = $v['id'];
        }
        //var_dump($env_ids);
        //统计各博物馆下所有环境达标总数和未达标被总数
        foreach($env_ids as $k => $v){
            $dec_datas = $this->db
                ->select($sumstr)
                ->where("date>=",$date_start)
                ->where("date<=",$date_end)
                ->where_in("eid",$v)
                ->get("data_env_compliance")->result_array();
            $datas[$k] = $dec_datas[0];
        }
        //var_dump($datas);
        //统计各博物馆达标率
        foreach($datas as $k => $v){
            $tsum = 0;
            $asum = 0;
            foreach ($v as $k1 => $v1) {
                if (strpos($k1, "total") !== false) $tsum += $v1;
                if (strpos($k1, "abnormal") !== false) $asum += $v1;
            }
            if(!$tsum) {$data[$k] = $tsum; continue;}//无数据的博物馆
            $data[$k] = round(($tsum-$asum)/$tsum,4);
        }
        //var_dump($data);
        //达标率区间参数
        $sp = array(
            1=>array("name"=>"99.5%(含)~100%","min"=>0.995,"max"=>1.1),
            2=>array("name"=>"99%(含)~99.5%","min"=>0.99,"max"=>0.995),
            3=>array("name"=>"95%(含)~99%","min"=>0.95,"max"=>0.99),
            4=>array("name"=>"<95%","min"=>0,"max"=>0.95)
        );

        //按照约定格式返回
        foreach($sp as $k=>$v){
            foreach($data as $v1){
                if($v1<$v['max'] && $v1>=$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $sp_data[] = array(
                "value"=>(string)count($data1[$k]),
                "name"=>$v['name']);
            else $sp_data[] = array(
                "value"=>"0",
                "name"=>$v['name']);
        }

        echo json_encode($sp_data,JSON_UNESCAPED_UNICODE);
    }

    //环形图-稳定性  天数据
    public function pie_stability(){
        $env = $this->env_type = "展厅";
        $date_start = $this->start_time = 20161024;

        $ts = array( //温度
            1=>array("name"=>"0~4%(含)","min"=>0,"max"=>0.04),
            2=>array("name"=>"4%~6%(含)","min"=>0.04,"max"=>0.06),
            3=>array("name"=>"6%~7%(含)","min"=>0.06,"max"=>0.07),
            4=>array("name"=>">7%","min"=>0.07,"max"=>999)
        );
        $hs = array( //湿度
            1=>array("name"=>"0~2%(含)","min"=>0,"max"=>0.02),
            2=>array("name"=>"2%~3%(含)","min"=>0.02,"max"=>0.03),
            3=>array("name"=>"3%~3.5%(含)","min"=>0.03,"max"=>0.035),
            4=>array("name"=>">3.5%","min"=>0.035,"max"=>999)
        );

        $dc_datas = $this->db
            ->where("date",$date_start)
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_complex")
            ->result_array();
        var_dump($dc_datas);
        foreach($dc_datas as $v){
            $data["temperature_scatter"][] = $v["scatter_temp"];
            $data["humidity_scatter"][] = $v["scatter_humidity"];
        }
        var_dump($data);
        //温度统计
        foreach($ts as $k=>$v){
            foreach($data['temperature_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $ts_data[] = array(
                "value"=>(string)count($data1[$k]),
                "name"=>$v['name']);
            else $ts_data[] = array(
                "value"=>"0",
                "name"=>$v['name']);
        }
        //湿度统计
        foreach($hs as $k=>$v){
            foreach($data['humidity_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data2[$k][] = $v1;
            }
            if(isset($data2[$k])) $hs_data[] = array(
                "value"=>(string)count($data2[$k]),
                "name"=>$v['name']);
            else $hs_data[] = array(
                "value"=>"0",
                "name"=>$v['name']);
        }

        $ret['temperature_scatter'] = $ts_data;
        $ret['humidity_scatter'] = $hs_data;
        var_dump($ret);
        echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    }

    //地图  日数据
    public function map($mid=false){
        $env = $this->env_type = "展厅";
        $date_start = $this->start_time = 20161024;
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");

        $sumstr = '';
        foreach ($param as $v) {
            $sumstr .= ",SUM({$v}_total),SUM({$v }_abnormal)";
        }
        $sumstr = substr($sumstr, 1);

        //获取各博物馆下的环境id
        $de_datas  = $this->db
            ->select("mid,id")
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_env")
            ->result_array();
        foreach($de_datas as $v){
            $env_ids[$v['mid']][] = $v['id'];
        }
        //统计各博物馆下所有环境达标总数和未达标被总数
        foreach($env_ids as $k => $v){
            $dec_datas = $this->db
                ->select($sumstr)
                ->where("date",$date_start)
                ->where_in("eid",$v)
                ->get("data_env_compliance")->result_array();
            $datas[$k] = $dec_datas[0];
        }
        //统计各博物馆达标率
        foreach($datas as $k => $v){
            $tsum = 0;
            $asum = 0;
            foreach ($v as $k1 => $v1) {
                if (strpos($k1, "total") !== false) $tsum += $v1;
                if (strpos($k1, "abnormal") !== false) $asum += $v1;
            }
            if(!$tsum) {$data['standard_percnet'][$k] = "0"; continue;}//无数据的博物馆
            $data['standard_percnet'][$k] = (string)round(($tsum-$asum)/$tsum,4);
        }

        $dc_datas = $this->db
            ->where("date",$date_start)
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_complex")
            ->result_array();
        foreach($dc_datas as $v){
            $data["temperature_scatter"][$v['mid']] = $v["scatter_temp"];
            $data["humidity_scatter"][$v['mid']] = $v["scatter_humidity"];
            $data['is_wave_abnormal'][$v['mid']] = $v['is_wave_abnormal']==1?"是":"否";
            $data['is_value_abnormal'][$v['mid']] = $v['is_value_abnormal']==1?"是":"否";
        }

        $mdata = $this->db->order_by("id asc")->get("museum")->result_array();//各博物馆基础数据
        //需要增加博物馆所属行政区域（省/直辖市/..） 经纬度坐标
        foreach($mdata as $v){
            $data[] = array(
                "mid"=>$v['id'],
                "museum"=>$v['name'],
                "standard_percent"=>$datas["standard_percent"][$v['id']],
                "temperature_scatter"=>$datas['temperature_scatter'][$v['id']],
                "humidity_scatter"=>$datas['humidity_scatter'][$v['id']],
                "is_wave_abnormal"=>$datas['is_wave_abnormal'][$v['id']],
                "is_value_abnormal"=>$datas['is_value_abnormal'][$v['id']],
            );
        }
        //指定博物馆
        if($mid){
            foreach($data as $v){
                if($v['mid'] == $mid){
                    var_dump($v);
                    return $v;
                }
            }
        }
        var_dump($data);
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        echo $data;
    }

    public function test(){

    }


}