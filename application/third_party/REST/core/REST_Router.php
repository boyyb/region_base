<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* load the HMVC_Router class */

class REST_Router extends CI_Router
{
    /**
     * Current module name
     * @var string
     * @access public
     */
    public $module;
    private $modules_locations = array();

    /**
     * 分析并构造路由
     */
    protected function _parse_routes()
    {

        $this->config->load('rest_api');
        if (config_item('allow_cross_domain')) {
            header('Access-Control-Allow-Origin: *');//允许跨域
            header('Access-Control-Allow-Headers: access_token');//允许跨域header
        }
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { 
            exit;
        }

        $locations = config_item('modules_locations');
        if (!$locations) {
            $locations = array('modules/');
        } else if (!is_array($locations)) {
            $locations = array($locations);
        }
        $this->modules_locations = $locations;

        // Apply the current module's routing config
        if ($module = $this->uri->segment(0)) {
            foreach ($this->modules_locations as $location) {
                if (is_file($file = $location . $module . '/config/routes.php')) {
                    include($file);

                    $route = (!isset($route) or !is_array($route)) ? array() : $route;
                    $this->routes = array_merge($this->routes, $route);
                    unset($route);
                }
            }
        }
        //使用默认
        return parent::_parse_routes();
    }

    /**
     * 检测路径中是否包含需要的控制器文件
     * @access    private
     * @param    array
     * @return    array
     */
    protected function _validate_request($segments)
    {
        if (count($segments) == 0) {
            return $segments;
        }
        // Locate the controller with modules support
        if ($located = $this->locate($segments)) {
            return $located;
        }
        if ($located = $this->rest_locate($segments)) {
            return $located;
        }
//        var_dump($segments);
        if ($segments[0] == 'sync') {
            $this->directory = '../third_party/REST/controllers/';
        }
        return parent::_validate_request($segments);
    }

    /**
     * 处理 (get|post|put|delete)/(base|env|relic)/api
     */
    protected function rest_locate($segments)
    {
        if (count($segments) < 1) {//参数不足
            return false;
        }
        if (!in_array($segments[0], config_item('allowed_http_methods'))) {//第一个参数不在允许的methods内
            //return false;
            $input =& load_class('Input', 'core');
            $method = $input->method(false);
            if (!in_array($method, config_item('allowed_http_methods'))) {//method不在允许的methods内
                return false;
            }
            array_unshift($segments, $method);
        }
        if (!in_array($segments[1], array_keys(config_item('api_hosts')))) {//不在api列表内
            return false;
        }
        $this->directory = '../third_party/REST/controllers/';
        //print_r($this->directory);
        //print_r($segments);
        array_unshift($segments, 'proxy');//发送到代理controller
        //print_r(parent::_validate_request($segments));
        return parent::_validate_request($segments);
    }

    /**
     * 寻找modules下的controller路径
     * @param    array
     * @return    array
     */
    function locate($segments)
    {
        $module = $segments[0];
        foreach ($this->modules_locations as $location) {
            $relative = APPPATH . $location;
            //如果 包含有 modules/$module/controllers文件夹
            if (is_dir($source = $relative . $module . '/controllers/')) {
                //$this->default_controller=$module;
                $this->module = $module;
                $this->directory = '../' . $location . $module . '/controllers/';
                $seg = array_slice($segments, 1);
                $c = count($seg);
                $i = 0;
                while ($c-- > 0) {
                    $ac = current($seg);
                    $next = next($seg);

                    if ($ac && is_dir($source . $ac . '/')) {
                        $source .= $ac . '/';
                        $this->directory .= $ac . '/';
                        if ($next && is_dir($source . $next . '/')) {
                            $i++;
                            continue;
                        }
                    }
                    if ($next && is_file($source . ucfirst($next) . '.php')) {
                        return array_slice($seg, $i + 1);
                    }
                    if (is_file($source . ucfirst($ac) . '.php')) {
                        return array_slice($seg, $i);
                    }
                }
                //如果有 controllers/$module.php
                if (is_file($source . ucfirst($module) . '.php')) {
                    return $segments;
                }
            } else if (is_file($source = $relative . ucfirst($module) . '.php')) {
                $this->module = $module;
                $this->directory = '../' . $location;
                return $segments;
            }
        }
    }

}