<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2017/8/28
 * Time: 13:51
 */
class Uploads extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->set_url();
        $this->load->model('uploads_model');
    }

    //设置url
    public function set_url()
    {
        $url['get_list_url'] = site_url('uploads/get_list');
        $this->load->vars($url);
    }

    //文件列表
    public function get_list()
    {
        $page = $this->input->post('page');
        $key = $this->input->post('key');
        $data['list'] = $this->uploads_model->get_list($page, $key);
        foreach ($data['list'] as $key => $val) {
            $data['list'][$key]['thumb_rel_path'] = $val['is_image'] ? $val['raw_rel_path'] . config_item('my_thumb_marker') . $val['file_ext'] : '';
        }
        echo json_encode($data);
    }

    //云上传
    public function cloud()
    {
        $data['queueid'] = $this->input->get('queueid', TRUE);
        $data['multi'] = $this->input->get('multi', TRUE);
        $this->load->view('uploads/cloud.html', $data);
    }

    //本地上传
    public function location()
    {
        $this->load->view('uploads/location.html');
    }

    //上传图片
    public function uploads_image()
    {
        $this->load->view('uploads/uploads_image.html');
    }

    //执行上传并写入数据库
    public function do_upload()
    {
        $filename = $this->input->post('filename');
        $config['upload_path'] = standard_path(config_item('my_upload') . 'upload/' . date('Ymd', time()) . '/');
        $config['allowed_types'] = '*';
        $config['file_name'] = md5(uniqid(microtime(TRUE), TRUE));
        $this->load->library('upload', $config, 'my_upload');
        $this->my_upload->do_upload($filename);
        $data = $res = $this->my_upload->data();
        $data['id'] = $this->uploads_model->save($res);
        $data['input_name'] = $filename;
        $data['thumb_rel_path'] = '';
        //生成缩略图
        if ($data['is_image']) {
            $this->create_thumb($data['abs_path'], config_item('my_thumb_width'), config_item('my_thumb_height'));
            //拼接缩略图地址
            $data['thumb_rel_path'] = $data['raw_rel_path'] . config_item('my_thumb_marker') . $data['file_ext'];
        }
        //返回数据
        echo json_encode($data);
    }

    //创建缩略图
    public function create_thumb($path = '', $width = 120, $height = 120)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $path;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = $width;
        $config['height'] = $height;
        $config['create_thumb'] = TRUE;
        $config['thumb_marker'] = '_' . $config['width'] . '_' . $config['height'];
        $this->load->library('image_lib', $config);
        $this->image_lib->resize();
    }


}