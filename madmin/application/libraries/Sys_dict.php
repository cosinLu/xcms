<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/22
 * Time: 18:07
 */
class Sys_dict
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * 单选按钮列表
     *
     * @param int $ident        属性上级标识
     * @param string $name      元素名称
     * @param string $check_val 选中值
     * @param string $disabled  禁用
     *
     * @return string
     */
    public function rbl($ident = '', $name = '', $check_val = '', $disabled = '')
    {
        $str = '';
        $res = $this->all($ident);
        foreach ($res as $key => $val) {
            if ($check_val == '') {
                $checked = ($key == 0) ? 'checked' : '';
            } else {
                $checked = ($val['ident'] == $check_val) ? 'checked' : '';
            }
            $str .= '<label><input type="radio" name="' . $name . '" value="' . $val['ident'] . '" ' . $checked . ' ' . $disabled . '><ins>' . $val['name'] . '</ins></label>';
        }

        return $str;
    }

    public function all($ident = '')
    {
        $this->CI->db->select('t1.*');
        $this->CI->db->from('sys_dict as t');
        $this->CI->db->join('sys_dict as t1', 't1.pid=t.id', 'left');
        $this->CI->db->where('t.ident', $ident);
        $this->CI->db->group_by('t1.id');
        $this->CI->db->order_by('t1.sort asc,t1.id asc');
        $res = $this->CI->db->get('sys_dict')->result_array();

        return $res;
    }

    /**
     * 复选框列表
     *
     * @param int $ident        属性上级标识
     * @param string $name      元素名称
     * @param string $check_val 选中值
     * @param string $disabled  禁用
     *
     * @return string
     */
    public function cbl($ident = '', $name = '', $check_val = '', $disabled = '')
    {
        $str = '';
        $check_val_arr = explode(',', $check_val);
        $res = $this->all($ident);
        foreach ($res as $val) {
            if (is_array($check_val_arr)) {
                $checked = (in_array($val['ident'], $check_val_arr)) ? 'checked' : '';
            } else {
                $checked = ($val['ident'] == $check_val) ? 'checked' : '';
            }
            $str .= '<label><input type="checkbox" name="' . $name . '[]" value="' . $val['ident'] . '" ' . $checked . ' ' . $disabled . '><ins>' . $val['name'] . '</ins></label>';
        }

        return $str;
    }

    /**
     * 下拉列表
     *
     * @param int $ident         属性上级标识
     * @param string $name       元素名称
     * @param string $select_val 选中值
     * @param string $disabled   禁用
     *
     * @return string
     */
    public function ddl($ident = '', $name = '', $select_val = '', $disabled = '')
    {
        $str = '';
        $res = $this->all($ident);
        $str .= '<select name="' . $name . '" class="form-control" ' . $disabled . '>';
        $str .= '<option value="0">-请选择-</option>';
        foreach ($res as $val) {
            $selected = ($val['ident'] == $select_val) ? 'selected' : '';
            $str .= '<option value="' . $val['ident'] . '" ' . $selected . '>' . $val['name'] . '</option>';
        }
        $str .= '</select>';

        return $str;

    }
}