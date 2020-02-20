<?php
namespace app\index\controller;
use think\Controller;

use app\index\model\Upload as UploadModel;

class Upload extends Controller
{
    public function uploadImg(){
        // $fn = isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false ;
        // if ($fn) {
        //     file_put_contents(
        //         ROOT_PATH . 'public' . DS . 'uploads/'.$fn,
        //         file_get_contents('php://input')
        //     );
        //     // echo "http://www.zhangxinxu.com/study/201109/uploads/$fn";
        //     return json_encode([
        //         'code' => '0000',
        //         'msg' => 'http://www.zhangxinxu.com/study/201109/uploads/$fn"'
        //     ]);
        // }
        // return '0';
        $file = request()->file('file');
        // foreach($files as $file){
        // }
        if(empty($file)){
            $result["code"] = "0001";
            $result["msg"] = "文件不存在";
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' .DS. 'uploads/editor','');
            if($info){
                // $name_path =$info->getFilename();
                //成功上传后 获取上传信息
                $name_path =str_replace('\\',"/",$info->getSavename());
                $result["code"] = '0000';
                $result["msg"] = '上传成功';
                $result['data'] = "uploads/editor/".$name_path;
            }else{
                // 上传失败获取错误信息
                $result["code"] = "0002";
                $result["msg"] = "上传失败";
            }
        }
        exit(json_encode($result));
    }
    
}