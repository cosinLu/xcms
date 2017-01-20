<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2016/8/26
 * Time: 19:43
 */
class Index extends CI_Controller
{
    protected $sys_session;
    protected $username;
    protected $password;

    public function __construct()
    {
        parent::__construct();
        $this->username = $this->input->post('username');
        $this->password = $this->input->post('password');
        $this->pre_url = urldecode($this->input->get('url'));
        $this->set_url();
        $this->load->model('index_model', 'index');
        $this->load->library('form_validation');
        $this->load->library('category');
    }

    //设置url
    function set_url()
    {
        $url['login_url'] = site_url('index/login?url=' . urlencode($this->pre_url));
        $url['code'] = site_url('index/code');
        $this->load->vars($url);
    }

    public function index()
    {
        $this->load->view('index/index.html');
    }

    //验证码
    public function code()
    {
        $config = array(
            'width' => 80,
            'height' => 30,
            'font_size' => 16,
            'code_len' => 4
        );
        $this->load->library('code', $config);
        $this->code->show();
    }

    //登录
    public function login()
    {
        $code = strtoupper($this->input->post('code'));
        //设置表单验证规则
        $this->form_validation->set_rules('username', '用户名', 'required|callback_check_username|callback_check_col_auth');
        $this->form_validation->set_rules('password', '密码', 'required|callback_check_password');
        $this->form_validation->set_rules('code', '验证码', 'required|callback_check_code');
        //执行表单验证
        $bool = $this->form_validation->run();
        if (!$bool) {
            $this->load->view('index/index.html');
        } else {
            //登录成功
            $sys_session['sys_session'] = $user_info = $this->index->user_info();
            //添加登录日志
            $this->index->insert_login_log($user_info['user_id']);
            $this->session->set_userdata($sys_session);
            if ($this->pre_url == '') {
                //定义登录成功后跳转url
                $this->load->library('sys_auth', array('user_info' => $user_info));
                $sys_col = $this->sys_auth->sys_col();
                $url = $this->category->children_url($sys_col);
                redirect($url[0]);
            } else {
                redirect($this->pre_url);
            }
        }
    }

    //验证验证码
    public function check_code()
    {
        $code = strtoupper($this->input->post('code'));
        if ($code != $this->session->sys_code && $code != '') {
            $this->form_validation->set_message('check_code', '{field} 输入错误。');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    //验证用户名
    public function check_username()
    {
        if ($this->username != '') {
            $rows = $this->index->check_username();
            if ($rows == 0) {
                $this->form_validation->set_message('check_username', '{field} 不存在。');
                return FALSE;
            } else {
                $userinfo = $this->index->user_info();
                if ($userinfo['status'] == 'forzen') {
                    $this->form_validation->set_message('check_username', '{field} 被冻结。');
                    return FALSE;
                } else {
                    return TRUE;
                }
                return TRUE;
            }
        }
    }

    //验证密码
    public function check_password()
    {
        $userinfo = $this->index->user_info();
        if (empty($userinfo) && $this->password != '') {
            $this->form_validation->set_message('check_password', '{field} 输入错误。');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    //验证系统栏目权限
    public function check_col_auth()
    {
        $user_info = $this->index->user_info();
        if (!empty($user_info)) {
            $this->load->library('sys_auth', array('user_info' => $user_info));
            $sys_col = $this->sys_auth->sys_col();
            $frist_sys_col = array();
            foreach ($sys_col as $val) {
                if ($val['level'] == 1) {
                    $frist_sys_col[] = $val;
                }
            }
            if (empty($frist_sys_col)) {
                $this->form_validation->set_message('check_col_auth', '{field} 没有任何权限。');
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            $this->form_validation->set_message('check_col_auth', '{field} 没有任何权限。');
            return FALSE;
        }
    }

    //登出
    public function logout()
    {
        //销毁session
        $this->session->unset_userdata('sys_session');
        redirect(site_url());
    }
}