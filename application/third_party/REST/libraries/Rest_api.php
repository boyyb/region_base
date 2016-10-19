<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rest_api
{
    protected $_ci;
    protected $handles;

    function __construct()
    {
        $this->_ci =& get_instance();
        //$this->_ci->benchmark->mark('api_start');
    }

    /**
     * @param string|array $uris "get/base/example/users_get/id/1" array(array(uri,data),array(uri,data));
     * @param array $uris_data
     * @return array
     */
    function init($uris, $uris_data = null)
    {
        $this->handles = array();
        if (is_array($uris)) {
            foreach ($uris as $k => $uri) {
                $this->_add($uri[0], count($uri) > 1 ? $uri[1] : null);
            }
        } else {
            $this->_add($uris, $uris_data);
        }

    }

    function _add($uri, $data = null)
    {
        if (substr_count($uri, '/') < 1) {
            return array('error' => 'uri参数不足' . $uri);
        }

        list($method, $app, $api) = explode('/', $uri, 3);
        $method = strtolower($method);
        if (!in_array($method, config_item('allowed_http_methods'))) {//第一个参数不在允许的methods内
            return array('error' => 'method不在允许的范围内');
        }
        $app = strtolower($app);
        $api_host = config_item('api_hosts');
        if (!in_array($app, array_keys($api_host))) {//不在api列表内
            return array('error' => 'app不在api列表内');
        }
        $api_url = $api_host[$app]['api_url'];
        $this->_ci->load->library('rest_token');
        $token_row = $this->_ci->rest_token->create_token();

        $options = array(
            CURLOPT_URL => $api_url . $api,
            CURLOPT_CUSTOMREQUEST => $method, // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_HTTPHEADER => array(
                config_item('rest_token_param_name') . ':' . $token_row['token'],
                //'X-HTTP-Method-Override: ' . $method,
                'X-FORWARDED-FOR:'.$token_row['ip'],
                'CLIENT-IP:'.$token_row['ip'],
            ),
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data ? http_build_query($data) : '',
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            //CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_DNS_CACHE_TIMEOUT => 60*60*72,
            CURLOPT_SSL_VERIFYPEER => false,// 信任任何证书，不是CA机构颁布的也没关系
            CURLOPT_SSL_VERIFYHOST => 0,// 检查证书中是否设置域名，如果不想验证也可设为0

        );
        if ($method == 'get' && $data) {
            $options[CURLOPT_URL] .= '?' . http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $this->handles[] = $ch;
    }

    function exec()
    {
        if (count($this->handles) == 0) {
            return array('error' => '没有curl handles', 'http_code' => 404);
        }
        // 创建批处理cURL句柄
        $mh = curl_multi_init();
        // add handles
        foreach ($this->handles as $k => $handle) {
            curl_multi_add_handle($mh, $handle);
        }

        $running_handles = null;
        //execute the handles
        do {
            $status_cme = curl_multi_exec($mh, $running_handles);
        } while ($status_cme == CURLM_CALL_MULTI_PERFORM);

        while ($running_handles && $status_cme == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $status_cme = curl_multi_exec($mh, $running_handles);
                    // echo "<br>''threads'' running = {$running_handles}";
                } while ($status_cme == CURLM_CALL_MULTI_PERFORM);
            }
        }
        $results = array();
        foreach ($this->handles as $k => $handle) {
            if (curl_error($handle) != '') {
                $results[$k]['error'] = '发起请求失败!';
            } else {
                $results[$k]['result'] = curl_multi_getcontent($handle);  // get results
                $result = json_decode($results[$k]['result'], true);
                if (!$result) {//json解析错误
                    $results[$k]['error'] = 'json解析错误';
                } else {
                    $results[$k] = $result;
                }
            }
            // close current handler
            curl_multi_remove_handle($mh, $handle);
        }
        curl_multi_close($mh);

        //$CI->benchmark->mark('api_end');
        //$result['exe_time'] = $CI->benchmark->elapsed_time('api_start', 'api_end');

        if (count($results) > 1) {
            return $results;
        } else {
            return $results[0];
        }

    }

    function exec_one()
    {
        if (count($this->handles) == 0) {
            return array('error' => '没有curl handles', 'http_code' => 404);
        }
        $adb_handle = $this->handles[0];
        $api = array();
        $api['result'] = $result = curl_exec($adb_handle);
        $api['curl_info'] = curl_getinfo($adb_handle);

        if (curl_error($adb_handle) != '') {//获取失败
            $api['error'] = curl_error($adb_handle);
            curl_close($adb_handle);
            return $api;
        }

        $result = json_decode($result, true);

        if (!$result) {//json解析错误
            $api['error'] = 'json解析错误';
            curl_close($adb_handle);
            return $api;
        }
        curl_close($adb_handle);
        $api['result'] = $result;
        return $api;
    }

}
