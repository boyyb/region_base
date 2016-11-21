<?php
class situation extends MY_Controller{

    public $date = null;

    function __construct(){
        parent::__construct();
        $date_str = $this->get("definite_time");
        switch ($date_str){
            case "yesterday": //昨天
                $this->date = "D".date("Ymd",time() - 24*60*60);
                break;
            case "before_yes": //前天
                $this->date = "D".date("Ymd",time() - 24*60*60*2);
                break;
            case "week": //本周
                $this->date = "W".date("YW");
                break;
            case "month": //本月
                $this->date = "M".date("Ym");
                break;
            default: $this->date = "D".$date_str; //某一天
        }
    }

    //环形图-达标率
    public function pie_compliance(){
        $env = $this->env_type;
        $param = $this->env_param;

        //构建达标率运算的sql查询字符串
        $totalstr = '';
        $abnormalstr = '';
        foreach ($param as $v) {
            $totalstr .= "+{$v}_total";
            $abnormalstr .= "+{$v}_abnormal";
        }
        $totalstr = "(".substr($totalstr, 1).")";
        $abnormalstr = "(".substr($abnormalstr, 1).")";
        $sp = "(".$totalstr."-".$abnormalstr.")/$totalstr "." as standard_percent";//达标率计算公式

        //统计各博物馆达标率
        $dc_datas = $this->db
            ->select("mid,".$sp)
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->get("data_complex")
            ->result_array();
        $data = array_column($dc_datas,"standard_percent");

        //达标率区间参数
        $sp = array(
            1=>array("name"=>"99.5%(含)~100%","min"=>0.995,"max"=>1.1),
            2=>array("name"=>"99%(含)~99.5%","min"=>0.99,"max"=>0.995),
            3=>array("name"=>"95%(含)~99%","min"=>0.95,"max"=>0.99),
            4=>array("name"=>"<95%","min"=>0,"max"=>0.95)
        );

        //构建返回数据
        foreach($sp as $k=>$v){
            foreach($data as $v1){
                if($v1<$v['max'] && $v1>=$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $sp_data[] = array(
                "value"=>count($data1[$k]),
                "name"=>$v['name']);
            else $sp_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }

        echo json_encode($sp_data,JSON_UNESCAPED_UNICODE);
    }

    //环形图-稳定性
    public function pie_stability(){
        $env = $this->env_type;
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
            ->select("distinct(mid),scatter_temperature,scatter_humidity")
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_complex")
            ->result_array();

        //温湿度各博物馆的离散数据
        foreach($dc_datas as $v){
            $data["temperature_scatter"][] = $v["scatter_temperature"];
            $data["humidity_scatter"][] = $v["scatter_humidity"];
        }

        //温度统计
        foreach($ts as $k=>$v){
            foreach($data['temperature_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $ts_data[] = array(
                "value"=>count($data1[$k]),
                "name"=>$v['name']);
            else $ts_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }
        //湿度统计
        foreach($hs as $k=>$v){
            foreach($data['humidity_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data2[$k][] = $v1;
            }
            if(isset($data2[$k])) $hs_data[] = array(
                "value"=>count($data2[$k]),
                "name"=>$v['name']);
            else $hs_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }
        $ret['temperature_scatter'] = $ts_data;
        $ret['humidity_scatter'] = $hs_data;

        echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    }

    //地图
    public function map(){
        $mid = $this->get("mids");//接收对比分析的博物馆id 格式mid=2,3,4
        $env = $this->env_type;
        $param = $this->env_param;
        $params = array();//环境参数编号
        foreach($param as $v){
            if($v=="humidity") array_push($params,1,2,3,10); //加混合材质 10
            elseif($v=="light") array_push($params,4,5,6,11); //加混合材质 11
            elseif($v=="temperature") array_push($params,7);
            elseif($v=="uv") array_push($params,8);
            elseif($v=="voc") array_push($params,9);
        }

        //博物馆基础信息(名称、坐标)
        $mdatas = $this->db->order_by("id asc")->get("museum")->result_array();
        foreach($mdatas as $v){
            $mdata[$v['id']] = $v;
        }

        //统计各博物馆下离散、达标率
        $totalstr = '';
        $abnormalstr = '';
        foreach ($param as $v) {
            $totalstr .= "+{$v}_total";
            $abnormalstr .= "+{$v}_abnormal";
        }
        $totalstr = "(".substr($totalstr, 1).")";
        $abnormalstr = "(".substr($abnormalstr, 1).")";
        $sp = "(".$totalstr."-".$abnormalstr.")/$totalstr "." as standard_percent";
        $dc_datas = $this->db
            ->select("mid,scatter_temperature,scatter_humidity,".$sp)
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->order_by("mid")
            ->get("data_complex")
            ->result_array();
        if(!$dc_datas) die(json_encode(array()));
        //统计各博物馆日波动超标情况 不剔除异常值
        $wave_data = array();
        $dep_datas = $this->db
            ->select("mid,date,wave_status")
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->where_in("param",$params)
            ->where("wave_status>",3)
            ->group_by("mid")
            ->get("data_envtype_param")
            ->result_array();
        if($dep_datas){
            foreach($dep_datas as $v){
                $wave_data[$v['mid']] = $v['wave_status']; //波动超标数据
            }
        }

        //统计各博物馆异常值情况
        $abnormal_data = array();
        $dep_datas = $this->db
            ->select("mid,date,SUM(count_abnormal) as number")
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->where_in("param",$params)
            ->group_by("mid")
            ->having("SUM(count_abnormal) > 0")
            ->get("data_envtype_param")
            ->result_array();
        if($dep_datas){
            foreach($dep_datas as $v){
                $abnormal_data[$v['mid']] = $v['number']; //异常值数据
            }
        }

        //所有博物馆数据综合
        foreach($dc_datas as $v){
            $map_data[] = array(
                "mid"=>$v['mid'],
                "name"=>$mdata[$v['mid']]['name'],
                "map_name"=>app_config("map_name"),
                "grid"=>array((float)$mdata[$v['mid']]['longitude'],(float)$mdata[$v['mid']]['latitude']),
                "compliance"=>$v['standard_percent'] !== null?round($v['standard_percent'],3)*100 . "%":null,
                "temperature_scatter"=>$v['scatter_temperature']?$v['scatter_temperature']*100 . "%":null,
                "humidity_scatter"=>$v['scatter_humidity']?$v['scatter_humidity']*100 . "%":null,
                "is_wave_abnormal"=>isset($wave_data[$v['mid']])?"是":"无",
                "is_value_abnormal"=>isset($abnormal_data[$v['mid']])?"是":"无"
            );
        }

        if($mid){//参与对比的博物馆
            $mid = explode(",",$mid);
            foreach($map_data as $v){
                foreach($mid as $v1){
                    if($v['mid'] == $v1){
                       $c_data[] = $v;
                    }
                }
            }
            echo json_encode($c_data,JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode($map_data,JSON_UNESCAPED_UNICODE);
        }
    }

    //统计博物馆数量统计
    public function statistic(){
        $data = array();
        $data["total"] = $this->db->count_all_results("museum");
        $data['show'] = $this->db
            ->select("distinct(mid)")
            ->where("date",$this->date)
            ->where("env_type",$this->env_type)
            ->count_all_results("data_complex");

        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }

}