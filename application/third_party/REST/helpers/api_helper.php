<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * 调用api
 * @param string|array $uris "get/base/example/users_get/id/1" array(array(uri,data),array(uri,data));
 * @param array $uris_data
 * @return array
 */
function API($uris = 'get/base/config', $uris_data = null)
{
    $_ci =& get_instance();
    $_ci->load->library('rest_api');
    $_ci->rest_api->init($uris, $uris_data);

    return $_ci->rest_api->exec();
}

function API_encode($app, $data)
{
    $_ci =& get_instance();
    $_ci->load->library('rest_des');

    $api_hosts = config_item('api_hosts');
    $key = $api_hosts[$app]['api_key'];

    $str = base64_encode($_ci->rest_des->encrypt(json_encode($data), $key));

    return $str;
}

function API_decode($app, $str)
{
    $_ci =& get_instance();
    $_ci->load->library('rest_des');

    $api_hosts = config_item('api_hosts');
    $key = $api_hosts[$app]['api_key'];

    $data = json_decode($_ci->rest_des->decrypt(base64_decode($str), $key), true);

    return $data;
}
