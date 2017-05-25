<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/30
 * Time: 12:51
 */
class Sys_user_auth_model extends MY_Model
{
    protected $user_id;
    protected $tb_sys_col;
    protected $tb_sys_col_auth;
    protected $tb_sys_dict;
    protected $tb_sys_user_auth;

    public function __construct()
    {
        parent::__construct();
        $this->user_id = $this->input->get('user_id');
        $this->tb_sys_col = $this->db->dbprefix . 'sys_col';
        $this->tb_sys_col_auth = $this->db->dbprefix . 'sys_col_auth';
        $this->tb_sys_dict = $this->db->dbprefix . 'sys_dict';
        $this->tb_sys_user_auth = $this->db->dbprefix . 'sys_user_auth';
        $this->load->library('category', array('tb_name' => 'sys_col'), 'category');
    }

    //获得列表
    public function get_list()
    {
        $sql = "SELECT
                t.*, GROUP_CONCAT(t2.name order by t2.sort asc,t2.id asc) AS auth_name_str,
                GROUP_CONCAT(t2.ident order by t2.sort asc,t2.id asc) AS auth_ident_str,
                    t3.col_auth_str,
                t3.col_id
            FROM
                " . $this->tb_sys_col . " as t
            LEFT JOIN " . $this->tb_sys_col_auth . " as t1 ON t1.col_id = t.id
            LEFT JOIN " . $this->tb_sys_dict . " as t2 ON t2.ident = t1.col_auth
            LEFT JOIN (
                SELECT
                    GROUP_CONCAT(col_auth) AS col_auth_str,
                    col_id
                FROM
                    " . $this->tb_sys_user_auth . "
                WHERE user_id=" . $this->user_id . "
                GROUP BY
                    col_id
            ) AS t3 ON t3.col_id = t.id
            WHERE
                t.display = 'show'
            GROUP BY
                t.id";
        $res = $this->db->query($sql)->result_array();
        $list = $this->category->children($res);
        return $list;
    }

    //删除
    public function del()
    {
        $this->db->where('user_id', $this->user_id);
        $this->db->delete('sys_user_auth');
        $rows = $this->db->affected_rows();
        return $rows;
    }

    //添加权限
    public function insert()
    {
        $col_id_arr = $this->input->post('id');
        $col_auth_arr = $this->input->post('auth');
        if (!empty($col_id_arr) && !empty($col_auth_arr)) {
            $vals = array();
            foreach ($col_id_arr as $val) {
                if (empty($col_auth_arr[$val])) {
                    $vals[] = array(
                        'user_id' => $this->user_id,
                        'col_id' => $val,
                        'col_auth' => ''
                    );
                } else {
                    foreach ($col_auth_arr[$val] as $item) {
                        $vals[] = array(
                            'user_id' => $this->user_id,
                            'col_id' => $val,
                            'col_auth' => $item
                        );
                    }
                }
            }
            $bool = $this->db->insert_batch('sys_user_auth', array_reverse($vals));
            return $bool;
        }
    }

}