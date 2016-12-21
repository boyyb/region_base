<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2016/11/1
 * Time: 16:46
 */

if(!function_exists("calculate")){
    function calculate($arr){
        foreach ($arr as $key => $value){
            if($value === 0){
                unset($arr[$key]);
            }
        }
        $average = sizeof($arr)?round(array_sum($arr)/sizeof($arr),4):0;
        $sum = 0;
        foreach ($arr as $k =>$v){
            $sum += pow($v - $average,2);
        }
        $standard = sizeof($arr)?sqrt($sum/sizeof($arr)):0;//标准差
        $scatter = $average?round($standard/$average,4):0;//离散系数

        $data = array("average"=>$average,"standard"=>round($standard,4),"scatter"=>$scatter);
        return $data;
    }
}