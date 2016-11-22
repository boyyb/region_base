<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 11:01
 */
class Generality extends REST_Controller{

    private $museum = array();
    function __construct()
    {
        parent::__construct();
        $museum = $this->db->select("id,name")->get("museum")->result_array();
        foreach ($museum as $value){
            $this->museum[$value["id"]] = $value["name"];
        }
    }

    public function index_get(){ //各馆概况
        $b = M("data_base");
        $c = M("data_complex");
        $params = array("temperature","humidity","light","voc","uv");
        $result = $relic = $precious_relic = $fixed_exhibition = $temporary_exhibition = array();
        $abnormal_all_small = $total_all_small = $abnormal_all_micro = $total_all_micro = 0;
        $compliance = array();
        $base = $b->fetAll();
        //$complex = $c->fetAll(array("date"=>"D".date("Ymd",strtotime("-1 day"))));
        $complex = $c->fetAll(array("date"=>"D20161024"));
        foreach ($complex as $value){
            if($value["mid"]){
                if($value["env_type"] == "展厅"){//小环境
                    foreach ($params as $v){
                        if($value[$v."_abnormal"]){
                            $compliance[$value["mid"]]["small"]["abnormal"][] = $value[$v."_abnormal"];
                            $abnormal_all_small += $value[$v."_abnormal"];
                        }
                        if($value[$v."_total"]) {
                            $compliance[$value["mid"]]["small"]["total"][] = $value[$v."_total"];
                            $total_all_small += $value[$v."_total"];
                        }
                    }
                }else{//微环境
                    foreach ($params as $v){
                        if($value[$v."_abnormal"]){
                            $compliance[$value["mid"]]["micro"]["abnormal"][] = $value[$v."_abnormal"];
                            $abnormal_all_micro += $value[$v."_abnormal"];
                        }
                        if($value[$v."_total"]) {
                            $compliance[$value["mid"]]["micro"]["total"][] = $value[$v."_total"];
                            $total_all_micro += $value[$v."_total"];
                        }
                    }
                }
            }
        }
        foreach ($base as $value) {
            $data = array();
            if ($value["mid"]) {
                $data["mid"] = $value["mid"];
                $data["museum"] = $this->museum[$value["mid"]];

                $data["count_relic"] = $relic[] = $value["count_relic"];
                $data["count_precious_relic"] = $precious_relic[] = $value["count_precious_relic"];
                $data["count_fixed_exhibition"] = $fixed_exhibition[] =  $value["count_fixed_exhibition"];
                $data["count_temporary_exhibition"] = $temporary_exhibition[] = $value["count_temporary_exhibition"];

                if (array_key_exists($value["mid"], $compliance)) {
                    if(array_key_exists("micro",$compliance[$value["mid"]])){
                        $abnormal_micro = array_key_exists("abnormal",$compliance[$value["mid"]]["micro"])?array_sum($compliance[$value["mid"]]["micro"]["abnormal"]):0;
                        $total_micro = array_key_exists("total",$compliance[$value["mid"]]["micro"])?array_sum($compliance[$value["mid"]]["micro"]["total"]):0;
                        $data["micro_compliance"] = $total_micro ? round(($total_micro - $abnormal_micro) / $total_micro, 2) : 0;
                    }
                    if(array_key_exists("small",$compliance[$value["mid"]])) {
                        $abnormal = array_key_exists("abnormal",$compliance[$value["mid"]]["small"])?array_sum($compliance[$value["mid"]]["small"]["abnormal"]):0;
                        $total = array_key_exists("total",$compliance[$value["mid"]]["small"])?array_sum($compliance[$value["mid"]]["small"]["total"]):0;
                        $data["small_compliance"] = $total ? round(($total - $abnormal) / $total, 2) : 0;
                    }
                }
            }
            if(!empty($data)){
                $result["museum"][] = $data;
            }
        }

        $result["average"] = array(
            "count_relic"=>sizeof($relic)?round(array_sum($relic)/sizeof($relic),1):0,
            "count_precious_relic"=>sizeof($precious_relic)?round(array_sum($precious_relic)/sizeof($precious_relic),1):0,
            "count_fixed_exhibition"=>sizeof($fixed_exhibition)?round(array_sum($fixed_exhibition)/sizeof($fixed_exhibition),1):0,
            "count_temporary_exhibition"=>sizeof($temporary_exhibition)?round(array_sum($temporary_exhibition)/sizeof($temporary_exhibition),1):0,
            "micro_compliance" => $total_all_micro?round(($total_all_micro - $abnormal_all_micro)/$total_all_micro,2):0,
            "small_compliance" => $total_all_small?round(($total_all_small - $abnormal_all_small)/$total_all_small,2):0
        );
        $this->response($result);
    }
}