<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/22
 * Time: 10:04
 */
class Navigation extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('navigation_model', 'navigation');
        $this->load->library('category', array('tb_name' => 'navigation', 'default' => '主导航'), 'category');
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('navigation/get_list?sys_cid=' . $this->sys_cid);
        $url['insert_btn'] = $this->sys_auth->set_auth(MYINSERT, $this->col_auth, '<a class="btn btn-primary" href="' . site_url('navigation/insert?sys_cid=' . $this->sys_cid) . '">新增</a>');
        $url['save_url'] = site_url('navigation/save?sys_cid=' . $this->sys_cid);
        $url['del_url'] = site_url('navigation/del?sys_cid=' . $this->sys_cid);
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('navigation/index.html');
    }

    //获得列表
    public function get_list()
    {
        $data['list'] = $this->navigation->get_list();
        foreach ($data['list']['list'] as $key => $val) {
            $disabled_insert_next_btn = '<a href="javascript:;" class="disabled">新增下级</a>';
            $disabled_update_btn = '<a href="javascript:;" class="disabled">编辑</a>';
            $disabled_del_btn = '<a href="javascript:;" class="disabled">删除</a>';
            if ($val['level'] == '1') {
                if ($val['add_next_auth'] == '1') {
                    $data['list']['list'][$key]['opera_btn'][] = $this->sys_auth->set_auth(MYINSERT, $this->col_auth, '<a href="' . site_url('navigation/insert?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">新增下级</a>', $disabled_insert_next_btn);
                } else {
                    $data['list']['list'][$key]['opera_btn'][] = $disabled_insert_next_btn;
                }
            }
            if ($val['edit_auth'] == '1') {
                $data['list']['list'][$key]['opera_btn'][] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a href="' . site_url('navigation/update?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">编辑</a>', $disabled_update_btn);
            } else {
                $data['list']['list'][$key]['opera_btn'][] = $disabled_update_btn;
            }
            if ($val['del_auth'] == '1') {
                $data['list']['list'][$key]['opera_btn'][] = $this->sys_auth->set_auth(MYDEL, $this->col_auth, '<a href="javascript:;" data-name="delCol" data-id="' . $val['id'] . '">删除</a>', $disabled_del_btn);
            } else {
                $data['list']['list'][$key]['opera_btn'][] = $disabled_del_btn;
            }
            $data['list']['list'][$key]['prefix'] = str_repeat('&nbsp;&nbsp;', ($val['level'] - 1) * 2) . (($val['level'] > 1) ? '└─&nbsp;' : '');
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $id = $this->input->get('id');
        $data['cols'] = $this->category->ddl('pid', 0, $id);
        $data['position'] = $this->sys_dict->rbl('position', 'position');
        $data['display'] = $this->sys_dict->rbl('display', 'display');
        $this->load->view('navigation/insert.html', $data);
    }

    //更新
    public function update()
    {
        $data['item'] = $this->navigation->update();
        $data['cols'] = $this->category->ddl('pid', $data['item']['id'], $data['item']['pid']);
        $data['position'] = $this->sys_dict->rbl('position', 'position', $data['item']['position']);
        $data['display'] = $this->sys_dict->rbl('display', 'display', $data['item']['display']);
        $this->load->view('navigation/update.html', $data);
    }


    //保存
    public function save()
    {
        $bool = $this->navigation->save();
        $this->sys_log->insert($this->section_name, (!$this->input->post('id')) ? '1' : '2', $bool);//日志
        $config['icon'] = 1;
        $config['url'] = site_url('navigation?sys_cid=' . $this->sys_cid);
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

    //删除
    public function del()
    {
        $id = $this->input->post('id');
        $rows = $this->category->del($id);//删除栏目
        $this->sys_log->insert($this->section_name, '3', $rows);//日志
        echo $rows;
    }

}