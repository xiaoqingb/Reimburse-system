<?php
namespace app\index\controller;

use app\index\model\User as UserModel;
use think\Session;
use think\Cookie;
use think\Validate;

class User extends Auth
{
    // protected $beforeActionList = [
    //     'isAuthed' => ['only' => 'getName']
    // ];

    public function login()
    {
        $email = input('post.email', '');
        $password = input('post.password', '');

        //step 1.验证用户的输入
        $rule = [
            'email' => 'require|email',
            'password' => 'require',

        ];
        $msg = [
            'email.require' => '你忘记输入邮箱了哦~',
            'email.email' => '你的邮箱格式有误哦~',
            'password.require' => '你没有输入密码哦~QAQ',
        ];
        $data = [
            'password' => $password,
            'email' => $email,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }

        // step 2. 判断账号存不存在
        $user = new UserModel();
        $result = $user->where('email', $email)->find();
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => '账号或密码错误!'
                ]
            );
        }
        //step 3.验证账号密码正不正确
        $user = $user->where('email', $email)->where('password', md5($password))->find();
        
        if (!$user) {
            //密码错误
            return json_encode(
                [
                    'code' => '0002',
                    'msg' => '账号或密码错误!'
                ]
            );
        }

        //step 4.成功登录后将数据写入 session 和cookie
        Session::set('name', $user->name);
        Session::set('id', $user->id);
        Cookie::set('user', $user->id . "::" . $user->name, 7 * 24 * 60 * 60, '/');
        return json_encode(
            [
                'code' => '0000',
                'msg' => '登录成功'
            ]
        );
    }

    public function regist()
    {
        $job = input('post.job');
        $account = (int)input('post.account');
        $password = input('post.password');
        $name = input('post.name');
        $major = input('post.major', '');
        $phone = (int)input('post.phone');
        //step 1.验证用户的输入
        $rule = [
            'account' => 'require|between:99999,999999999999999',
            'password' => 'require',
            'name' => 'require',
            'phone' => 'require',
        ];
        $msg = [
            'account.require' => '帐号为在长度6-15之间的数字',
            'password.require' => '你没有输入密码哦~QAQ',
            'name.require' => '你没有输入姓名哦~QAQ',
            'phone.require' => '你没有输入手机哦~QAQ',
        ];
        $data = [
            'account' => $account,
            'password' => $password,
            'name' => $name,
            'phone' => $phone
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => $validate->getError()
                ]
            );
        }

        $user = new UserModel();
        //判断用户是否存在
        $result = $user->where('account', $account)->find();
        if ($result !== null) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => '该用户已存在'
                ]
            );
        }
        switch($job){
            case '教师':
                $job = 1;
                break;
            case '管理员':
                $job = 2;
                break;
        }
        //插入用户
        $user->account = $account;
        $user->password = md5($password);
        $user->name = $name;
        $user->job = $job;
        $user->phone = $phone;
        $user->major = $major;
        $result = $user->save();
        if (!$result) {
            return json_encode(
                [
                    'code' => '0001',
                    'msg' => '插入数据的时候发生问题'
                ]
            );
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '用户创建成功！'
            ]
        );
    }

    public function getName()
    {
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取用户名成功！',
                'data' => Session::get('name'),
            ]
        );
    }

    public function logout()
    {
        Session::clear();
        Cookie::delete('user');
        return json_encode(
            [
                'code' => '0000',
                'msg' => '登出成功!'
            ]
        );
    }
}
