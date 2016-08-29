<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/22
 * Time: 10:05
 */
class MY_Controller extends CI_Controller
{
    protected $sys_session;
    protected $sys_cid;//系统栏目标识
    protected $prferer;//上一个页面url
    protected $is_save;//保存：1=保存，2=保存并继续新增

    public function __construct()
    {
        parent::__construct();
        $this->sys_session = $this->session->sys_session;
        $this->sys_cid = $this->input->get('sys_cid');
        $this->peferer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
        $this->is_save = ($this->input->post('is_save') == '') ? '1' : $this->input->post('is_save');
        $this->load->library('category', array('tb_name' => 'sys_col'), 'my_category');

        $this->menu();//菜单
        $this->sidebar();
    }

    //菜单
    public function menu()
    {
        $sys_col = $this->sys_col();//系统栏目
        $sys_col_sort = $this->my_category->children($sys_col);//排序后的系统栏目
        $sys_col_parent_id = $this->my_category->parent_id($sys_col, $this->sys_cid, TRUE);//获得栏目所有上级id
        $sys_col_url = $this->my_category->children_url($sys_col_sort);//获得系统栏目url
        foreach ($sys_col_sort as $key => $val) {
            if ($val['pid'] == 0) {
                $data['menu'][$key] = $val;
                $data['menu'][$key]['url'] = $sys_col_url[$key];
                $data['menu'][$key]['active'] = ($val['id'] == $sys_col_parent_id[0]) ? 'active' : '';
            }
            if ($val['id'] == $this->sys_cid) {
                $data['section_name'] = $val['name'];
            }
        }
        $this->load->vars($data);
    }

    //侧边栏
    public function sidebar()
    {
        $str = '';
        $parent_level = 0;
        $sys_col = $this->sys_col();//系统栏目
        $sys_col_parent_id = $this->my_category->parent_id($sys_col, $this->sys_cid, TRUE);//获得当前栏目所有上级id
        $sys_col_chidren = $this->my_category->children($sys_col, $sys_col_parent_id[0]);//获得当前栏目一级栏目的所有下级栏目
        foreach ($sys_col_chidren as $val) {
            $level = $val['level'];
            $dir = ($val['dir']) ? $val['dir'] . '/' : '';
            $sys_ctrl = ($val['ctrl']) ? $val['ctrl'] . '/' : '';
            $method = ($val['method']) ? $val['method'] . '/' : '';
            $param = (!empty($val['param'])) ? '&' . $val['param'] : '';
            $current = ($val['id'] == $this->sys_cid) ? 'current' : '';
            if ($level < $parent_level) {
                $str .= '</li>' . str_repeat('</ul></li>', $parent_level - $level);
            } elseif ($level > $parent_level) {
                $str .= '<ul data-level="' . ($level - 1) . '">';
            } else {
                $str .= '</li>';
            }
            $str .= '<li>';
            if ($dir == '' && $sys_ctrl == '' && $method == '') {
                $str .= '<a href="javascript:;" class="' . $current . '" data-name="mtree_link">';
            } else {
                $str .= '<a href="' . site_url($dir . $sys_ctrl . $method . '?sys_cid=' . $val['id'] . $param) . '" class="' . $current . '" data-name="mtree_link">';
            }
            $str .= '<span data-name="mtree_indent"></span>';
            $str .= '<span data-name="mtree_btn"></span>';
            $str .= '<span data-name="mtree_name">' . $val['name'] . '</span>';
            $str .= '</a>';
            $parent_level = $level;
        }
        $str .= str_repeat('</li></ul>', $parent_level + 1);
        $data['sidebar'] = $str;
        $this->load->vars($data);
    }

    //系统栏目
    public function sys_col()
    {
        $this->db->from('sys_col');
        $this->db->where('display', 'show');
        $this->db->order_by('sort asc,id asc');
        $res = $this->db->get()->result_array();
        return $res;
    }


}

//信息控制器
class MY_info_Controller extends MY_Controller
{
    protected $info_cid;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('category', array('tb_name' => 'info_col'), 'category');
        $this->info_cid = $this->input->get('info_cid');
        $this->info_col_sidebar();
    }

    //获得信息栏目
    public function info_col_sidebar()
    {
        $this->db->select('info_col.*');
        $this->db->select('info_type.sys_ctrl');
        $this->db->from('info_col');
        $this->db->join('info_type', 'info_type.id=info_col.info_type_id', 'left');
        $this->db->where('info_col.display', 'show');
        $info_col_res = $this->db->get()->result_array();
        $info_col_sort = $this->category->children($info_col_res, 0, TRUE);
        $str = '';
        $parent_level = 0;
        foreach ($info_col_sort as $val) {
            $level = $val['level'];
            $current = ($val['id'] == $this->info_cid) ? 'current' : '';
            if ($level < $parent_level) {
                $str .= '</li>' . str_repeat('</ul></li>', $parent_level - $level);
            } elseif ($level > $parent_level) {
                $str .= '<ul data-level="' . ($level) . '">';
            } else {
                $str .= '</li>';
            }
            $str .= '<li>';
            if ($val['sys_ctrl'] == '') {
                $str .= '<a class="' . $current . '" href="javascript:;" data-name="mtree_link">';
            } else {
                $str .= '<a class="' . $current . '" href="' . site_url($val['sys_ctrl'] . '?sys_cid=' . $this->sys_cid . '&info_cid=' . $val['id']) . '" data-name="mtree_link">';
            }
            $str .= '<span data-name="mtree_indent"></span>';
            $str .= '<span data-name="mtree_btn"></span>';
            $str .= '<span data-name="mtree_name">' . $val['name'] . '</span>';
            $str .= '</a>';
            $parent_level = $level;
        }
        $str .= str_repeat('</li></ul>', $parent_level + 1);
        $data['info_col_sidebar'] = $str;
        $this->load->vars($data);
    }

}