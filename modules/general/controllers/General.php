<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/10/31
 * Time: 10:24
 */
class General extends MY_Controller{

    function __construct()
    {
        parent::__construct();

        $this->load->helper(array("calculate"));
        $this->start_time = $this->get("start_time");// 20160101
        $this->end_time = $this->get("end_time");
        $this->definite_time = $this->get("definite_time");
        $this->env_type = $this->get("env_type");
        $this->env_param = $this->get("env_param");//array
        if($this->definite_time){
            switch ($this->definite_time){
                case "yesterday": //昨天
                    $this->start_time = $this->end_time = date("Ymd",time() - 24*60*60);
                    break;
                case "before_yes": //前天
                    $this->start_time = $this->end_time = date("Ymd",time() - 24*60*60*2);
                    break;
                case "week": //本周
                    $day_num = date("w");
                    $this->start_time = date("Ymd",time() - 24*60*60*($day_num-1));
                    $this->end_time = date("Ymd",time() + 24*60*60*(7-$day_num));
                    break;
                case "month": //本月
                    $this->start_time = date("Ym")."01";
                    $this->end_time = date("Ym").date("t");
                    break;
            }
        }
        $museum = $this->db->select("id,name")->get("museum")->result_array();
        foreach ($museum as $value){
            $this->museum[$value["id"]] = $value["name"];
        }


    }

    public function detail_standard_get($flag = false){ //区域详情达标率
        $params = '';
        $suffix = array("total","abnormal");
        $data_flag = $data_standard = array();
        foreach ($this->env_param as $param){
            $params .= ",c.".$param."_total".",c.".$param."_abnormal";
        }
        $data_compliance = $this->db->select("m.id,c.id as cid".$params)
                                    ->join("data_env e","e.mid=m.id")
                                    ->join("data_env_compliance c","c.eid=e.id")
                                    ->where("e.env_type",$this->env_type)
                                    ->where("c.date >=",$this->start_time)
                                    ->where("c.date <=",$this->end_time)
                                    ->get("museum m")
                                    ->result_array();
        //echo $this->db->last_query();exit;
        foreach ($data_compliance as $value){
            if($value["id"]){
                foreach ($this->env_param as $param){
                    foreach ($suffix as $s){
                        $data_flag[$value["id"]][$s][] = $value[$param."_".$s];
                    }
                }
            }
        }

        foreach ($data_flag as $k => $value){
                $total = array_sum($value["total"]);
                $abnormal = array_sum($value["abnormal"]);
                $data_standard[$k] = round(($total - $abnormal) / $total,2);
        }

        if($flag){
            return $data_standard;
        }

        $this->response($data_standard);
    }

    public function data_scatter_get($flag = false){ //昨日 前日 温湿度稳定系数
        $data = $this->db->select("c.mid,c.scatter_temp,c.scatter_humidity")
                         ->join("museum m","m.id=c.mid")
                         ->where("c.date >=",$this->start_time)
                         ->where("c.date <=",$this->end_time)
                         ->where("c.env_type",$this->env_type)
                         ->get("data_complex c")
                         ->result_array();
        $datas = array();
        foreach ($data as $value){
            $datas["scatter_temp"][$value["mid"]] = $value["scatter_temp"];
            $datas["scatter_humidity"][$value["mid"]] = $value["scatter_humidity"];
        }var_dump($datas);die;
        if($flag){
            return $datas;
        }
        $this->response($datas);
    }


    public function general_all_get(){ //区域详情-达标与稳定概况
        $data_standard = $this->detail_standard_get(true);
        $data_scatter = $this->data_scatter_get(true);
        $general_standard = $this->general_one($data_standard);
        $general_scatter_temp = $this->general_one($data_scatter["scatter_temp"]);
        $general_scatter_humidity = $this->general_one($data_scatter["scatter_humidity"]);
        $this->response(array("standard"=>$general_standard,"scatter_temp"=>$general_scatter_temp,"scatter_humidity"=>$general_scatter_humidity));
    }

    protected function general_one($data){
        $calculate = calculate($data);
        $rs = array();
        $rs["less"] = $rs["equal"] = $rs["more"] = 0;
        $rs["attention"] = array();
        $rs["all"] = count($data);
        $rs["standard"] = $calculate["standard"];
        $rs["average"] = $calculate["average"];
        foreach ($data as $k => $value){
            $rs["museum"][] = array("name"=>$this->museum[$k],"data"=>$value,"distance"=>$value - $calculate["average"]);
            $z = ($value - $calculate["average"]) / $calculate["standard"];
            if($z < -2){
                $rs["attention"][] = $this->museum[$k];
            }
            if($value < $calculate["average"]){
                $rs["less"] ++;
            }elseif ($value == $calculate["average"]){
                $rs["equal"] ++;
            }else{
                $rs["more"] ++;
            }
        }

        return $rs;
    }

    public function param_details_get(){ //区域详情-环境指标统计详情
        $texture_data = $compliance = $standard= $rs = array();
        $params = array("temperature","humidity","light","uv","voc");
        $all =  $this->db->select("p.*")
                         ->join("data_envtype_param p","p.mid=m.id")
                         ->where("p.date >=",$this->start_time)
                         ->where("p.date <=",$this->end_time)
                         ->where("p.env_type",$this->env_type)
                         ->get("museum m")
                         ->result_array();

        $env = $this->db->select("c.*,e.mid")
            ->join("data_env_compliance c","c.eid=e.id")
            ->where("c.date >=",$this->start_time)
            ->where("c.date <=",$this->end_time)
            ->where("e.env_type",$this->env_type)
            ->get("data_env e")
            ->result_array();

        foreach ($env as $value){
            $compliance[$value["mid"]][] = $value;
        }

        foreach ($compliance as $mid => $value){
            $standard[$mid] = array_key_exists($mid, $standard)?$standard[$mid]:array();
            foreach ($value as $v) {
                foreach ($params as $p) {
                    if($v[$p."_total"]){
                        $standard[$mid][$p."_total"] = array_key_exists($p."_total", $standard[$mid])?$standard[$mid][$p."_total"]:0;
                        $standard[$mid][$p."_total"] += $v[$p."_total"];
                    }
                    if($v[$p."_abnormal"]){
                        $standard[$mid][$p."_abnormal"] = array_key_exists($p."_abnormal", $standard[$mid])?$standard[$mid][$p."_abnormal"]:0;
                        $standard[$mid][$p."_abnormal"] += $v[$p."_abnormal"];
                    }
                }
            }

        }

        foreach ($all as $item) {
            $texture_data[$item["param"]][] = array(
                "mid"=>$item["mid"],
                "museum"=>$this->museum[$item["mid"]],
                "max"=>$item["max"],
                "min"=>$item["min"],
                "max2"=>$item["max2"],
                "min2"=>$item["min2"],
                "distance"=>$item["max"] - $item["min"],
                "middle"=>$item["middle"],
                "average"=>$item["average"],
                "count_abnormal"=>$item["count_abnormal"],
                "standard"=>$item["standard"]
            );
        }

        //print_r($texture_data);exit;

        foreach ($this->texture as $k => $v){
            foreach ($v as $param => $tt){
                $data = array_key_exists($k,$texture_data)?$texture_data[$k]:array();
                if($data){
                    foreach ($data as $key => $value){
                        if(array_key_exists($param."_total",$standard[$value["mid"]]) && $total = $standard[$value["mid"]][$param."_total"]){
                            $abnormal = array_key_exists($param."_abnormal",$standard[$value["mid"]])?$standard[$value["mid"]][$param."_abnormal"]:0;
                            $data[$key]["standard_percent"] = round(($total - $abnormal) / $total,2);
                        }
                    }
                }
                if(!empty($tt)){
                    $rs[$param][] = array(
                                            "texture"=>implode("、",$tt),
                                            "data"=>$data
                                          );
                }else{
                    $rs[$param] = $data;
                }
            }
        }

        $this->response($rs);
    }

    


    protected function date_list($s,$e){
        $date=array();
        for($i = strtotime($s); $i <= strtotime($e); $i += 86400) {
            $date[] = date("Ymd", $i);
        }
        return $date;
    }

    //博物馆详情-历史达标率-折线图
    protected function data_history_standard($mid){
        $env = $this->env_type;
        $date_s = $this->start_time = 20161024;
        $date_e = $this->end_time = 20161025;
        $date = $this->date_list($date_s, $date_e);
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");
        $data = array();//数据返回数组

        $sumstr = ''; //参数求和str
        foreach ($param as $v) {
            $sumstr .= ",SUM(" . $v . "_total)";
            $sumstr .= ",SUM(" . $v . "_abnormal)";
        }
        $sumstr = substr($sumstr, 1);
        $mdatas = $this->db->select("id,name,env_type")->where("mid", $mid)->where("env_type", $env)
            ->get("data_env")->result_array();

        $envid_list = array_column($mdatas, "id");//环境id列表

        $envdatas = array();
        foreach ($date as $v) { //按照日期分别统计
            $res = $this->db->select("date," . $sumstr)
                ->where_in("eid", $envid_list)->where("date", $v)->group_by("date")
                ->get("data_env_compliance")->result_array();
            if ($res) $envdatas[$v] = $res[0];
        }
        if(empty($envdatas)) return false;
        //整体达标率，分日期
        foreach ($envdatas as $k => $v) {
            $tsum = 0; //所有参数达标和未达标总和
            $asum = 0; //所有参数未达标总和
            foreach ($v as $k1 => $v1) {
                if (strpos($k1, "total") !== false) $tsum += $v1;
                if (strpos($k1, "abnormal") !== false) $asum += $v1;
            }
            $data[$k] = round(($tsum - $asum) / $tsum, 4);
        }

        return $data;
    }
    //接口调用-博物馆详情-历史达标率-折线图
    public function history_standard($mid){ //传入博物馆id
        $datas = $this->data_history_standard($mid);
        $data = json_encode(array_values($datas),JSON_UNESCAPED_UNICODE);
        echo $data;
    }

    //博物馆详情-历史达标率-分环境
    protected function data_history_env_standard($mid){
        $env = $this->env_type = "展厅";
        $date_s = $this->start_time = 20161024;
        $date_e = $this->end_time = 20161025;
        $date = $this->date_list($date_s, $date_e);
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");
        $data = array();//数据返回数组

        $sumstr = ''; //参数求和str
        foreach ($param as $v) {
            $sumstr .= ",SUM({$v}_total),SUM({$v }_abnormal)";
        }
        $sumstr = substr($sumstr, 1);
        $mdatas = $this->db->select("id,name,env_type")->where("mid", $mid)->where("env_type", $env)
            ->get("data_env")->result_array();
        foreach ($mdatas as $v) {
            $mdatas1[$v['id']] = $v['name']; //传递环境名称
            $envid_list[] = $v['id'];//环境id列表
        }
        $dec_datas = array();
        foreach($date as $v) { //按环境id分别统计
            $res = $this->db->select("date,eid,".$sumstr)
                    ->where_in("eid", $envid_list)->where("date", $v)->group_by("eid")
                    ->get("data_env_compliance")->result_array();
            if($res) $dec_datas[$v] = $res;
        }
        if(empty($dec_datas)) return false;
        //var_dump($dec_datas);
        //各环境达标率 分日期
        foreach($dec_datas as $k=>$v){
                foreach($v as $k1=>$v1) {
                    $tsum = 0;
                    $asum = 0;
                    foreach ($v1 as $k2 => $v2) {
                        if (strpos($k2, "total") !== false) $tsum += $v2;
                        if (strpos($k2, "abnormal") !== false) $asum += $v2;
                    }
                    $data1[$v1['eid']][$k] = ($tsum-$asum)/$tsum;
                }
            }
        //var_dump($data1);
        //各环境分别统计最大/最小/平均
        foreach($data1 as $k=>$v){
                $data[] = array(
                    "eid"=>$k,
                    "max"=>round(max($v),4),
                    "min"=>round(min($v),4),
                    "average"=>round(array_sum($v)/count($v),4),
                    "ename"=>$mdatas1[$k]
                );
        }
        //var_dump($data);
        return $data;
    }
    //接口调用-博物馆详情-历史达标率-分环境
    public function history_env_standard($mid){
        $datas = $this->data_history_env_standard($mid);
        $data = json_encode($datas,JSON_UNESCAPED_UNICODE);
        echo $data;
    }

    //博物馆详情-历史稳定性(温度/湿度)-折线图
    protected function data_history_stability($mid,$type){
        $env = $this->env_type = "展厅";
        $date_s = $this->start_time = 20161024;
        $date_e = $this->end_time = 20161025;
        $date = $this->date_list($date_s, $date_e);
        $data = array();

        $field = $type;
        if ($type == "temperature") $field = "temp";
        foreach ($date as $v) {
            $row = $this->db->select("date,scatter_" . $field)->where("env_type", $env)->where("mid", $mid)
                ->where("date", $v)->get("data_complex", 1)->result_array();
            if($row) $data[$v] = $row[0]['scatter_' . $field];
        }
        return $data;
    }
    //接口调用-博物馆详情-历史稳定性(温度/湿度)-曲线图
    public function history_stability($mid,$type){
        $datas = $this->data_history_stability($mid,$type);
        $data = json_encode(array_values($datas),JSON_UNESCAPED_UNICODE);
        echo $data;
    }

    //博物馆详情-温湿度稳定性-各环境
    protected function data_history_env_stability($mid,$type){
        $env = $this->env_type = "展厅";
        $date_s = $this->start_time = 20161024;
        $date_e = $this->end_time = 20161025;

        $dates = $this->date_list($date_s, $date_e);
        $data = array();

        $datas = $this->db->select("id,name")->where("mid",$mid)->where("env_type",$env)
            ->get('data_env')->result_array();
        foreach($datas as $v){
            $envs[$v['id']] = $v['name'];
            $env_list[] = $v['id'];
        }

        foreach($dates as $date){
            $edatas[$date] = $this->db->select("distinct(eid),{$type}_scatter")->where("date",$date)->where_in("eid",$env_list)
                ->get("data_env_complex")->result_array();
        }
        foreach($edatas as $date=>$v){
            foreach($v as $k1=>$v1){
                $newdatas[$v1['eid']][$date] = $v1["{$type}_scatter"];
            }
        }

        foreach($newdatas as $k=>$v){
            $data[] = array(
                "eid"=>$k,
                "max"=>max($v),
                "min"=>min($v),
                "average"=>round((array_sum($v)/count($v)),4),
                "ename"=>$envs[$k]
            );
        }
        return $data;
    }
    //接口调用-博物馆详情-温湿度稳定性-各环境
    public function history_env_stability($mid,$type){
        $datas = $this->data_history_env_stability($mid,$type);
        $data = json_encode($datas,JSON_UNESCAPED_UNICODE);
        echo $data;
    }

    //区域详情-态势图-基础数据
    protected function data_area_pie($date){
        $env = $this->env_type;
        $param = $this->env_param;
        $data = array();

        $sumstr = '';
        foreach ($param as $v) {
            $sumstr .= ",SUM({$v}_total),SUM({$v }_abnormal)";
        }
        $sumstr = substr($sumstr, 1);

        $data['museum_total'] = $this->db->count_all_results("museum");//总数
        $dc_datas = $this->db->select("distinct(mid)")->where("date",$date)->order_by("mid asc")
            ->get("data_complex")->result_array();
        $data['museum_show'] = count($dc_datas);//显示数
        $mid_list = array_column($dc_datas,"mid");

        foreach($mid_list as $mid){
            $edatas  = $this->db->select("id")->where('mid',$mid)->where("env_type",$env)->get("data_env")->result_array();
            if($edatas) $env_ids[$mid] = array_column($edatas,"id");
        }

        foreach($env_ids as $k => $v){
            $dec_datas = $this->db->select($sumstr)->where("date",$date)->where_in("eid",$v)
                ->get("data_env_compliance")->result_array();
            $datas[$k] = $dec_datas[0];
        }

        foreach($datas as $k => $v){
            $tsum = 0;
            $asum = 0;
            foreach ($v as $k1 => $v1) {
                    if (strpos($k1, "total") !== false) $tsum += $v1;
                    if (strpos($k1, "abnormal") !== false) $asum += $v1;
                }
            if(!$tsum) {$data["standard"][$k] = NUll; continue;}
            $data["standard_percent"][$k] = round(($tsum-$asum)/$tsum,4);
        }

        $dc_datas = $this->db->where("date",$date)->where("env_type",$env)->order_by("mid asc")
            ->get("data_complex")->result_array();
        foreach($dc_datas as $v){
            $data["temperature_scatter"][$v['mid']] = $v["scatter_temp"];
            $data["humidity_scatter"][$v['mid']] = $v["scatter_humidity"];
            $data['is_wave_abnormal'][$v['mid']] = $v['is_wave_abnormal']==1?"是":"否";
            $data['is_value_abnormal'][$v['mid']] = $v['is_value_abnormal']==1?"是":"否";
        }

        return $data;
    }
    //接口调用-区域详情-态势图-饼图
    public function area_pie(){
        $date = $this->start_time;
        $datas = $this->data_area_pie($date);

        $sp = array(
            1=>array("name"=>"99.5%(含)~100%","min"=>0.995,"max"=>1.1),
            2=>array("name"=>"99%(含)~99.5%","min"=>0.99,"max"=>0.995),
            3=>array("name"=>"95%(含)~99%","min"=>0.95,"max"=>0.99),
            4=>array("name"=>"<95%","min"=>0,"max"=>0.95)
        );

        //达标率
        foreach($sp as $k=>$v){
            foreach($datas['standard_percent'] as $v1){
                if($v1<$v['max'] && $v1>=$v['min']) $data[$k][] = $v1;
            }
            if(isset($data[$k])) $sp_data[] = array("value"=>count($data[$k]), "name"=>$v['name']);
            else $sp_data[] = array("value"=>0, "name"=>$v['name']);
        }
        //var_dump($sp_data);

        $ts = array(
            1=>array("name"=>"0~4%(含)","min"=>0,"max"=>0.04),
            2=>array("name"=>"4%~6%(含)","min"=>0.04,"max"=>0.06),
            3=>array("name"=>"6%~7%(含)","min"=>0.06,"max"=>0.07),
            4=>array("name"=>">7%","min"=>0.07,"max"=>999)
        );

        //温度稳定性
        foreach($ts as $k=>$v){
            foreach($datas['temperature_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data1[$k][] = $v1;
            }
            if(isset($data1[$k])) $ts_data[] = array("value"=>count($data1[$k]), "name"=>$v['name']);
            else $ts_data[] = array("value"=>0, "name"=>$v['name']);
        }
        //var_dump($ts_data);

        $hs = array(
            1=>array("name"=>"0~2%(含)","min"=>0,"max"=>0.02),
            2=>array("name"=>"2%~3%(含)","min"=>0.02,"max"=>0.03),
            3=>array("name"=>"3%~3.5%(含)","min"=>0.03,"max"=>0.035),
            4=>array("name"=>">3.5%","min"=>0.035,"max"=>999)
        );

        //湿度稳定性
        foreach($hs as $k=>$v){
            foreach($datas['humidity_scatter'] as $v1){
                if($v1<=$v['max'] && $v1>$v['min']) $data2[$k][] = $v1;
            }
            if(isset($data2[$k])) $hs_data[] = array("value"=>count($data2[$k]), "name"=>$v['name']);
            else $hs_data[] = array("value"=>0, "name"=>$v['name']);
        }
        //var_dump($hs_data);
        $ret['count'] = array("total"=>$datas['museum_total'],"show"=>$datas['museum_show']);
        $ret['standard_percent'] = $sp_data;
        $ret['temperature_scatter'] = $ts_data;
        $ret['humidity_scatter'] = $hs_data;

        echo json_encode($ret,JSON_UNESCAPED_UNICODE);
    }

    //接口调用-区域详情-态势图-地图-日数据
    public function area_map($mid=false){
        $date = $this->start_time = 20161024;
        $datas = $this->data_area_pie($date);
        $mdata = $this->db->select("id,name")->order_by("id asc")->get("museum")->result_array();
        foreach($mdata as $v){ //所有博物馆
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


    //接口调用-对比分析-历史达标与稳定情况-折线
    public function comparison_history_standard(){
        //接收到的mid组成数组
        $mids = array(3,4);
        $data = array();
        $mdatas = $this->db->select("id,name")->order_by("id asc")->get("museum")->result_array();
        foreach ($mdatas as $v) {
            $mdata_list[$v['id']] = $v['name'];
        }

        foreach($mids as $mid) {
            $sp_datas = $this->data_history_standard($mid);
            $tc_datas = $this->data_history_stability($mid, "temperature");
            $hc_datas = $this->data_history_stability($mid, "humidity");

            $data[] = array(
                "mid" => $mid,
                "museum" => $mdata_list[$mid],
                "standard_percent" => $sp_datas,
                "temperature_scatter" => $tc_datas,
                "humidity_scatter" => $hc_datas,
            );
        }

        var_dump($data);
        return $data;
    }

    //对比分析-环境指标与统计详情  昨天/前天
    protected function data_env_statistics(){
        $mids = array(2,3,4);//接收mid
        $env = $this->env_type = "展厅";
        $date_s = $this->start_time = 20161024;
        $param = $this->env_param = array("temperature", "humidity", "light", "uv", "voc");
        $data = array();

        $mdatas = $this->db->select("id,name")->order_by("id asc")->get("museum")->result_array();
        foreach ($mdatas as $v) {
            $mdata_list[$v['id']] = $v['name'];
        }
        $params = array();
        foreach($param as $v){
            if($v=="humidity") array_push($params,1,2,3);
            elseif($v=="light") array_push($params,4,5,6);
            elseif($v=="temperature") array_push($params,7);
            elseif($v=="uv") array_push($params,8);
            elseif($v=="voc") array_push($params,9);
        }

        $dep_datas = $this->db->where("date",$date_s)->where_in("mid",$mids)->where("env_type",$env)->where_in("param",$params)
            ->get("data_envtype_param")->result_array();

        //获取分材质各博物馆环境id
        foreach($params as $v){
            foreach($mids as $mid){
                if($v<4){
                    $result = $this->db->select("id")->where("mid",$mid)->where("env_type",$env)->where("material_humidity",$v)->get("data_env")->result_array();
                }elseif($v>=4 && $v<=6){
                    $result = $this->db->select("id")->where("mid",$mid)->where("env_type",$env)->where("material_light",$v)->get("data_env")->result_array();
                }else{
                    $result = $this->db->select("id")->where("mid",$mid)->where("env_type",$env)->get("data_env")->result_array();
                }
                if($result){
                    $env_ids[$v][$mid] = array_column($result,"id");
                }
            }
        }
        //var_dump($env_ids);

        //分材质各博物馆达标率
        foreach($env_ids as $envid=>$v){
            foreach($v as $mid=>$envids){
                if($envid<4){
                    $res = $this->db->select("SUM(humidity_total) as total,SUM(humidity_abnormal) as abnormal")->where_in("eid",$envids)
                        ->where("date",$date_s)->get("data_env_compliance")->result_array();
                }elseif($envid>=4 && $envid<=6){
                    $res = $this->db->select("SUM(light_total) as total,SUM(light_abnormal) as abnormal")->where_in("eid",$envids)
                        ->where("date",$date_s)->get("data_env_compliance")->result_array();
                }elseif($envid==7){
                    $res = $this->db->select("SUM(temperature_total) as total,SUM(temperature_abnormal) as abnormal")->where_in("eid",$envids)
                        ->where("date",$date_s)->get("data_env_compliance")->result_array();
                }elseif($envid==8){
                    $res = $this->db->select("SUM(uv_total) as total,SUM(uv_abnormal) as abnormal")->where_in("eid",$envids)
                        ->where("date",$date_s)->get("data_env_compliance")->result_array();
                }elseif($envid==9){
                    $res = $this->db->select("SUM(voc_total) as total,SUM(voc_abnormal) as abnormal")->where_in("eid",$envids)
                        ->where("date",$date_s)->get("data_env_compliance")->result_array();
                }
                //var_dump($res);
                if($res[0]['total']) $sp_datas[$envid][$mid] = round((($res[0]['total'] - $res[0]['abnormal']) / $res[0]['total']),4);
            }
        }
        //var_dump($sp_datas);
       // var_dump($dep_datas);

        foreach($dep_datas as $v){
            if(isset($sp_datas[$v['param']][$v['mid']])) $sp = $sp_datas[$v['param']][$v['mid']];
            else $sp = NULL;
            $datas[$v['param']][] = array(
                "mid"=>$v['mid'],
                "museum"=>$mdata_list[$v['mid']],
                "max"=>$v['max'],
                "min"=>$v['min'],
                "max2"=>$v['max2'],
                "min2"=>$v['min2'],
                "distance"=>$v['max2'] - $v['min2'],
                "middle"=>$v['middle'],
                "average"=>$v['average'],
                "standard"=>$v['standard'],
                "count_abnormal"=>$v['count_abnormal'],
                "standard_percent"=>$sp,
            );
        }

        //var_dump($datas);
        foreach($datas as $k=>$v){
            if($k==7){
                $data['temperature'] = $v;
            }elseif($k==8){
                $data['uv'] = $v;
            }elseif($k==9){
                $data['voc'] = $v;
            }elseif($k==1){
                $data['humidity']['texture'] = "石质,陶器,瓷器";
                $data['humidity']['data'] = $v;
            }elseif($k==2){
                $data['humidity']['texture'] = "铁质,青铜";
                $data['humidity']['data'] = $v;
            }elseif($k==3){
                $data['humidity']['texture'] = "纸质,壁画,纺织品,漆木器,其他";
                $data['humidity']['data'] = $v;
            }elseif($k==4){
                $data['light']['texture'] = "石质,陶器,瓷器,铁质,青铜";
                $data['light']['data'] = $v;
            }elseif($k==5){
                $data['light']['texture'] = "纸质,壁画,纺织品";
                $data['light']['data'] = $v;
            }elseif($k==6){
                $data['light']['texture'] = "漆木器,其他";
                $data['light']['data'] = $v;
            }

        }

        //echo json_encode($data,JSON_UNESCAPED_UNICODE);
        var_dump($data);
    }
    

}