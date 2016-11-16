<?php
class history extends MY_Controller{
    public $mids = array();
    public $museum = array();
    public function __construct(){
        parent::__construct();
        $mid = $this->get("mid"); //?mid=2,2,4
        $mids = explode(",",$mid);
        $this->mids = $mids;//参与对比的博物馆id
        $this->get_museum_info();
    }

    protected function date_list($s,$e)
    {
        $date = array();
        for ($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }
    //博物馆数据 id=>名称
    protected function get_museum_info(){
        $datas = $this->db->order_by("id asc")->get('museum')->result_array();
        foreach($datas as $v){
            $this->museum[$v['id']] = $v['name'];
        }
    }
    //历史达标率
    public function standard_percent(){
        $env = $this->env_type = "展厅";
        $date_start = $this->start_time = 20161024;
        $date_end = $this->end_time = 20161028;
        $date_list = $this->date_list($date_start,$date_end);
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");
        $mids = $this->mids = array(2,3,4);

        $totalstr = '';
        $abnormalstr = '';
        foreach ($param as $v) {
            $totalstr .= "+{$v}_total";
            $abnormalstr .= "+{$v}_abnormal";
        }
        $totalstr = "(".substr($totalstr, 1).")";
        $abnormalstr = "(".substr($abnormalstr, 1).")";
        $sp = "(".$totalstr."-".$abnormalstr.")/$totalstr"."as standard_percent";

        //各个博物馆分日期显示达标率
        foreach($mids as $mid) {
            foreach($date_list as $date){
                $dc_datas = $this->db
                    ->select("mid,date," . $sp)
                    ->where("date", $date)
                    ->where("env_type", $env)
                    ->where("mid", $mid)
                    ->get("data_complex")
                    ->result_array();
                if($dc_datas) $datas[$mid][$date] = $dc_datas[0]['standard_percent'];
                else $datas[$mid][$date] = null;
            }
        }

        foreach($datas as $k => $v){
            $ret[] = array(
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }
        var_dump($ret);
        echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    }

    //稳定性（温度/湿度）
    public function stability(){
        $env = $this->env_type = "展厅";
        $date_start = $this->start_time = 20161024;
        $date_end = $this->end_time = 20161028;
        $date_list = $this->date_list($date_start,$date_end);
        //$param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");
        $mids = $this->mids = array(2,3,4);//测试数据

        //各个博物馆分日期显示温度湿度离散数据
        foreach($mids as $mid) {
            foreach($date_list as $date){
                $dc_datas = $this->db
                    ->select("mid,date,scatter_temperature,scatter_humidity")
                    ->where("date", $date)
                    ->where("env_type", $env)
                    ->where("mid", $mid)
                    ->get("data_complex")
                    ->result_array();
                if($dc_datas) {
                    $tc_datas[$mid][$date] = $dc_datas[0]['scatter_temperature'];
                    $hc_datas[$mid][$date] = $dc_datas[0]['scatter_humidity'];
                } else {
                    $tc_datas[$mid][$date] = null;
                    $hc_datas[$mid][$date] = null;
                }
            }
        }

        foreach($tc_datas as $k=>$v){
            $ret['temperature_scatter'][] = array(
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        foreach($hc_datas as $k=>$v){
            $ret['humidity_scatter'][] = array(
                "name"=>$this->museum[$k],
                "data"=>array_values($v)
            );
        }

        var_dump($ret);
        echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    }



}