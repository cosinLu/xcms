<?php

/**
 * Created by PhpStorm.
 * User: MengXianghan
 * Date: 2016/8/23
 * Time: 21:12
 */
class Sys_oplog_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取列表
    public function get_list($page = '', $start_time = '', $stop_time = '')
    {
        $this->db->select('t.*');
        $this->db->select('t1.name status_name,t1.color status_color');
        $this->db->select('t2.name opera_name');
        $this->db->from('sys_oplog t');
        $this->db->join('common_dict t1', 't1.ident=t.status', 'left');
        $this->db->join('common_dict t2', 't2.ident=t.opera', 'left');
        if ($start_time && $stop_time) {
            $this->db->where('t.time >', strtotime($start_time));
            $this->db->where('t.time <', strtotime($stop_time));
        }
        if ($this->session->sys_session['user_type'] > 1) {
            $this->db->where('t.user_id', $this->session->sys_session['user_id']);
        }
        $config['total_rows'] = $this->db->count_all_results(NULL, FALSE);
        $config['per_page'] = config_item('my_per_page');
        $config['cur_page'] = $page;
        $this->pagination->initialize($config);
        $this->db->order_by('t.time desc');
        $this->db->limit($config['per_page'], ($page - 1) * $config['per_page']);
        $res['list'] = $this->db->get()->result_array();
        $res['pagination'] = $this->pagination->create_ajax_links();
        $res['total'] = $config['total_rows'];

        return $res;
    }

}