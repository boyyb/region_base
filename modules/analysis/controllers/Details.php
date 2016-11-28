<?php
class Details extends MY_Controller{

    protected $mids = array(); //参与对比博物馆id
    protected $date = null; //查询日期
    protected $date_start = null; //日波动统计开始日期
    protected $date_end = null; //日波动统计结束日期
    protected $date_list = array();//日波动日期列表

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

        $this->mids = explode(",",$this->get("mids")); //mids=2,3,4
        $this->date_list = $this->_date_list($this->date_start,$this->date_end);

    }

    //生成日期列表
    protected function _date_list($s,$e){
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }

    //数据统计
    protected function _data($param_id){
        $data = array();
        //各博物馆均值、极差、标准差、异常值、达标率...
        foreach($this->mids as $mid){
            $dep_datas = $this->db
                ->where("date",$this->date)
                ->where("env_type",$this->env_type)
                ->where("mid",$mid)
                ->where("param",$param_id)
                ->get("data_envtype_param")
                ->result_array();
            $datas[$mid] = isset($dep_datas[0])?$dep_datas[0]:null;
        }
        foreach($datas as $mid=>$v){
            if(!$v) continue;
            //异常值统计
            $value_abnormal = array();
            if($v['count_abnormal']){ //存在异常值
                $value_abnormal = $this->db
                    ->select("CONCAT(`date`,\" \",`time`) as date,equip_no,val")
                    ->where("depid",$v['id'])
                    ->get("data_abnormal")
                    ->result_array();
            }
            //日波动统计(按天统计)
            $wave_abnormal = $wave_abnormal2 = array();
            $wave_arr = array();
            if(in_array($param_id,array(1,2,3,7,10,12))){ //仅计算温湿度
                foreach($this->date_list as $date) {
                    $dep_data = $this->db
                        ->where("date", "D" . $date)
                        ->where("env_type", $this->env_type)
                        ->where("mid", $mid)
                        ->where("param", $param_id)
                        ->get("data_envtype_param")
                        ->result_array();
                    if(!$dep_data) continue;
                    $wave_arr['min'][] = explode(",", $dep_data[0]['wave'])[0];
                    $wave_arr['max'][] = explode(",", $dep_data[0]['wave'])[1];
                    $wave_arr['min2'][] = explode(",", $dep_data[0]['wave'])[2];
                    $wave_arr['max2'][] = explode(",", $dep_data[0]['wave'])[3];
                    if($dep_data[0]['wave_status']>0){ //存在日波动异常
                        foreach(array(0,1) as $type){
                            $dwa_datas = $this->db
                                ->select("val,env_name,date")
                                ->where("depid",$dep_data[0]['id'])
                                ->where("type",$type)
                                ->get("data_wave_abnormal")
                                ->result_array();
                            if($type == 0) $wave_abnormal = array_merge($wave_abnormal,$dwa_datas); //累加每天的波动异常数据
                            else $wave_abnormal2 = array_merge($wave_abnormal2,$dwa_datas);
                        }
                    }
                }
            }
            //各自波动数据取并集
            $wave_min = isset($wave_arr['min'])?min($wave_arr['min']):null;
            $wave_max = isset($wave_arr['max'])?max($wave_arr['max']):null;
            $wave_min2 = isset($wave_arr['min2'])?min($wave_arr['min2']):null;
            $wave_max2 = isset($wave_arr['max2'])?max($wave_arr['max2']):null;

            $data[] = array(
                "mid"=>$mid,
                "name"=>$this->museum[$mid],
                "min"=>$v['min'],
                "max"=>$v["max"],
                "average"=>$v['average'],
                "middle"=>$v['middle'],
                "distance"=>(string)($v["max"]-$v['min']),
                "compliance"=>$v['compliance'],
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

        return $data;
    }

    //数据调用
    public function data(){
        foreach($this->env_param as $param){
            $ret[$param]['unit'] = $this->unit[$param]; //环境参数单位
            if($param == "temperature"){
                $ret["temperature"]['list'] = $this->_data(7);
            }elseif($param == "uv"){
                $ret['uv']['list'] = $this->_data(8);
            }elseif($param == "voc"){
                $ret['voc']['list'] = $this->_data(9);
            }elseif($param == "humidity" && $this->env_type != "展厅"){ // 展柜/库房要分材质
                $ret['humidity']['data'][] = array(
                    "texture"=>"石质、陶器、瓷器",
                    "list"=>$this->_data(1)
                );
                $ret['humidity']['data'][] = array(
                    "texture"=>"铁质、青铜",
                    "list"=>$this->_data(2)
                );
                $ret['humidity']['data'][] = array(
                    "texture"=>"纸质、壁画、纺织品、漆木器、其他",
                    "list"=>$this->_data(3)
                );
                $ret['humidity'][] = array(
                    "texture"=>"混合材质",
                    "data"=>$this->_data(12)
                );
            }elseif($param == "humidity" && $this->env_type == "展厅"){ //展厅不分材质
                $ret['humidity']['list'] = $this->_data(10);
            }elseif($param == "light" && $this->env_type == "展厅"){ //展厅不分材质
                $ret['light']['list'] = $this->_data(11);
            }elseif($param == "light" && $this->env_type != "展厅"){ //展柜库房分材质
                $ret['light']['data'][] = array(
                    "texture"=>"石质、陶器、瓷器、铁质、青铜",
                    "list"=>$this->_data(4)
                );
                $ret['light']['data'][] = array(
                    "texture"=>"纸质、壁画、纺织品",
                    "list"=>$this->_data(5)
                );
                $ret['light']['data'][] = array(
                    "texture"=>"漆木器、其他",
                    "list"=>$this->_data(6)
                );
                $ret['light']['data'][] = array(
                    "texture"=>"混合材质",
                    "list"=>$this->_data(13)
                );
            }
        }

        $this->response($ret);
    }




}