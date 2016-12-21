<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/10
 * Time: 11:01
 */
class Generality extends REST_Controller{

    private $museum = array();
    private $museum_source = array();
    function __construct()
    {
        parent::__construct();
        $museum = $this->db->select("id,name,site_url")->get("museum")->result_array();
        $this->museum_source = $museum;
        foreach ($museum as $value){
            $this->museum[$value["id"]] = array("name"=>$value["name"],"site_url"=>$value["site_url"]);
        }
    }

    public function all_museum_get(){
        $this->response($this->museum_source);
    }

    public function index_get(){ //各馆概况
        $b = M("data_base");
        $c = M("data_complex");
        $params = array("temperature","humidity","light","voc","uv");
        $result = $relic = $precious_relic = $fixed_exhibition = $temporary_exhibition = $micro_compliance = $small_compliance = array();
        $abnormal_all_small = $total_all_small = $abnormal_all_micro = $total_all_micro = 0;
        $compliance = array();
        $base = $b->fetAll();
        $complex = $c->fetAll(array("date"=>"D".date("Ymd",strtotime("-1 day"))));
        //$complex = $c->fetAll(array("date"=>"D20161206"));
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
            if ($value["mid"]) {
                $datas = $data = array();
                $legend = array($this->museum[$value["mid"]]["name"],"区域平均");
                $data["name"] = $this->museum[$value["mid"]]["name"];
                $data["value"][] = $relic[] = $value["count_relic"];
                $data["value"][] = $precious_relic[] = $value["count_precious_relic"];
                $data["value"][] = $fixed_exhibition[] =  $value["count_fixed_exhibition"];
                $data["value"][] = $temporary_exhibition[] = $value["count_temporary_exhibition"];

                if (array_key_exists($value["mid"], $compliance)) {
                    if(array_key_exists("micro",$compliance[$value["mid"]])){
                        $abnormal_micro = array_key_exists("abnormal",$compliance[$value["mid"]]["micro"])?array_sum($compliance[$value["mid"]]["micro"]["abnormal"]):0;
                        $total_micro = array_key_exists("total",$compliance[$value["mid"]]["micro"])?array_sum($compliance[$value["mid"]]["micro"]["total"]):0;
                        $data["value"][] = $micro_compliance[] = $total_micro ? round(($total_micro - $abnormal_micro) / $total_micro, 4)*100 : 0;
                    }else{
                        $data["value"][] = 0;
                    }


                    if(array_key_exists("small",$compliance[$value["mid"]])) {
                        $abnormal = array_key_exists("abnormal",$compliance[$value["mid"]]["small"])?array_sum($compliance[$value["mid"]]["small"]["abnormal"]):0;
                        $total = array_key_exists("total",$compliance[$value["mid"]]["small"])?array_sum($compliance[$value["mid"]]["small"]["total"]):0;
                        $data["value"][] = $small_compliance[] = $total ? round(($total - $abnormal) / $total, 4)*100 : 0;
                    }else{
                        $data["value"][] = 0;
                    }
                }else{
                    $data["value"][] = 0;
                    $data["value"][] = 0;
                }
                $datas[] = $data;
                $result[] = array(
                    "mid"=>$value["mid"],
                    "legend"=>$legend,
                    "data"=>$datas
                );
            }

        }

        $indicator_compliance = array(
            array("name"=>"馆藏文物数量","max"=>max($relic)),
            array("name"=>"珍贵文物数量","max"=>max($precious_relic)),
            array("name"=>"固定展览数量","max"=>max($fixed_exhibition)),
            array("name"=>"临时展览数量","max"=>max($temporary_exhibition)),
            array("name"=>"昨日微环境达标率","max"=>100),
            array("name"=>"昨日小环境达标率","max"=>100)
        );

        $average = array(
            sizeof($relic)?round(array_sum($relic)/sizeof($relic),1):0,
            sizeof($precious_relic)?round(array_sum($precious_relic)/sizeof($precious_relic),1):0,
            sizeof($fixed_exhibition)?round(array_sum($fixed_exhibition)/sizeof($fixed_exhibition),1):0,
            sizeof($temporary_exhibition)?round(array_sum($temporary_exhibition)/sizeof($temporary_exhibition),1):0,
            $total_all_micro?round(($total_all_micro - $abnormal_all_micro)/$total_all_micro,4)*100:0,
            $total_all_small?round(($total_all_small - $abnormal_all_small)/$total_all_small,4)*100:0
        );
        foreach ($result as $key => $value){
            $result[$key]["indicator"] = $indicator_compliance;
            $result[$key]["data"][] = array("name"=>"区域平均","value"=>$average);
            // 跳转地址
            $code = API_encode('base', array('username'=>$this->_user['username'], 'key'=>date('Y-m-d')));

            $result[$key]['url'] = $this->museum[$value["mid"]]["site_url"]."?code={$code}";
        }

        $this->response($result);
    }
}