<?php
defined('BASEPATH') OR exit('Error');

/**
 * Author: 孟祥涵
 * Date: 2016/4/29
 * Time: 23:38
 * Email: 1056811341@qq.com
 */
class Tree
{
    private $CI;
    private $sort = 'sort';
    private $id = 'id';
    private $pid = 'pid';
    private $level = 'level';
    private $name = 'name';
    private $order_by = 'asc';

    public function __construct($params = array())
    {
        $this->CI =& get_instance();

        //初始化参数
        $this->initialize($params);
    }

    /**
     * 初始化参数
     *
     * @param array $config
     */
    public function initialize($config = array())
    {
        foreach ($config as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * 排序
     *
     * @param $a
     * @param $b
     * @return int
     */
    public function compare_sort($a, $b)
    {
        $sort = isset($a[$this->sort]) ? $this->sort : $this->id;
        $a_sort = $a[$sort];
        $b_sort = $b[$sort];
        if ($a_sort == $b_sort) {
            if ($this->order_by == 'asc') {
                return ($a[$this->id] > $b[$this->id]) ? 1 : -1;
            } else {
                return ($a[$this->id] < $b[$this->id]) ? 1 : -1;
            }
        }

        if ($this->order_by == 'asc') {
            return ($a_sort > $b_sort) ? 1 : -1;
        } else {
            return ($a_sort < $b_sort) ? 1 : -1;
        }
    }

    /**
     * 序列化
     *
     * @param array $data 数据源
     * @param int $pid 标识，默认从0开始
     * @return array
     */
    public function serialize($data = array(), $pid = 0)
    {
        $children = array();
        $res = array();

        foreach ($data as $val) {
            if ($val[$this->pid] == $pid) {
                $children[] = $val;
            }
        }
        if (empty($children)) {
            return array();
        }
        usort($children, array('Tree', 'compare_sort'));
        foreach ($children as $val) {
            $res[] = $val;
            $temp = $this->serialize($data, $val[$this->id]);
            if (!empty($temp)) {
                $res = array_merge($res, $temp);
            }
        }

        return $res;
    }

    /**
     * 反转序列化
     *
     * @param array $data 数据源
     * @param string $id 标识，必填
     * @return array
     */
    public function reverse_serialize($data = array(), $id = '')
    {
        if ($id == '') return array();
        $parent = array();
        $res = array();
        foreach ($data as $val) {
            if ($val[$this->id] == $id) {
                $parent[] = $val;
                break;
            }
        }
        if (empty($parent)) {
            return array();
        }
        foreach ($parent as $val) {
            $res[] = $val;
            $temp = $this->reverse_serialize($data, $val[$this->pid]);
            if (!empty($temp)) {
                $res = array_merge($res, $temp);
            }
        }

        return $res;
    }

    /**
     * 获取子级
     *
     * @param array $data 数据源
     * @param int $pid 标识
     * @param bool $is_self 是否包含自身
     * @param string $variable 指定的键值，string或array
     * @return array
     */
    public function get_children($data = array(), $pid = 0, $is_self = FALSE, $variable = '')
    {
        $pid = $this->get_field($data, $pid);
        $res = $this->serialize($data, $pid);

        //是否包含自身
        if ($is_self) {
            foreach ($data as $val) {
                if ($val[$this->id] == $pid) {
                    $res = array_merge(array($val), $res);
                    break;
                }
            }
        }

        //返回指定键值数据
        return $this->_get_variable($res, $variable);
    }

    /**
     * 获取父级
     *
     * @param array $data 数据源
     * @param string $id 标识
     * @param bool $is_self 是否包含自身
     * @param string $variable 指定的键值，string或array
     * @return array
     */
    public function get_parent($data = array(), $id = '', $is_self = FALSE, $variable = '')
    {
        $id = $this->get_field($data, $id);
        $res = $this->reverse_serialize($data, $id);

        if (!$is_self) {
            array_shift($res);
        }

        //返回指定键值数据
        return $this->_get_variable($res, $variable);
    }

    /**
     * 获得标识
     *
     * @param array $data 数据源
     * @param array $arr array('字段名','值')
     * @return string
     */
    public function get_field($data = array(), $arr = array(), $variable = '')
    {
        $arr = is_array($arr) ? $arr : explode(',', $arr);
        if (count($arr) > 1) {
            $res = '';
            $variable = $variable ?: $this->id;
            $variable = is_array($variable) ? $variable : explode(',', $variable);
            foreach ($data as $val) {
                if ($val[$arr[0]] == $arr[1]) {
                    if (count($variable) == 1) {
                        $res = $val[$variable[0]];
                    } else {
                        foreach ($variable as $val) {
                            $res[$val] = $val[$val];
                        }
                    }
                    break;
                }
            }

            return $res;
        }

        return implode(',', $arr);
    }

    /**
     * 返回指定键值数据
     *
     * @param array $res 数据源
     * @param string $variable 指定的键值，string或array
     * @return array
     */
    public function _get_variable($res = array(), $variable = '')
    {
        if (empty($res)) return array();

        if (is_string($variable) && $variable != '') {
            $variable = explode(',', $variable);
        }

        if (is_array($variable) && !empty($variable)) {
            foreach ($res as $key => $val) {
                foreach ($variable as $val_key) {
                    if (count($variable) > 1) {
                        $res_key[$key][$val_key] = isset($val[$val_key]) ? $val[$val_key] : '';
                    } else {
                        $res_key[] = isset($val[$val_key]) ? $val[$val_key] : '';
                    }
                }
            }

            return $res_key;
        }

        return $res;
    }

    /**
     * 下拉菜单
     *
     * @param array $data 数据源
     * @param string $name 下拉菜单名称
     * @param string $selected_val 选中值
     * @param string $disabled_val 禁用值
     * @param string $type 类型
     * @param bool $default 默认显示，默认显示“根目录”，string或array
     * @return string
     */
    public function ddl($data = array(), $name = '', $selected_val = '', $disabled_val = '', $type = '', $default = TRUE)
    {
        $data = $this->serialize($data);
        $start_level = 1;
        $str = '';
        $str .= '<select name="' . $name . '" class="form-control">';

        if ($default === TRUE) {
            $str .= '<option data-content="" value="-1">根目录</option>';
        } else if (is_array($default)) {
            $str .= '<option data-content="" value="' . $default[0] . '">' . $default[1] . '</option>';
        } else if (is_string($default) && $default != '') {
            $str .= '<option data-content="" value="-1">' . $default . '</option>';
        }
        //获取当前分类的所有下级id，包含当前分类
        if ($disabled_val != '') {
            $children_id = $this->get_children($data, $disabled_val, TRUE, 'id');
        }
        foreach ($data as $key => $val) {
            $prefix = ($val[$this->level] > $start_level) ? '└─&nbsp;' : '';
            $space = str_repeat('&nbsp;&nbsp;', ($val[$this->level] - $start_level) * 2);
            //选中
            $selected = ($val[$this->id] == $selected_val) ? 'selected' : '';
            //禁止选择
            $disabled = '';
            if ($disabled_val != '') {
                $disabled = (in_array($val[$this->id], $children_id)) ? 'disabled' : '';
            }
            //禁止选择[用于只允许选择相同类型]
            if ($type != '' && isset($val['model_id'])) {
                $disabled = ($val['model_id'] != $type) ? 'disabled' : '';
            }
            //附加数据
            $content = isset($val['data']) ? $val['data'] : '';
            $str .= '<option data-content=\'' . $content . '\' value="' . $val[$this->id] . '" ' . $selected . ' ' . $disabled . '>' . $space . $prefix . $val[$this->name] . '</option>';
        }

        $str .= '</select>';

        return $str;
    }

    /**
     * 获取一条数据
     *
     * @param string $table 表名
     * @param array $where 查询条件
     * @return array
     */
    public function one($table = '', $where = array())
    {
        if (empty($where)) return array();
        $this->CI->db->where($where);
        $res = $this->CI->db->get($table)->row_array();

        return $res;
    }

    /**
     * 新增
     *
     * @param string $table 表名
     * @param array $post 需要提交的数据
     * @return mixed
     */
    public function insert($table = '', $post = array())
    {
        if ($post[$this->pid] == 0) {
            $level = 1;
        } else {
            $res = $this->one($table, array($this->id => $post[$this->pid]));
            $level = $res[$this->level] + 1;
        };
        //写入数据
        $post[$this->level] = $level;
        $this->CI->db->insert($table, $post);
        $insert_id = $this->CI->db->insert_id();

        return $insert_id;
    }

    /**
     * 修改
     *
     * @param array $data 数据源
     * @param string $table 表名
     * @param string $id 标识
     * @param array $post 需要提交的数据
     * @return bool
     */
    public function update($data = array(), $table = '', $id = '', $post = array())
    {
        if (empty($id)) {
            return FALSE;
        }
        if (isset($post[$this->pid]) && $post[$this->pid] < 1) {
            $post[$this->pid] = 0;
        }
        //当前分类信息
        $res = $this->one($table, array($this->id => $id));
        //上级分类信息
        $parent_row = $this->one($table, array($this->id => $post[$this->pid]));
        //上级分类级别
        $parent_level = (empty($parent_row)) ? 0 : $parent_row[$this->level];
        //当前分类新级别
        $current_new_level = $parent_level + 1;
        //当前分类级别
        $current_level = $res[$this->level];
        //等级差值
        $differ_level = $current_new_level - $current_level;
        //修改当前分类所有下级级别
        //1.获取当前分类所有下级
        $children = $this->get_children($data, $id, FALSE);
        //2.遍历修改
        foreach ($children as $val) {
            //下级的新级别
            $next_new_level = $val[$this->level] + $differ_level;
            //执行更新
            $this->CI->db->set($this->level, $next_new_level);
            $this->CI->db->where($this->id, $val[$this->id]);
            $this->CI->db->update($table);
        }
        //修改当前分类等级及数据
        $post[$this->level] = $current_new_level;
        $this->CI->db->where($this->id, $id);
        $bool = $this->CI->db->update($table, $post);

        return $bool;
    }

    /**
     * 删除
     *
     * @param array $data 数据源
     * @param string $table 表名
     * @param string $id 标识
     * @return bool
     */
    public function del($data = array(), $table = '', $id = '')
    {
        if (empty($id)) {
            return FALSE;
        }
        //获取当前所有下级分类id，包括当前分类
        $children_id = $this->get_children($data, $id, TRUE, 'id');
        if (empty($children_id)) {
            return FALSE;
        }
        $this->CI->db->where_in($this->id, $children_id);
        $this->CI->db->delete($table);
        $rows = $this->CI->db->affected_rows();

        return $rows;
    }

}