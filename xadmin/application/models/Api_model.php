<?php
defined('BASEPATH') OR exit('Error');

/**
 * Author: 孟祥涵
 * Date: 2016/5/9
 * Time: 16:32
 * Email: 1056811341@qq.com
 */
class Api_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    //删除
    public function del($tbname = '', $id = '', $primary = '')
    {
        $this->db->where($primary, $id);
        $this->db->delete($tbname);
        $rows = $this->db->affected_rows();

        return $rows;
    }

    //批量删除
    public function batch_del($tbname = '', $id = array(), $primary = '')
    {
        $this->db->where_in($primary, $id);
        $this->db->delete($tbname);
        $rows = $this->db->affected_rows();

        return $rows;
    }

    //修改指定数据
    public function editable($tbname = '', $pk = '', $field = '', $value = '')
    {
        $this->db->where('id', $pk);
        $bool = $this->db->update($tbname, array($field => $value));

        return $bool;
    }

}