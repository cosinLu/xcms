<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/22
 * Time: 10:04
 */
class Info_col extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('info_col_model', 'info_col');
        $this->load->library('category', array('tb_name' => 'info_col'), 'category');
        $this->set_url();
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('info_col/get_list?sys_cid=' . $this->sys_cid);
        $url['insert_btn'] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a class="btn btn-primary" href="' . site_url('info_col/insert?sys_cid=' . $this->sys_cid) . '">新增</a>');
        $url['save_url'] = site_url('info_col/save?sys_cid=' . $this->sys_cid);
        $url['del_url'] = site_url('info_col/del?sys_cid=' . $this->sys_cid);
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('info_col/index.html');
    }

    //获得列表
    public function get_list()
    {
        $data['list'] = $this->info_col->get_list();
        foreach ($data['list']['list'] as $key => $val) {
            $data['list']['list'][$key]['insert_next_btn'] = $this->sys_auth->set_auth(MYINSERT, $this->col_auth, '<a href="' . site_url('info_col/insert?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">新增下级</a>', '<a href="javascript:;" class="disabled">新增下级</a>');
            $data['list']['list'][$key]['update_btn'] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a href="' . site_url('info_col/update?sys_cid=' . $this->sys_cid . '&id=' . $val['id']) . '">编辑</a>', '<a href="javascript:;" class="disabled">编辑</a>');
            $data['list']['list'][$key]['del_btn'] = $this->sys_auth->set_auth(MYUPDATE, $this->col_auth, '<a href="javascript:;" data-name="delCol" data-id="' . $val['id'] . '">删除</a>', '<a href="javascript:;" class="disabled">删除</a>');
            $data['list']['list'][$key]['prefix'] = str_repeat('&nbsp;&nbsp;', ($val['level'] - 1) * 2) . ((!empty($val['level'] - 1) ? '└─&nbsp;' : ''));
        }
        echo json_encode($data);
    }

    //新增
    public function insert()
    {
        $id = $this->input->get('id');
        $data['info_col'] = $this->category->insert_option($id);
        $data['info_type'] = dropdown_list($this->info_col->info_type(), 'info_type_id');
        $data['pic'] = $this->sys_dict->radio_button_list(21, 'pic');
        $data['display'] = $this->sys_dict->radio_button_list(9, 'display');
        $this->load->view('info_col/insert.html', $data);
    }

    //更新
    public function update()
    {
        $data['item'] = $this->info_col->update();
        $data['info_type'] = dropdown_list($this->info_col->info_type(), 'info_type_id', $data['item']['info_type_id']);
        $data['info_col'] = $this->category->update_option($data['item']['id'], $data['item']['pid']);
        $data['pic'] = $this->sys_dict->radio_button_list(21, 'pic', $data['item']['pic']);
        $data['display'] = $this->sys_dict->radio_button_list(9, 'display', $data['item']['display']);
        $this->load->view('info_col/update.html', $data);
    }


    //保存
    public function save()
    {
        $bool = $this->info_col->save();
        if ($bool) {
            switch ($this->is_save) {
                case '1':
                    $this->prompt->success('操作成功！', site_url('info_col?sys_cid=' . $this->sys_cid));
                    break;
                case '2':
                    $this->prompt->success('操作成功！', $this->peferer);
                    break;
            }
        } else {
            $this->prompt->error('操作失败！', site_url('info_col?sys_cid=' . $this->sys_cid));
        }
    }

    //删除
    public function del()
    {
        $id = $this->input->post('id');
        $rows = $this->category->del($id);//删除栏目
        echo $rows;
    }

}