<?php

/**
 * Created by PhpStorm.
 * User: MengXianghan
 * Date: 2016/8/23
 * Time: 21:11
 */
require_once 'Info.php';

class Info_news extends Info
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('info_news_model', 'info_news');
        $this->load->library('uploadifive');
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('info_news/get_list?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
        $url['insert_btn'] = $this->sys_auth->set_auth(MYINSERT, $this->col_auth, '<a class="btn btn-primary btn-sm" href="' . site_url('info_news/insert?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid) . '">新增</a>');
        $url['del_btn'] = $this->sys_auth->set_auth(MYDEL, $this->col_auth, '<a class="btn btn-danger btn-sm batch-del-mhook" href="javascript:;" data-tb="info_news" data-checkname="id" data-url = "' . site_url('ajax/batch_del?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid) . '">删除</a>');
        $url['search_btn'] = $this->sys_auth->set_auth(MYLOOK, $this->col_auth, '<button type="button" class="btn btn-info btn-sm search-btn-mhook">搜索</button>');
        $url['save_url'] = site_url('info_news/save?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('info_news/index.html');
    }

    //获得列表
    public function get_list()
    {
        $key = $this->input->post('key');
        $page = ($this->input->post('page')) ?: 1;
        $data['list'] = $this->info_news->get_list($this->cid, $key, $page);
        foreach ($data['list']['list'] as $key => $val) {
            $data['list']['list'][$key]['title'] = $val['title'];
            $data['list']['list'][$key]['display_name'] = '<span style="color:' . $val['display_color'] . ';">' . $val['display_name'] . '</span>';
            $data['list']['list'][$key]['create_time'] = date('m/d H:i', $val['create_time']);
            $data['list']['list'][$key]['opera_btn'][] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a href="' . site_url('info_news/update?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid . '&id=' . $val['id']) . '">编辑</a>', '<a href="javascript:;" class="disabled">编辑</a>');
            $data['list']['list'][$key]['opera_btn'][] = $this->sys_auth->set_auth(MYDEL, $this->col_auth, '<a href="javascript:;" class="del-mhook" data-tb="info_news" data-id="' . $val['id'] . '" data-url="' . site_url('ajax/del?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid . '&col_name=' . urlencode($this->main_section_name)) . '">删除</a>', '<a href="javascript:;" class="disabled">删除</a>');
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $data['cols'] = $this->category->ddl(array(), 'cid', 0, $this->cid, FALSE, $this->tpl_id());
        $data['target'] = $this->sys_dict->rbl('target', 'target');
        $data['display'] = $this->sys_dict->rbl('display', 'display');
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $this->load->view('info_news/insert.html', $data);
    }

    //更新
    public function update()
    {
        $id = $this->input->get('id');
        $data['item'] = $this->info_news->update($id);
        $data['cols'] = $this->category->ddl(array(), 'cid', 0, $data['item']['cid'], FALSE, $this->tpl_id());
        $data['target'] = $this->sys_dict->rbl('target', 'target', $data['item']['target']);
        $data['display'] = $this->sys_dict->rbl('display', 'display', $data['item']['display']);
        $data['create_time'] = date('Y-m-d H:i:s', $data['item']['create_time']);
        $this->load->view('info_news/update.html', $data);
    }

    //保存
    public function save()
    {
        $data = array(
            'id' => $this->input->post('id'),
            'vals' => array(
                'cid' => $this->input->post('cid'),
                'title' => $this->input->post('title'),
                'summary' => $this->input->post('summary'),
                'target' => $this->input->post('target'),
                'display' => $this->input->post('display'),
                'sort' => $this->input->post('sort'),
                'content' => $this->input->post('content'),
                'create_time' => strtotime($this->input->post('create_time'))
            )
        );
        $bool = $this->info_news->save($data);
        //写入日志
        $this->sys_log->insert($this->main_section_name, (!$data['id']) ? '1' : '2', $bool);
        $config['icon'] = 1;
        $config['url'] = site_url('info_news?sys_cid=' . $this->sys_cid . '&cid=' . $this->cid);
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