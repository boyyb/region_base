<?php
class history extends MY_Controller{

    public $mids = array(); //存放参与对比博物馆的id
    public $date_start = null; //查询开始日期
    public $date_end = null; //查询结束日期
    public $date_list = array(); //日期列表

    public function __construct(){
        parent::__construct();
        $this->mids = explode(",",$this->get("mids")); //mids=2,3,4
        $date = $this->get("definite_time"); //接收日期字符串
        switch ($date){
            case "yesterday": //昨天
                $this->date_start = date("Ymd",strtotime('-1 day'));
                $this->date_end = $this->date_start;
                break;
            case "before_yes": //前天
                $this->date_start = date("Ymd",strtotime('-2 day'));
                $this->date_end = $this->date_start;
                break;
            case "week": //本周
                $this->date_start = date("Ymd",mktime(0,0,0,date('m'),date('d')-date('w')+1,date('y')));
                $this->date_end = date("Ymd",strtotime('-1 day'));
                break;
            case "month": //本月
                $this->date_start = date("Ymd",mktime(0,0,0,date('m'),1,date('y')));
                $this->date_end = date("Ymd",strtotime('-1 day'));
                break;
            default: $this->date_end = $this->date_start = $date; //指定某一天
        }
        $this->date_list = $this->_date_list($this->date_start,$this->date_end);
        if($this->date_end < $this->date_start) $this->response(array());// 跨周/月
    }

    //生成日期列表
    protected function _date_list($s,$e){
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }

    //历史达标率-多博物馆对比
    public function compliance(){
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
                if($dc_datas && $dc_datas[0]['total'])
                    $datas[$mid][$date] = round($dc_datas[0]['standard_percent'],3);
                else $datas[$mid][$date] = null;
            }
        }
        //构建返回数据格式
        foreach($datas as $k => $v){
            $ret[] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        $this->response($ret);
    }

    //稳定性（温度/湿度）- 多博物馆对比
    public function stability(){
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
                if($dc_datas && $dc_datas[0]['scatter_temperature'])
                    $tc_datas[$mid][$date] = (float)$dc_datas[0]['scatter_temperature'];
                else $tc_datas[$mid][$date] = null;

                if($dc_datas && $dc_datas[0]['scatter_humidity'])
                    $hc_datas[$mid][$date] = (float)$dc_datas[0]['scatter_humidity'];
                else $hc_datas[$mid][$date] = null;
            }
        }
        //温度
        foreach($tc_datas as $k=>$v){
            $ret['temperature_scatter'][] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }
        //湿度
        foreach($hc_datas as $k=>$v){
            $ret['humidity_scatter'][] = array(
                "mid"=>$k,
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        $this->response($ret);
    }


}