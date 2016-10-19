<?php
(defined('BASEPATH')) OR exit('No direct script access allowed');

if (!function_exists('M')) {

    /**
     * 加载model
     * <code>
     *  $m=M('test');//使用当前moduleName/models/test_model.php，没有则找application/models/test_model.php，还没有则使用MY_Model;
     *  $m->show();
     *  $memberTestModel=M('member/test');//modules/member/models/test_model.php
     *  $memberTestModel->show();
     *  支持无限极文件夹 M('admin/a/user');//modules/admin/models/a/User_model.php
     *  $this->load->model('test_model');//类似于这样，但如果没有找到则返回new MY_Model($table);
     *  $this->test_model->show();
     * </code>
     * application/models下的文件最好不要与moduleName/moduleName/models下的文件重名
     * @param string $table
     * @return MY_Model
     * @property MY_Model
     * @extends MY_Model
     */
    function M($table = '')
    {
        $CI = &get_instance();

        // class_exists('MY_Model', FALSE) OR load_class('Model', 'core');
        $model_name = str_replace('/', '_', $table) . '_model';

        try {
            $CI->load->model($table . '_model', $model_name);
        } catch (Exception $e) {
            return new MY_Model($table);
        }

        return $CI->$model_name;

    }

}