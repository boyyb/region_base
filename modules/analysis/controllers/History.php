<?php
class History extends MY_Controller{

    protected $mids = array(); //存放参与对比博物馆的id
    protected $date_start = null; //查询开始日期
    protected $date_end = null; //查询结束日期
    protected $date_list = array(); //日期列表
    protected $week = array();

    public function __construct(){
        parent::__construct();
        if(!$this->get("env_type") || !$this->get("definite_time") || !$this->get("env_param") || !$this->get("mids"))
            $this->response(array("error"=>"缺少必要参数！"));
        $this->mids = explode(",",$this->get("mids")); //mids=2,3,4
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
                }else{ //本周数据
                    $this->date_start = date("Ymd",mktime(0,0,0,date("m"),date("d")-(date("w")==0?7:date("w"))+1,date("Y")));
                    $this->date_end = date("Ymd",strtotime('-1 day'));
                }
                break;
            case "month": //本月
                if(date("d") == "01"){ //1号查上月数据
                    $this->date_start = date("Ymd",mktime(0,0,0,date('m')-1,1,date('y')));
                    $this->date_end = date("Ymd",mktime(23,59,59,date("m"),0,date("y")));
                }else{
                    $this->date_start = date("Ymd",mktime(0,0,0,date('m'),1,date('y')));
                    $this->date_end = date("Ymd",strtotime('-1 day'));
                }
                break;
            default:
                $this->date_start = $this->date_end = $date_str;
        }

        $this->date_list = $this->_date_list($this->date_start,$this->date_end);
        if(count($this->date_list) < 2) $this->response(array("error"=>"查询结果为单天数据！"));
    }

    //生成日期列表
    protected function _date_list($s,$e){
        $weekarray=array("周日","周一","周二","周三","周四","周五","周六");
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
            $this->week[] = $weekarray[date("w",$i)];
        }
        return $date;
    }

    //历史达标率-多博物馆对比
    public function compliance_get(){
        //构建达标率计算字符串
        $totalstr = '';
        $abnormalstr = '';
        foreach ($this->env_param as $v) {
            $totalstr .= "+{$v}_total";
            $abnormalstr .= "+{$v}_abnormal";
        }
        $totalstr = "(".substr($totalstr, 1).")";
        $abnormalstr = "(".substr($abnormalstr, 1).")";
        $sp = "(".$totalstr."-".$abnormalstr.")/$totalstr "." as standard_percent";

        //各个博物馆分日期显示达标率 按照每天统计
        foreach($this->mids as $mid) {
            foreach($this->date_list as $date){
                $dc_datas = $this->db
                    ->select("mid,date,{$totalstr} as total,{$sp}")
                    ->where("date", "D".$date)
                    ->where("env_type", $this->env_type)
                    ->where("mid", $mid)
                    ->get("data_complex")
                    ->result_array();
                if($dc_datas && $dc_datas[0]['total']) //include sp=0
                    $datas[$mid][$date] = round($dc_datas[0]['standard_percent'],3);
                else $datas[$mid][$date] = null;
            }
            $names[] = $this->museum[$mid];
        }
        //构建返回数据格式
        $ret['title'] = "历史达标率";
        $ret['names'] = $names;
        if($this->get("definite_time") == "week") $ret['date'] = $this->week;
        else $ret['date'] = $this->date_list;
        foreach($datas as $k => $v){
            $ret['list'][] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        $this->response($ret);
    }

    //稳定性（温度/湿度）- 多博物馆对比
    protected function scatter(){
        //各个博物馆分日期显示温度湿度离散数据
        foreach($this->mids as $mid) {
            foreach($this->date_list as $date){
                $dc_datas = $this->db
                    ->select("mid,date,scatter_temperature,scatter_humidity")
                    ->where("date", "D".$date)
                    ->where("env_type", $this->env_type)
                    ->where("mid", $mid)
                    ->get("data_complex")
                    ->result_array();
                if($dc_datas && $dc_datas[0]['scatter_temperature']) //排除null值
                    $tc_datas[$mid][$date] = (float)$dc_datas[0]['scatter_temperature'];
                else $tc_datas[$mid][$date] = null;

                if($dc_datas && $dc_datas[0]['scatter_humidity'])
                    $hc_datas[$mid][$date] = (float)$dc_datas[0]['scatter_humidity'];
                else $hc_datas[$mid][$date] = null;
            }
            $names[] = $this->museum[$mid];
        }

        if($this->get("definite_time") == "week") $date = $this->week;
        else $date = $this->date_list;
        $ret['temperature']['title'] = "历史稳定性-温度（离散系数）";
        $ret['humidity']['title'] = "历史稳定性-湿度（离散系数）";
        $ret['temperature']['names'] = $ret['humidity']['names'] =$names;
        $ret['temperature']['date'] = $ret['humidity']['date'] =$date;
        //温度
        foreach($tc_datas as $k=>$v){
            $ret['temperature']['list'][] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }
        //湿度
        foreach($hc_datas as $k=>$v){
            $ret['humidity']['list'][] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        return $ret;
    }

    public function scatter_temperature_get(){
        $data = $this->scatter();
        $this->response($data['temperature']);
    }

    public function scatter_humidity_get(){
        $data = $this->scatter();
        $this->response($data['humidity']);
    }

}