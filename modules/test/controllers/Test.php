<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {

    function index_get_nologin($param='default') {	// 优先级 1
        $this->response("index_get_nologin - {$param}");
    }

    function index_get($param='default') {	// 优先级 2
        $this->response("index_get - {$param}");
    }

    function index_nologin($param='default') {	// 优先级 3
        $this->response("index_nologin - {$param}");
    }

    function index($param='default') {	// 优先级 4
        $this->response("index - {$param}");
    }

}