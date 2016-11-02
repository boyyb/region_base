<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/1
 * Time: 16:46
 */

if(!function_exists("calculate")){
    function calculate($arr){
        $average = round(array_sum($arr)/sizeof($arr),2);
        $sum = 0;
        foreach ($arr as $k =>$v){
            $sum += pow($v - $average,2);
        }
        $standard = sqrt($sum/sizeof($arr));//标准差
        $scatter = round($standard/$average,2);//离散系数

        $data = array("average"=>$average,"standard"=>round($standard,2),"scatter"=>$scatter);
        return $data;
    }
}