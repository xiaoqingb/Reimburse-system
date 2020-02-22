<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    '/'=>'index/index/index',
    'login'=>'index/index/login',
    'regist'=>'index/index/regist',
    'apply'=>'index/index/index',
    'applying'=>'index/index/applying',
    'applied'=>'index/index/applied',
    'profile'=>'index/index/profile',


    'admin'=> 'index/index/admin_index',
    'admin/form-detail' => 'index/index/admin_form_detail'
 ];
