<?php

class Logout extends MY_Controller
{

    //退出，仅base
    function index_post()
    {

        $local_api = config_item('local_api');
        $api_hosts = config_item('api_hosts');
        $result = array('msg' => '注销成功');
        $api = $api_hosts[$local_api];
        unset($api_hosts[$local_api]);
        $api_hosts[$local_api] = $api;//将当前应用 放在最后退出，否则token将改变
        $api_list = array();
        foreach ($api_hosts as $app => $api) {
            $api_list[] = array('post/' . $app . '/sync/logout',
                array('auth_code' => API_encode($app, array('token' => $this->_token)))
            );
        }
        $result['results'] = API($api_list);//批量发出
        $result['is_exit'] = true;

        $this->response($result);
    }

}