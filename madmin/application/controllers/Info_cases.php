<?php

/**
 * Created by PhpStorm.
 * User: MengXianghan
 * Date: 2016/8/23
 * Time: 21:11
 */
require_once 'Information.php';

class Info_cases extends Information
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('info_cases_model', 'info_cases');
        $this->load->library('uploadifive');
        $this->load->library('category', array('tb_name' => 'info_col'), 'category');
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('info_cases/get_list?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
        $url['insert_btn'] = $this->sys_auth->set_auth(MYINSERT, $this->col_auth, '<a class="btn btn-primary" href="' . site_url('info_cases/insert?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid) . '">新增</a>');
        $url['del_btn'] = $this->sys_auth->set_auth(MYDEL, $this->col_auth, '<a class="btn btn-default" href="javascript:;" data-name="batchDel" data-tb="info_cases" data-checkname="id" data-url = "' . site_url('ajax/batch_del?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid) . '">删除</a>');
        $url['search_btn'] = $this->sys_auth->set_auth(MYLOOK, $this->col_auth, '<button type="button" data-name="searchbtn" class="btn btn-default">搜索</button>');
        $url['save_url'] = site_url('info_cases/save?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('info_cases/index.html');
    }

    //获得列表
    public function get_list()
    {
        $data['list'] = $this->info_cases->get_list();
        foreach ($data['list']['list'] as $key => $val) {
            $data['list']['list'][$key]['title'] = $val['title'];
            $data['list']['list'][$key]['time'] = date('m/d H:i', $val['update_time']);
            $data['list']['list'][$key]['update_btn'] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a href="' . site_url('info_cases/update?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid . '&id=' . $val['id']) . '">编辑</a>', '<a href="javascript:;" class="disabled">编辑</a>');
            $data['list']['list'][$key]['del_btn'] = $this->sys_auth->set_auth(MYDEL, $this->col_auth, '<a href="javascript:;" data-name="del" data-tb="info_cases" data-id="' . $val['id'] . '" data-url="' . site_url('ajax/del?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid . '&col_name=' . urlencode($this->main_section_name)) . '">删除</a>', '<a href="javascript:;" class="disabled">删除</a>');
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $data['info_col'] = $this->category->ddl('cid', 0, $this->cid, array(), FALSE, $this->sys_tpl_id());
        $data['case_target'] = $this->sys_dict->rbl('target', 'case_target');
        $data['target'] = $this->sys_dict->rbl('target', 'target');
        $data['display'] = $this->sys_dict->rbl('display', 'display');
        $this->load->view('info_cases/insert.html', $data);
    }

    //更新
    public function update()
    {
        $data['item'] = $this->info_cases->update();
        $data['info_col'] = $this->category->ddl('cid', 0, $data['item']['cid'], array(), FALSE, $this->sys_tpl_id());
        $data['case_target'] = $this->sys_dict->rbl('target', 'case_target', $data['item']['case_target']);
        $data['image'] = $this->uploadifive->get_list($data['item']['image'], 'image');
        $data['images'] = $this->uploadifive->get_list($data['item']['images'], 'images');
        $data['target'] = $this->sys_dict->rbl('target', 'target', $data['item']['target']);
        $data['display'] = $this->sys_dict->rbl('display', 'display', $data['item']['display']);
        $this->load->view('info_cases/update.html', $data);
    }

    //保存
    public function save()
    {
        $bool = $this->info_cases->save();
        $this->sys_log->insert($this->main_section_name, (!$this->input->post('id')) ? '1' : '2', $bool);//日志
        $config['icon'] = 1;
        $config['url'] = site_url('info_cases?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
        if ($bool) {
            switch ($this->is_save) {
                case '1':
                    echo json_encode($config);
                    break;
                case '2':
                    $config['url'] = $this->peferer;
                    echo json_encode($config);
                    break;
            }
        } else {
            $config['icon'] = 2;
            echo json_encode($config);
        }
    }

}