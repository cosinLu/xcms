<?php
defined('BASEPATH') OR exit('Error');

/**
 * Author: 孟祥涵
 * Date: 2016/5/9
 * Time: 16:31
 * Email: 1056811341@qq.com
 */
class Ajax extends M_Controller
{
    protected $col_name;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ajax_model', 'ajax');
        $this->col_name = (urldecode($this->input->get('col_name'))) ?: $this->section_name;
    }

    //删除
    public function del()
    {
        $tbname = $this->input->post('tbname');
        $id = $this->input->post('id');
        $primary = $this->input->post('primary') ?: 'id';
        $rows = $this->ajax->del($tbname, $id, $primary);
        //写入日志
        $this->sys_log_lib->insert($this->col_name, '3', $rows);
        echo $rows;
    }

    //批量删除
    public function batch_del()
    {
        $tbname = $this->input->post('tbname');
        $id = explode(',', $this->input->post('id'));
        $primary = $this->input->post('primary') ?: 'id';
        $rows = $this->ajax->batch_del($tbname, $id, $primary);
        //写入日志
        $this->sys_log_lib->insert($this->col_name, '3', $rows);
        echo $rows;
    }
}