<?php
namespace app\index\controller;

class Index
{
    // 前台
    // 教师模块
    public function index()
    {
        return view();
    }
    // 登录
    public function login(){return view();}
    // 注册
    public function regist(){return view();}

    // 1.申请页面
    public function apply(){return view();}
    // 2.已申请页面
    public function applying(){return view();}
    // 3.报销结果页面
    public function applied(){return view();}
    // 4.个人信息页面
    public function profile(){return view();}

    // 后台
    public function admin_index(){return view();}
    public function admin_form_detail(){return view();}
    public function admin_profile(){return view();}
    public function admin_applied(){return view();}

}
