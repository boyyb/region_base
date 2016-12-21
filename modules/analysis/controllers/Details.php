<?php
class Details extends MY_Controller{

    protected $mids = array(); //参与对比博物馆id
    protected $date = null; //查询日期
    protected $date_start = null; //日波动统计开始日期
    protected $date_end = null; //日波动统计结束日期
    protected $date_list = array(); //日波动日期列表

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
            default: //指定具体某一天
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
            if(!$v) { //没有对应数据，相关统计数据全部置0
                $data[] = array(
                    "empty"=>true,
                    "mid"=>(string)$mid,
                    "name"=>$this->museum[$mid],
                );
                continue;
            }
            //异常值统计
            $value_abnormal = array();
            if($v['count_abnormal']){ //存在异常值
                $value_abnormal = $this->db
                    ->select("id,CONCAT(`date`,\" \",`time`) as date,equip_no,val")
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
                    $temp = explode(",",$dep_data[0]['wave']);
                    $wave_arr['min'][] = array("value"=>$temp[0],"status"=>substr(sprintf("%04d", decbin($dep_data[0]['wave_status'])),0,1));
                    $wave_arr['max'][] = array("value"=>$temp[1],"status"=>substr(sprintf("%04d", decbin($dep_data[0]['wave_status'])),1,1));
                    $wave_arr['min2'][] = array("value"=>$temp[2],"status"=>substr(sprintf("%04d", decbin($dep_data[0]['wave_status'])),2,1));
                    $wave_arr['max2'][] = array("value"=>$temp[3],"status"=>substr(sprintf("%04d", decbin($dep_data[0]['wave_status'])),3,1));
                    if($dep_data[0]['wave_status']>0){ //存在日波动异常
                        foreach(array(0,1) as $type){
                            $dwa_datas = $this->db
                                ->select("id,val,env_name,date")
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
            foreach($wave_arr as $k=>$v1){
                usort($v1,function($x,$y){  //从小到大排序
                    if($x['value']==$y['value']){
                        return 0;//value相等的不用排
                    }
                    return $x['value']>$y['value']?1:-1;
                });
                if($k=="min" || $k=="min2") $wave[$k] = reset($v1);
                else $wave[$k] = end($v1);

            }

            $wave_min = isset($wave['min'])?$wave['min']:null;
            $wave_max = isset($wave['max'])?$wave['max']:null;
            $wave_min2 = isset($wave['min2'])?$wave['min2']:null;
            $wave_max2 = isset($wave['max2'])?$wave['max2']:null;

            $data[] = array(
                "id"=>$v['id'],
                "mid"=>(string)$mid,
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

    //数据调用-多博物馆对比-new
    public function data_get(){
        if(!$this->get("env_type") || !$this->get("definite_time") || !$this->get("env_param") || !$this->get("mids"))
            $this->response(array("error"=>"缺少必要参数！"));
        $param_list = array(
            "temperature"=>7,
            "uv"=>8,
            "voc"=>9,
            "humidity"=>array(
                "hall"=>10,
                "other"=>array( //非展厅：展柜和库房
                    1=>"石质、陶器、瓷器",
                    2=>"铁质、青铜",
                    3=>"纸质、壁画、纺织品、漆木器、其他",
                    12=>"混合材质"),
            ),
            "light"=>array(
                "hall"=>11,
                "other"=>array(
                    4=>"石质、陶器、瓷器、铁质、青铜",
                    5=>"纸质、壁画、纺织品",
                    6=>"漆木器、其他",
                    13=>"混合材质"),
            )
        );
        $data = array();
        foreach($this->env_param as $param){
            $data[$param]["unit"] = $this->unit[$param];
            if(is_array($param_list[$param])){ // 温度/光照
                if($this->env_type=="展厅"){ //不分材质
                    $data[$param]["list"] = $this->_data($param_list[$param]["hall"]);
                    $min_list = array_column($data[$param]["list"],"min")?array_column($data[$param]["list"],"min"):array(0);
                    $max_list = array_column($data[$param]["list"],"max")?array_column($data[$param]["list"],"max"):array(0);
                    $data[$param]['left']=min($min_list);
                    $data[$param]['right']=max($max_list);
                }else{ //展柜/库房分材质
                    foreach($param_list[$param]['other'] as $k=>$v){
                        $tmp = $this->_data($k);
                        $min_list = array_column($tmp,"min")?array_column($tmp,"min"):array(0);
                        $max_list = array_column($tmp,"max")?array_column($tmp,"max"):array(0);
                        $data[$param]["data"][] = array(
                            "texture"=>$v,
                            "list"=>$tmp,
                            "left"=>min($min_list),
                            "right"=>max($max_list)
                        );
                    }
                }
            }else{ //无需分类的 温度/uv/voc
                $data[$param]["list"] = $this->_data($param_list[$param]);
                $min_list = array_column($data[$param]["list"],"min")?array_column($data[$param]["list"],"min"):array(0);
                $max_list = array_column($data[$param]["list"],"max")?array_column($data[$param]["list"],"max"):array(0);
                $data[$param]['left']=min($min_list);
                $data[$param]['right']=max($max_list);
            }
        }

        $this->response($data);
    }

    //数据统计-单博物馆按时间对比
    protected function _data_by_time($param_id){
        $data = array();
        $mid = $this->get("mid");
        $date_compare = array($this->get("btime"),$this->get("etime"));

        //各博物馆均值、极差、标准差、异常值、达标率...
        foreach($date_compare as $date){
            $dep_datas = $this->db
                ->where("date","D{$date}")
                ->where("env_type",$this->env_type)
                ->where("mid",$mid)
                ->where("param",$param_id)
                ->get("data_envtype_param")
                ->result_array();
            $datas[$date] = isset($dep_datas[0])?$dep_datas[0]:null;
        }

        foreach($datas as $date=>$v){
            if(!$v) { //没有对应数据
                $data[] = array(
                    "empty"=>true,
                    "mid"=>(string)$mid,
                    "name"=>$this->museum[$mid],
                    "date"=>(string)$date
                );
                continue;
            }
            //异常值统计
            $value_abnormal = array();
            if($v['count_abnormal']){ //存在异常值
                $value_abnormal = $this->db
                    ->select("id,CONCAT(`date`,\" \",`time`) as date,equip_no,val")
                    ->where("depid",$v['id'])
                    ->get("data_abnormal")
                    ->result_array();
            }
            //日波动统计(天数据直接统计)
            $wave_abnormal = $wave_abnormal2 = array();
            $wave_arr = array();
            if(in_array($param_id,array(1,2,3,7,10,12))){ //仅计算温湿度
                $temp = explode(",",$v['wave']);
                $wave_arr['min'] = array("value"=>$temp[0],"status"=>substr(sprintf("%04d", decbin($v['wave_status'])),0,1));
                $wave_arr['max'] = array("value"=>$temp[1],"status"=>substr(sprintf("%04d", decbin($v['wave_status'])),1,1));
                $wave_arr['min2'] = array("value"=>$temp[2],"status"=>substr(sprintf("%04d", decbin($v['wave_status'])),2,1));
                $wave_arr['max2'] = array("value"=>$temp[3],"status"=>substr(sprintf("%04d", decbin($v['wave_status'])),3,1));
                if($v['wave_status']>0){ //存在日波动异常
                    foreach(array(0,1) as $type){
                        $dwa_datas = $this->db
                            ->select("id,val,env_name,date")
                            ->where("depid",$v['id'])
                            ->where("type",$type)
                            ->get("data_wave_abnormal")
                            ->result_array();
                        if($type == 0) $wave_abnormal = $dwa_datas;
                        else $wave_abnormal2 = $dwa_datas;
                    }
                }
            }

            $data[] = array(
                "id"=>$v['id'],
                "mid"=>(string)$mid,
                "name"=>$this->museum[$mid],
                "date"=>(string)$date,
                "min"=>$v['min'],
                "max"=>$v["max"],
                "average"=>$v['average'],
                "middle"=>$v['middle'],
                "distance"=>(string)($v["max"]-$v['min']),
                "compliance"=>$v['compliance'],
                "standard"=>$v['standard'],
                "count_abnormal"=>$v['count_abnormal'],
                "value_abnormal"=>$value_abnormal,
                "wave_min"=>isset($wave_arr['min'])?$wave_arr['min']:null,
                "wave_max"=>isset($wave_arr['max'])?$wave_arr['max']:null,
                "wave_min2"=>isset($wave_arr['min2'])?$wave_arr['min2']:null,
                "wave_max2"=>isset($wave_arr['max2'])?$wave_arr['max2']:null,
                "wave_abnormal"=>$wave_abnormal,
                "wave_abnormal2"=>$wave_abnormal2
            );
        }

        return $data;
    }

    //数据调用-单个博物馆按时间对比-new
    public function data_by_time_get(){
        if(!$this->get("mid") || !$this->get("btime") || !$this->get("etime"))
            $this->response(array("error"=>"缺少必要参数！"));
        if(!$this->get("env_type") || !$this->get("env_param"))
            $this->response(array("error"=>"缺少必要参数！"));
        if(!is_numeric($this->get("mid"))) $this->response(array("error"=>"博物馆id格式错误！"));
        $param_list = array(
            "temperature"=>7,
            "uv"=>8,
            "voc"=>9,
            "humidity"=>array(
                "hall"=>10,
                "other"=>array( //非展厅：展柜和库房
                    1=>"石质、陶器、瓷器",
                    2=>"铁质、青铜",
                    3=>"纸质、壁画、纺织品、漆木器、其他",
                    12=>"混合材质"
                )
            ),
            "light"=>array(
                "hall"=>11,
                "other"=>array(
                    4=>"石质、陶器、瓷器、铁质、青铜",
                    5=>"纸质、壁画、纺织品",
                    6=>"漆木器、其他",
                    13=>"混合材质"
                )
            )
        );
        $data = array();
        foreach($this->env_param as $param){
            $data[$param]["unit"] = $this->unit[$param];
            if(is_array($param_list[$param])){ // 湿度/光照
                if($this->env_type=="展厅"){ //展厅不分材质
                    $data[$param]["list"] = $this->_data_by_time($param_list[$param]["hall"]);
                    $min_list = array_column($data[$param]["list"],"min")?array_column($data[$param]["list"],"min"):array(0);
                    $max_list = array_column($data[$param]["list"],"max")?array_column($data[$param]["list"],"max"):array(0);
                    $data[$param]['left']=min($min_list);
                    $data[$param]['right']=max($max_list);
                }else{ //展柜/库房分材质
                    foreach($param_list[$param]['other'] as $k=>$v){
                        $tmp = $this->_data_by_time($k);
                        $min_list = array_column($tmp,"min")?array_column($tmp,"min"):array(0);
                        $max_list = array_column($tmp,"max")?array_column($tmp,"max"):array(0);
                        $data[$param]["data"][] = array(
                            "texture"=>$v,
                            "list"=>$tmp,
                            "left"=>min($min_list),
                            "right"=>max($max_list)
                        );
                    }
                }
            }else{ //无需分类的 温度/uv/voc
                $data[$param]["list"] = $this->_data_by_time($param_list[$param]);
                $min_list = array_column($data[$param]["list"],"min")?array_column($data[$param]["list"],"min"):array(0);
                $max_list = array_column($data[$param]["list"],"max")?array_column($data[$param]["list"],"max"):array(0);
                $data[$param]['left']=min($min_list);
                $data[$param]['right']=max($max_list);
            }
        }

        $this->response($data);
    }



    //统计异常值-根据depid获取数据
    public function abnormal(){
        $depid = $this->get("depid");
        if(!$depid) $this->response(array("error"=>"缺少必要参数！"));
        $data = $this->db
            ->select("id,CONCAT(`date`,\" \",`time`) as date,equip_no,val")
            ->where("depid",$depid)
            ->get("data_abnormal")
            ->result_array();
        if(!$data) $this->response(array("error"=>"没有查询到数据！"));
        $this->response($data);
    }

    //统计日波动超标数据-多博物馆对比
    protected function _wave_abnormal($param_id){
        foreach($this->mids as $mid){
            $wave_abnormal = $wave_abnormal2 = array();
            foreach($this->date_list as $date) {
                $dep_data = $this->db
                    ->where("date", "D" . $date)
                    ->where("env_type", $this->env_type)
                    ->where("mid", $mid)
                    ->where("param", $param_id)
                    ->where("wave_status>",0)
                    ->get("data_envtype_param")
                    ->result_array();
                if(!$dep_data) continue;
                foreach(array(0,1) as $type){
                    $dwa_datas = $this->db
                        ->select("id,val,env_name,date")
                        ->where("depid",$dep_data[0]['id'])
                        ->where("type",$type)
                        ->get("data_wave_abnormal")
                        ->result_array();
                    if($type == 0) $wave_abnormal = array_merge($wave_abnormal,$dwa_datas); //累加每天的波动异常数据
                    else $wave_abnormal2 = array_merge($wave_abnormal2,$dwa_datas);
                }
            }

            $data[] = array(
                "mid"=>(string)$mid,
                "name"=>$this->museum[$mid],
                "wave_abnormal"=>$wave_abnormal,
                "wave_abnormal2"=>$wave_abnormal2
            );
        }

        return $data;
    }

    public function wave_abnormal(){
        $data['temperature'] = $this->_wave_abnormal(7);
        if($this->env_type == "展厅"){
            $data['humidity'] = $this->_wave_abnormal(10);
        }else{
            $data['humidity'][] = array(
                "texture"=>"石质、陶器、瓷器",
                "list"=>$this->_wave_abnormal(1)
            );
            $data['humidity'][] = array(
                "texture"=>"铁质、青铜",
                "list"=>$this->_wave_abnormal(2)
            );
            $data['humidity'][] = array(
                "texture"=>"纸质、壁画、纺织品、漆木器、其他",
                "list"=>$this->_wave_abnormal(3)
            );
            $data['humidity'][] = array(
                "texture"=>"混合材质",
                "data"=>$this->_wave_abnormal(12)
            );
        }

        $this->response($data);
    }

    protected function _wave_abnormal_by_time($param_id)
    {
        $data = array();
        $mid = $this->mids[0];
        $wave_abnormal = $wave_abnormal2 = array();
        foreach ($this->date_compare as $date) {
            $dep_data = $this->db
                ->where("date", "D" . $date)
                ->where("env_type", $this->env_type)
                ->where("mid", $mid)
                ->where("param", $param_id)
                ->where("wave_status>", 0)
                ->get("data_envtype_param")
                ->result_array();
            if ($dep_data) {
                foreach (array(0, 1) as $type) {
                    $dwa_datas = $this->db
                        ->select("id,val,env_name,date")
                        ->where("depid", $dep_data[0]['id'])
                        ->where("type", $type)
                        ->get("data_wave_abnormal")
                        ->result_array();
                    if ($type == 0) $wave_abnormal = $dwa_datas;
                    else $wave_abnormal2 = $dwa_datas;
                }
            }

            $data[] = array(
                "mid" => (string)$mid,
                "name" => $this->museum[$mid],
                "date" => $date,
                "wave_abnormal" => $wave_abnormal,
                "wave_abnormal2" => $wave_abnormal2
            );
        }

        return $data;
    }

    public function wave_abnormal_by_time(){
        if(count(array_filter($this->date_compare)) != 2) $this->response(array("error"=>"对比日期格式不正确！"));
        $data['temperature'] = $this->_wave_abnormal_by_time(7);
        if($this->env_type == "展厅"){
            $data['humidity'] = $this->_wave_abnormal_by_time(10);
        }else{
            $data['humidity'][] = array(
                "texture"=>"石质、陶器、瓷器",
                "list"=>$this->_wave_abnormal_by_time(1)
            );
            $data['humidity'][] = array(
                "texture"=>"铁质、青铜",
                "list"=>$this->_wave_abnormal_by_time(2)
            );
            $data['humidity'][] = array(
                "texture"=>"纸质、壁画、纺织品、漆木器、其他",
                "list"=>$this->_wave_abnormal_by_time(3)
            );
            $data['humidity'][] = array(
                "texture"=>"混合材质",
                "data"=>$this->_wave_abnormal_by_time(12)
            );
        }

        $this->response($data);
    }

}