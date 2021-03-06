<?php
class Situation extends MY_Controller{

    protected $date = null; //通用查询日期字符串
    protected $date_start = null; //日波动查询开始日期 20160101
    protected $date_end = null; //日波动查询结束日期 20160105
    protected $date_list = array(); //日波动查询的日期列表

    function __construct(){
        parent::__construct();
        $date_str = $this->get("definite_time");
        switch ($date_str){
            case "yesterday": //昨天
                $this->date = "D".date("Ymd",strtotime('-1 day'));
                $this->date_start = $this->date_end = date("Ymd",strtotime('-1 day'));
                break;
            case "before_yes": //前天
                $this->date = "D".date("Ymd",strtotime('-2 day'));
                $this->date_start = $this->date_end = date("Ymd",strtotime('-2 day'));
                break;
            case "week": //本周
                if(date("w") == 1){ //周一查上周数据
                    $this->date_start = date("Ymd",mktime(0,0,0,date('m'),date('d')-date('w')-6,date('y')));
                    $this->date_end = date("Ymd",mktime(23,59,59,date('m'),date('d')-date('w'),date('y')));
                    $this->date = "W".date("YW",strtotime($this->date_end));
                }else{ //本周数据
                    $this->date_start = date("Ymd",mktime(0,0,0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y")));
                    $this->date_end = date("Ymd",strtotime('-1 day'));
                    $this->date = "W".date("YW");
                }
                break;
            case "month": //本月
                if(date("d") == "01"){ //1号查上月数据
                    $this->date_start = date("Ymd",mktime(0,0,0,date('m')-1,1,date('y')));
                    $this->date_end = date("Ymd",mktime(23,59,59,date("m"),0,date("y")));
                    $this->date = "M".date("Ym",strtotime($this->date_end));
                }else{
                    $this->date_start = date("Ymd",mktime(0,0,0,date('m'),1,date('y')));
                    $this->date_end = date("Ymd",strtotime('-1 day'));
                    $this->date = "M".date("Ym");
                }
                break;
            default:
                $this->date = "D".$date_str; //某一天
                $this->date_start = $this->date_end = $date_str;
        }

        $this->date_list = $this->_date_list($this->date_start,$this->date_end);
    }

    //生成日期列表
    protected function _date_list($s,$e){
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = "D".date("Ymd", $i);
        }
        return $date;
    }

    //达标率
    public function pie_compliance_get(){
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
            ->select("distinct(mid),".$sp)
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->get("data_complex")
            ->result_array();
        if(!$dc_datas) $this->response(array("error"=>"没查询到数据！")); //数据库无对应日期数据
        $data = array_column($dc_datas,"standard_percent");//各博物馆达标率
        if(count(array_filter($data)) == 0) $this->response(array("error"=>"没查询到数据！"));
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
                if(!$v1) continue; // 排除null值 不计入统计
                if($v1<$v['max'] && $v1>=$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $sp_data[] = array(
                "value"=>count($data1[$k]),
                "name"=>$v['name']);
            else $sp_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }

        $this->response($sp_data);
    }


    //接口调用-离散系数-温度
    public function pie_scatter_temperature_get(){
        $env = $this->env_type;
        $ts = array( //区间阈值
            1=>array("name"=>"0~4%(含)","min"=>0,"max"=>0.04),
            2=>array("name"=>"4%~6%(含)","min"=>0.04,"max"=>0.06),
            3=>array("name"=>"6%~7%(含)","min"=>0.06,"max"=>0.07),
            4=>array("name"=>">7%","min"=>0.07,"max"=>9999)
        );

        $dc_datas = $this->db
            ->select("distinct(mid),scatter_temperature,temperature_total")
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->order_by("mid asc")
            ->get("data_complex")
            ->result_array();
        if(!$dc_datas) $this->response(array("error"=>"没查询到数据！"));

        //各博物馆的离散数据列表
        foreach($dc_datas as $v){
            if($v['temperature_total']>0) $list[] = $v['scatter_temperature']; //存在温度数据
        }

        //区间个数统计
        foreach($ts as $k=>$v){
            foreach($list as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $ts_data[] = array(
                "value"=>count($data1[$k]),
                "name"=>$v['name']);
            else $ts_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }

        $this->response($ts_data);
    }


    //接口调用-离散系数-湿度
    public function pie_scatter_humidity_get(){
        $hs = array( //湿度
            1=>array("name"=>"0~2%(含)","min"=>0,"max"=>0.02),
            2=>array("name"=>"2%~3%(含)","min"=>0.02,"max"=>0.03),
            3=>array("name"=>"3%~3.5%(含)","min"=>0.03,"max"=>0.035),
            4=>array("name"=>">3.5%","min"=>0.035,"max"=>9999)
        );
        $scatter = "AVG(standard/average) as scatter";
        $dep_datas = $this->db
            ->select('mid,env_type,param,'.$scatter)
            ->where("date",$this->date)
            ->where("env_type",$this->env_type)
            ->where_in("param",array(1,2,3,10,12))
            ->group_by("mid")
            ->get("data_envtype_param")
            ->result_array();
        if(!$dep_datas) $this->response(array("error"=>"没有查询到数据！"));
        $list = array_column($dep_datas,"scatter");
        foreach($hs as $k=>$v){
            foreach($list as $v1){
                $v1 = number_format($v1,4);
                if($v1<=$v['max'] && $v1>$v['min']) $data2[$k][] = $v1;
            }
            if(isset($data2[$k])) $hs_data[] = array(
                "value"=>count($data2[$k]),
                "name"=>$v['name']);
            else $hs_data[] = array(
                "value"=>0,
                "name"=>$v['name']);
        }

        $this->response($hs_data);
    }

    //地图-所有博物馆（多博物馆）
    public function map_get(){
        $mid = $this->get("mids");//接收对比分析的博物馆id 格式mid=2,3,4
        $env = $this->env_type;
        $param = $this->env_param;
        $params = array();//环境参数编号
        $wave_params = array();//日波动相关的环境参数编号
        foreach($param as $v){
            if($v=="humidity" && $this->env_type != "展厅") {array_push($params,1,2,3,12);array_push($wave_params,1,2,3,12);}
            elseif($v=="humidity" && $this->env_type == "展厅") {array_push($params,10);array_push($wave_params,10);}
            elseif($v=="light" && $this->env_type != "展厅") array_push($params,4,5,6,13);
            elseif($v=="light" && $this->env_type == "展厅") array_push($params,11);
            elseif($v=="temperature") {array_push($params,7);array_push($wave_params,7);}
            elseif($v=="uv") array_push($params,8);
            elseif($v=="voc") array_push($params,9);
        }

        //统计接收到的各博物馆下温度离散、达标率
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
            ->select("distinct(mid),scatter_temperature,temperature_total,".$sp)
            ->where("date",$this->date)
            ->where("env_type",$env)
            ->order_by("mid")
            ->get("data_complex")
            ->result_array();
        $datas = array();
        if($dc_datas){
            foreach($dc_datas as $v){
                $datas[$v['mid']] = $v;
            }
        }

        //统计各个博物馆的离散-湿度
        $scatter = "AVG(standard/average) as scatter";
        $dep_datas = $this->db
            ->select('mid,env_type,param,'.$scatter)
            ->where("date",$this->date)
            ->where("env_type",$this->env_type)
            ->where_in("param",array(1,2,3,10,12))
            ->group_by("mid")
            ->get("data_envtype_param")
            ->result_array();
        foreach($dep_datas as $v){
            $scatter_humidity[$v['mid']] = number_format($v['scatter'],4);
        }

        //统计各博物馆日波动(温度/湿度)超标情况 不剔除异常值
        $wave_data = array();
        if(in_array("temperature",$this->env_param) || in_array("humidity",$this->env_param)){
            $dep_datas = $this->db
                ->select("mid,date,wave_status")
                ->where_in("date",$this->date_list) //按天统计
                ->where("env_type",$env)
                ->where_in("param",$wave_params)
                ->where("wave_status>",3)
                ->group_by("mid")
                ->get("data_envtype_param")
                ->result_array();
            if($dep_datas){
                foreach($dep_datas as $v){
                    $wave_data[$v['mid']] = $v['wave_status']; //波动超标数据
                }
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
        $mdatas = $this->db->order_by("id asc")->get("museum")->result_array();//所有博物馆基础信息(名称、坐标)
        $map_data['map_name'] = app_config("map_name");
        foreach($mdatas as $val){
            $map_data['data'][] = array(
                "mid"=>$val['id'],
                "name"=>$val['name'],
                "grid"=>array((float)$val['longitude'],(float)$val['latitude']),
                "list"=>array(
                    array(
                        "compliance"=>(isset($datas[$val['id']]) && $datas[$val['id']]['temperature_total']>0)?number_format($datas[$val['id']]['standard_percent'],4)*100 . "%":null,
                        "temperature_scatter"=>(isset($datas[$val['id']]) && $datas[$val['id']]['temperature_total']>0)?$datas[$val['id']]['scatter_temperature']*100 . "%":null,
                        "humidity_scatter"=>isset($scatter_humidity[$val['id']])?$scatter_humidity[$val['id']]*100 . "%":null,
                        "is_wave_abnormal"=>isset($wave_data[$val['id']])?"是":"无",
                        "is_value_abnormal"=>isset($abnormal_data[$val['id']])?"是":"无"
                    )
                )
            );
        }

        if($mid){//参与对比的博物馆
            $c_data['map_name'] = $map_data['map_name'];
            $mid = explode(",",$mid);
            foreach($map_data['data'] as $v){
                foreach($mid as $v1){
                    if($v['mid'] == $v1){
                       $c_data['data'][] = $v;
                    }
                }
            }
            $this->response($c_data);
        }else{
            $this->response($map_data);
        }
    }

    //统计博物馆数量统计
    public function statistic_get(){
        $data = array();
        $data["total"] = $this->db->count_all_results("museum");
        $data['show'] = $data['total'];

        $this->response($data);
    }

    //地图-博物馆按时间对比
    public function map_by_time_get(){
        if(!$this->get("mid") || !$this->get("btime") || !$this->get("etime"))
            $this->response(array("error"=>"缺少必要参数！"));
        if(!$this->get("env_type") || !$this->get("env_param"))
            $this->response(array("error"=>"缺少必要参数！"));
        $mid = $this->get("mid");
        if(!is_numeric($mid)) $this->response(array("error"=>"博物馆id格式错误！"));
        $date_compare = array($this->get("btime"),$this->get("etime")); //对比时间
        $env = $this->env_type;
        $param = $this->env_param;
        $params = array();//环境参数编号
        $wave_params = array();//日波动相关的环境参数编号
        foreach($param as $v){
            if($v=="humidity" && $this->env_type != "展厅") {array_push($params,1,2,3,12);array_push($wave_params,1,2,3,12);}
            elseif($v=="humidity" && $this->env_type == "展厅") {array_push($params,10);array_push($wave_params,10);}
            elseif($v=="light" && $this->env_type != "展厅") array_push($params,4,5,6,13);
            elseif($v=="light" && $this->env_type == "展厅") array_push($params,11);
            elseif($v=="temperature") {array_push($params,7);array_push($wave_params,7);}
            elseif($v=="uv") array_push($params,8);
            elseif($v=="voc") array_push($params,9);
        }

        //统计一个博物馆下两个日期的离散、达标率
        $totalstr = '';
        $abnormalstr = '';
        foreach ($param as $v) {
            $totalstr .= "+{$v}_total";
            $abnormalstr .= "+{$v}_abnormal";
        }
        $totalstr = "(".substr($totalstr, 1).")";
        $abnormalstr = "(".substr($abnormalstr, 1).")";
        $sp = "(".$totalstr."-".$abnormalstr.")/$totalstr "." as standard_percent";
        $datas = array();
        foreach($date_compare as $date){
            //统计湿度离散
            $scatter = "AVG(standard/average) as scatter";
            $dep_datas = $this->db
                ->select('mid,env_type,param,'.$scatter)
                ->where("date","D".$date)
                ->where("env_type",$this->env_type)
                ->where_in("param",array(1,2,3,10,12))
                ->where("mid",$mid)
                ->group_by("mid")
                ->get("data_envtype_param")
                ->result_array();
            if($dep_datas) $scatter_humidity[$date] = (number_format($dep_datas[0]['scatter'],4)*100)."%";
            else $scatter_humidity[$date] = null;
            //统计温度离散、达标率
            $dc_datas = $this->db
                ->select("distinct(mid),scatter_temperature,temperature_total,".$sp)
                ->where("date","D".$date)
                ->where("mid",$mid)
                ->where("env_type",$env)
                ->get("data_complex")
                ->result_array();
            if($dc_datas){
                $datas[$date] = array(
                    "mid"=>$mid,
                    "scatter_temperature"=>$dc_datas[0]['temperature_total']>0?($dc_datas[0]['scatter_temperature']*100)."%":null,
                    "standard_percent"=>$dc_datas[0]['temperature_total']>0?(round($dc_datas[0]['standard_percent'],4)*100)."%":null,
                );
            }else{
                $datas[$date] = array(
                    "mid"=>$mid,
                    "scatter_temperature"=>null,
                    "standard_percent"=>null
                );
            }
        }

        //统计单个博物馆日波动(温度/湿度)超标情况 不剔除异常值
        $wave_data = array();
        if(in_array("temperature",$this->env_param) || in_array("humidity",$this->env_param)){
            foreach($date_compare as $date){
                $dep_datas = $this->db
                    ->select("mid,date,wave_status")
                    ->where("date","D".$date) //按天统计
                    ->where("env_type",$env)
                    ->where("mid",$mid)
                    ->where_in("param",$wave_params)
                    ->where("wave_status>",3)
                    ->get("data_envtype_param")
                    ->result_array();
                if($dep_datas){
                    $wave_data[$date] = true;
                }else{
                    $wave_data[$date] = false;
                }
            }

        }

        //统计单个博物馆异常值情况
        $abnormal_data = array();
        foreach($date_compare as $date){
            $dep_datas = $this->db
                ->select("mid,date,SUM(count_abnormal) as number")
                ->where("date","D".$date)
                ->where("env_type",$env)
                ->where_in("param",$params)
                ->where("mid",$mid)
                ->group_by("mid")
                ->having("SUM(count_abnormal) > 0")
                ->get("data_envtype_param")
                ->result_array();
            if($dep_datas){
                $abnormal_data[$date] = true;
            }else{
                $abnormal_data[$date] = false;
            }
        }

        //所有博物馆基础信息(名称、坐标)
        $mdatas = $this->db->where("id",$mid)->get("museum")->result_array();
        $mdatas = $mdatas[0];

        //返回格式
        $map_data['map_name'] = app_config("map_name");
        $map_data['data']= array(
            "mid"=>$mid,
            "name"=>$mdatas['name'],
            "grid"=>array((float)$mdatas['longitude'],(float)$mdatas['latitude']),
            "list"=>array(
                array(
                    "date"=>$date_compare[0],
                    "compliance"=> $datas[$date_compare[0]]['standard_percent'],
                    "temperature_scatter"=>$datas[$date_compare[0]]['scatter_temperature'],
                    "humidity_scatter"=>$scatter_humidity[$date_compare[0]],
                    "is_wave_abnormal"=>$wave_data[$date_compare[0]]?"是":"无",
                    "is_value_abnormal"=>$abnormal_data[$date_compare[0]]?"是":"无"
                ),
                array(
                    "date"=>$date_compare[1],
                    "compliance"=> $datas[$date_compare[1]]['standard_percent'],
                    "temperature_scatter"=>$datas[$date_compare[1]]['scatter_temperature'],
                    "humidity_scatter"=>$scatter_humidity[$date_compare[1]],
                    "is_wave_abnormal"=>$wave_data[$date_compare[1]]?"是":"无",
                    "is_value_abnormal"=>$abnormal_data[$date_compare[1]]?"是":"无"
                )
            )
        );

        $this->response($map_data);
    }

}