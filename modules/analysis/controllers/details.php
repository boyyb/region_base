<?php
class details extends MY_Controller{

    public $mids = array(); //参与对比博物馆id
    public $date = null; //对比日期
    public $date_start = null; //日波动统计开始日期
    public $date_end = null; //日波动统计结束日期
    public $date_list = array();//日波动日期列表

    function __construct(){
        parent::__construct();
        $date_str = $this->get("definite_time");
        switch ($date_str){
            case "yesterday": //昨天
                $this->date = "D".date("Ymd",time() - 24*60*60);
                $this->date_start = $this->date_end = date("Ymd",time() - 24*60*60);
                break;
            case "before_yes": //前天
                $this->date = "D".date("Ymd",time() - 24*60*60*2);
                $this->date_start = $this->date_end = date("Ymd",time() - 24*60*60*2);
                break;
            case "week": //本周
                $this->date = "W".date("YW");
                $this->date_start = date("Ymd",mktime(0,0,0,date('m'),date('d')-date('w')+1,date('y')));
                $this->date_end = date("Ymd",time() - 24*60*60);
                break;
            case "month": //本月
                $this->date = "M".date("Ym");
                $this->date_start = date("Ymd",mktime(0,0,0,date('m'),1,date('y')));
                $this->date_end = date("Ymd",time() - 24*60*60);
                break;
            default:
                $this->date = "D".$date_str;
                $this->date_start = $this->date_end = $date_str; //指定某一天


        }
        $this->mids = explode(",",$this->get("mids")); //mids=2,3,4
        $this->date_list = $this->_date_list($this->date_start,$this->date_end);


        var_dump($this->date_list);
    }

/*if($v=="humidity") array_push($params,1,2,3,10); //加混合材质 10
elseif($v=="light") array_push($params,4,5,6,11); //加混合材质 11
elseif($v=="temperature") array_push($params,7);
elseif($v=="uv") array_push($params,8);
elseif($v=="voc") array_push($params,9);*/

    //生成日期列表
    protected function _date_list($s,$e){
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }

    //温度/uv/voc
    public function unclassified_data(){
        $data = array();
        //各博物馆均值、极差、标准差、异常值、达标率...
        foreach($this->mids as $mid){
            $dep_datas = $this->db
                ->where("date",$this->date)
                ->where("env_type",$this->env_type)
                ->where("mid",$mid)
                ->where("param",7)
                ->get("data_envtype_param_copy")
                ->result_array();
            $datas[$mid] = isset($dep_datas[0])?$dep_datas[0]:null;
        }
        foreach($datas as $mid=>$v){
            if(!$v) continue;
            //异常值统计
            $value_abnormal = array();
            if($v['count_abnormal']){
                $value_abnormal = $this->db
                    ->select("CONCAT(`date`,\" \",`time`) as date,equip_no,val")
                    ->where("depid",$v['id'])
                    ->get("data_abnormal")
                    ->result_array();
            }
            //日波动统计(天数据直接取值，周月数据分别统计天)
            $wave_abnormal = $wave_abnormal2 = array();
            $wave_min = $wave_max = $wave_min2 = $wave_max2 = null;
            if($this->date_start == $this->date_end){ //天数据
                $wave_min = explode(",",$v['wave'])[0];
                $wave_max = explode(",",$v['wave'])[1];
                $wave_min2 = explode(",",$v['wave'])[2];
                $wave_max2 = explode(",",$v['wave'])[3];
                if($v['wave_status']>0){//存在波动超标
                    foreach(array(0,1) as $type){
                        $dwa_datas = $this->db
                            ->select("val,env_name,date")
                            ->where("depid",$v['id'])
                            ->where("type",$type)
                            ->get("data_wave_abnormal")
                            ->result_array();
                        if($type == 0) $wave_abnormal = $dwa_datas;
                        else $wave_abnormal2 = $dwa_datas; //剔除异常值的波动超标数据
                    }
                }
            }else{ // 周/月数据
                foreach($this->date_list as $date) {
                    $dep_data = $this->db
                        ->where("date", "D" . $date)
                        ->where("env_type", $this->env_type)
                        ->where("mid", $mid)
                        ->where("param", 7)
                        ->get("data_envtype_param_copy")
                        ->result_array();
                    if(!$dep_data) continue;
                    var_dump($dep_data);
                    $wave_arr['min'][] = explode(",", $dep_data[0]['wave'])[0];
                    $wave_arr['max'][] = explode(",", $dep_data[0]['wave'])[1];
                    $wave_arr['min2'][] = explode(",", $dep_data[0]['wave'])[2];
                    $wave_arr['max2'][] = explode(",", $dep_data[0]['wave'])[3];
                    if($dep_data[0]['wave_status']>0){
                        foreach(array(0,1) as $type){
                            $dwa_datas = $this->db
                                ->select("val,env_name,date")
                                ->where("depid",$dep_data[0]['id'])
                                ->where("type",$type)
                                ->get("data_wave_abnormal")
                                ->result_array();
                            if($type == 0) $wave_abnormal = array_merge($wave_abnormal,$dwa_datas);
                            else $wave_abnormal2 = array_merge($wave_abnormal2,$dwa_datas);
                        }
                    }
                }
                $wave_min = isset($wave_arr['min'])?min($wave_arr['min']):null;
                $wave_max = isset($wave_arr['max'])?max($wave_arr['max']):null;
                $wave_min2 = isset($wave_arr['min2'])?min($wave_arr['min2']):null;
                $wave_max2 = isset($wave_arr['max2'])?max($wave_arr['max2']):null;


            }

            $data[] = array(
                "mid"=>$mid,
                "name"=>$this->museum[$mid],
                "min"=>$v['min'],
                "max"=>$v["max"],
                "average"=>$v['average'],
                "middle"=>$v['middle'],
                "distance"=>(string)($v["max"]-$v['min']),
                "standard_percent"=>$v['compliance'],
                "standard"=>$v['standard'],
                "count_abnormal"=>$v['count_abnormal'],
                "value_abnormal"=>$value_abnormal,
                "wave_min"=>$wave_min,
                "wave_max"=>$wave_max,
                "wave_min2"=>$wave_min2,
                "wave_max2"=>$wave_max2,
                "wave_abnormal"=>$wave_abnormal,
                "wave_abnormal2"=>$wave_abnormal2
            );
        }

        var_dump($data);
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    //湿度
    public function humidity(){
        $data = array();
        //各博物馆均值、极差、标准差、异常值、达标率...
        foreach($this->mids as $mid){
            $dep_datas = $this->db
                ->where("date",$this->date)
                ->where("env_type",$this->env_type)
                ->where("mid",$mid)
                ->where("param",7)
                ->get("data_envtype_param_copy")
                ->result_array();
            $datas[$mid] = isset($dep_datas[0])?$dep_datas[0]:null;
        }
        foreach($datas as $mid=>$v){
            if(!$v) continue;
            //异常值统计
            $value_abnormal = array();
            if($v['count_abnormal']){
                $value_abnormal = $this->db
                    ->select("CONCAT(`date`,\" \",`time`) as date,equip_no,val")
                    ->where("depid",$v['id'])
                    ->get("data_abnormal")
                    ->result_array();
            }
            //日波动统计(天数据直接取值，周月数据分别统计天)
            $wave_abnormal = $wave_abnormal2 = array();
            $wave_min = $wave_max = $wave_min2 = $wave_max2 = null;
            if($this->date_start == $this->date_end){ //天数据
                $wave_min = explode(",",$v['wave'])[0];
                $wave_max = explode(",",$v['wave'])[1];
                $wave_min2 = explode(",",$v['wave'])[2];
                $wave_max2 = explode(",",$v['wave'])[3];
                if($v['wave_status']>0){//存在波动超标
                    foreach(array(0,1) as $type){
                        $dwa_datas = $this->db
                            ->select("val,env_name,date")
                            ->where("depid",$v['id'])
                            ->where("type",$type)
                            ->get("data_wave_abnormal")
                            ->result_array();
                        if($type == 0) $wave_abnormal = $dwa_datas;
                        else $wave_abnormal2 = $dwa_datas; //剔除异常值的波动超标数据
                    }
                }
            }else{ // 周/月数据
                foreach($this->date_list as $date) {
                    $dep_data = $this->db
                        ->where("date", "D" . $date)
                        ->where("env_type", $this->env_type)
                        ->where("mid", $mid)
                        ->where("param", 7)
                        ->get("data_envtype_param_copy")
                        ->result_array();
                    if(!$dep_data) continue;
                    var_dump($dep_data);
                    $wave_arr['min'][] = explode(",", $dep_data[0]['wave'])[0];
                    $wave_arr['max'][] = explode(",", $dep_data[0]['wave'])[1];
                    $wave_arr['min2'][] = explode(",", $dep_data[0]['wave'])[2];
                    $wave_arr['max2'][] = explode(",", $dep_data[0]['wave'])[3];
                    if($dep_data[0]['wave_status']>0){
                        foreach(array(0,1) as $type){
                            $dwa_datas = $this->db
                                ->select("val,env_name,date")
                                ->where("depid",$dep_data[0]['id'])
                                ->where("type",$type)
                                ->get("data_wave_abnormal")
                                ->result_array();
                            if($type == 0) $wave_abnormal = array_merge($wave_abnormal,$dwa_datas);
                            else $wave_abnormal2 = array_merge($wave_abnormal2,$dwa_datas);
                        }
                    }
                }
                $wave_min = isset($wave_arr['min'])?min($wave_arr['min']):null;
                $wave_max = isset($wave_arr['max'])?max($wave_arr['max']):null;
                $wave_min2 = isset($wave_arr['min2'])?min($wave_arr['min2']):null;
                $wave_max2 = isset($wave_arr['max2'])?max($wave_arr['max2']):null;


            }

            $data[] = array(
                "mid"=>$mid,
                "name"=>$this->museum[$mid],
                "min"=>$v['min'],
                "max"=>$v["max"],
                "average"=>$v['average'],
                "middle"=>$v['middle'],
                "distance"=>(string)($v["max"]-$v['min']),
                "standard_percent"=>$v['compliance'],
                "standard"=>$v['standard'],
                "count_abnormal"=>$v['count_abnormal'],
                "value_abnormal"=>$value_abnormal,
                "wave_min"=>$wave_min,
                "wave_max"=>$wave_max,
                "wave_min2"=>$wave_min2,
                "wave_max2"=>$wave_max2,
                "wave_abnormal"=>$wave_abnormal,
                "wave_abnormal2"=>$wave_abnormal2
            );
        }

        var_dump($data);
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }







}