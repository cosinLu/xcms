<?php

/**
 * Created by PhpStorm.
 * User: MengXianghan
 * Date: 2016/8/23
 * Time: 21:11
 */
class Info_model extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('info_model_model');
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('info_model/get_list');
        $url['insert_btn'] = $this->auth->set(config_item('my_insert'), $this->sys_menu_auth, '<a class="btn btn-primary btn-sm" href="' . site_url('info_model/insert') . '">新增</a>');
        $url['del_btn'] = $this->auth->set(config_item('my_del'), $this->sys_menu_auth, '<a class="btn btn-danger btn-sm batch-del-hook" href="javascript:;" data-tb="model" data-checkname="id" data-url = "' . site_url('api/batch_del') . '">删除</a>');
        $url['save_url'] = site_url('info_model/save');
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('info_model/index.html');
    }

    //获取列表
    public function get_list()
    {
        $key = $this->input->post('key');
        $page = ($this->input->post('page')) ?: 1;
        $data['list'] = $this->info_model_model->get_list($key, $page);
        foreach ($data['list']['list'] as $key => $val) {
            $data['list']['list'][$key]['display_name'] = '<span style="color:' . $val['display_color'] . ';">' . $val['display_name'] . '</span>';
            $data['list']['list'][$key]['opera_btn'][] = $this->auth->set(config_item('my_update'), $this->sys_menu_auth, '<a href="' . site_url('info_model/update?id=' . $val['id']) . '">编辑</a>', '<a href="javascript:;" class="disabled">编辑</a>');
            $data['list']['list'][$key]['opera_btn'][] = $this->auth->set(config_item('my_del'), $this->sys_menu_auth, '<a href="javascript:;" class="del-hook" data-tb="model" data-id="' . $val['id'] . '" data-url="' . site_url('api/del') . '">删除</a>', '<a href="javascript:;" class="disabled">删除</a>');
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $data['dict'] = $this->dictionary->dict(array(
            array('rbl', 'display', 'display')
        ));
        $this->load->view('info_model/insert.html', $data);
    }

    //修改
    public function update()
    {
        $id = $this->input->get('id');
        $data['item'] = $this->info_model_model->update($id);
        $data['dict'] = $this->dictionary->dict(array(
            array('rbl', 'display', 'display', $data['item']['display'])
        ));
        $this->load->view('info_model/update.html', $data);
    }

    //保存
    public function save()
    {
        $post = array(
            'id' => $this->input->post('id'),
            'vals' => array(
                'name' => $this->input->post('name'),
                'model' => $this->input->post('model'),
                'list_model' => $this->input->post('list_model'),
                'detail_model' => $this->input->post('detail_model'),
                'display' => $this->input->post('display'),
                'sort' => $this->input->post('sort')
            )
        );
        $bool = $this->info_model_model->save($post);
        //写入日志
        $this->oplog->insert($this->section_name, (!$post['id']) ? '1' : '2', $bool);
        $config['icon'] = 1;
        $config['url'] = site_url('info_model');
        if ($bool) {
            switch ($this->submit_type) {
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