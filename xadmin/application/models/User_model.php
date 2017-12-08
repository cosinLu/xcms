<?php

/**
 * Created by PhpStorm.
 * User: MengXianghan
 * Date: 2016/8/23
 * Time: 21:12
 */
class User_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取列表
    public function get_list($key = '', $page = '')
    {
        $this->db->select('t.*');
        $this->db->select('t1.rel_path avatar');
        $this->db->from('user t');
        $this->db->join('uploads t1', 't1.id=t.avatar', 'left');
        if ($key != '') {
            $this->db->like('concat(t.username,t.phone,t.email)', $key);
        }
        $config['total_rows'] = $this->db->count_all_results(NULL, FALSE);
        $config['per_page'] = config_item('my_per_page');
        $config['cur_page'] = $page;
        $this->pagination->initialize($config);
        $this->db->order_by('t.create_time,t.id desc');
        $this->db->limit($config['per_page'], ($page - 1) * $config['per_page']);
        $res['list'] = $this->db->get()->result_array();
        $res['pagination'] = $this->pagination->create_ajax_links();
        $res['total'] = $config['total_rows'];

        return $res;
    }

    //修改
    public function update($id = '')
    {
        $this->db->where('id', $id);
        $res = $this->db->get('user')->row_array();

        return $res;
    }

    //保存
    public function save($post = array())
    {
        if ($post['id']) {
            $bool = $this->db->where('id', $post['id'])->update('user', $post['vals']);
        } else {
            $bool = $this->db->insert('user', $post['vals']);
        }

        return $bool;
    }

}