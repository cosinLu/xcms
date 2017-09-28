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
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('navigation/get_list?sys_cid=' . $this->sys_cid);
        $url['insert_btn'] = $this->auth->set($this->config->item('insert', 'mcms'), $this->sys_menu_auth, '<a class="btn btn-primary btn-sm" href="' . site_url('navigation/insert?sys_cid=' . $this->sys_cid) . '">新增</a>');
        $url['save_url'] = site_url('navigation/save?sys_cid=' . $this->sys_cid);
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('navigation/index.html');
    }

    //获取列表
    public function get_list()
    {
        $data['list'] = $this->navigation->get_list();
        foreach ($data['list']['list'] as $key => $val) {
            $data['list']['list'][$key]['display_name'] = '<span style="color:' . $val['display_color'] . ';">' . $val['display_name'] . '</span>';
            $data['list']['list'][$key]['prefix'] = str_repeat('&nbsp;&nbsp;', ($val['level'] - 1) * 2) . (($val['level'] > 1) ? '└─&nbsp;' : '');
            $disabled_insert_next_btn = '<a href="javascript:;" class="disabled">新增下级</a>';
            $disabled_update_btn = '<a href="javascript:;" class="disabled">编辑</a>';
            $disabled_del_btn = '<a href="javascript:;" class="disabled">删除</a>';
            if ($val['add_next_auth'] == '1') {
                $data['list']['list'][$key]['opera_btn'][] = $this->auth->set($this->config->item('insert', 'mcms'), $this->sys_menu_auth, '<a href="' . site_url('navigation/insert?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">新增下级</a>', $disabled_insert_next_btn);
            } else {
                $data['list']['list'][$key]['opera_btn'][] = $disabled_insert_next_btn;
            }
            if ($val['edit_auth'] == '1') {
                $data['list']['list'][$key]['opera_btn'][] = $this->auth->set($this->config->item('update', 'mcms'), $this->sys_menu_auth, '<a href="' . site_url('navigation/update?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">编辑</a>', $disabled_update_btn);
            } else {
                $data['list']['list'][$key]['opera_btn'][] = $disabled_update_btn;
            }
            if ($val['del_auth'] == '1') {
                $data['list']['list'][$key]['opera_btn'][] = $this->auth->set($this->config->item('del', 'mcms'), $this->sys_menu_auth, '<a href="javascript:;" class="del-hook" data-id="' . $val['id'] . '" data-url="' . site_url('navigation/del?sys_cid=' . $this->sys_cid) . '">删除</a>', $disabled_del_btn);
            } else {
                $data['list']['list'][$key]['opera_btn'][] = $disabled_del_btn;
            }
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $id = $this->input->get('id');
        $data['type'] = $this->tree->ddl($this->navigation->data(), 'pid', $id);
        $data['col'] = $this->tree->ddl($this->navigation->cols(), 'col', '', '', '', array('', '-请选择-'));
        $data['dict'] = $this->dictionary->dict(array(
            array('rbl', 'target', 'target'),
            array('rbl', 'position', 'position'),
            array('rbl', 'display', 'display')
        ));
        $this->load->view('navigation/insert.html', $data);
    }

    //修改
    public function update()
    {
        $id = $this->input->get('id');
        $data['item'] = $this->navigation->update($id);
        $data['type'] = $this->tree->ddl($this->navigation->data(), 'pid', $data['item']['pid'], $data['item']['id']);
        $data['col'] = $this->tree->ddl($this->navigation->cols(), 'col', '', '', '', array('', '-请选择-'));
        $data['dict'] = $this->dictionary->dict(array(
            array('rbl', 'target', 'target', $data['item']['target']),
            array('rbl', 'position', 'position', $data['item']['position']),
            array('rbl', 'display', 'display', $data['item']['display'])
        ));
        $this->load->view('navigation/update.html', $data);
    }


    //保存
    public function save()
    {
        $post = array(
            'id' => $this->input->post('id'),
            'vals' => array(
                'name' => $this->input->post('name'),
                'pid' => $this->input->post('pid'),
                'col' => $this->input->post('col'),
                'url' => $this->input->post('url'),
                'target' => $this->input->post('target'),
                'position' => $this->input->post('position'),
                'display' => $this->input->post('display'),
                'sort' => $this->input->post('sort'),
            )
        );
        $bool = $this->navigation->save($post);
        //写入日志
        $this->oplog->insert($this->section_name, (!$post['id']) ? '1' : '2', $bool);
        $config['icon'] = 1;
        $config['url'] = site_url('navigation?sys_cid=' . $this->sys_cid);
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

    //删除
    public function del()
    {
        $id = $this->input->post('id');
        //删除栏目
        $rows = $this->tree->del($this->navigation->data(), 'navigation', $id);
        //写入日志
        $this->oplog->insert($this->section_name, '3', $rows);
        echo $rows;
    }

}