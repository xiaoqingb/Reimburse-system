<?php
namespace app\index\controller;
use think\Controller;

use app\index\model\Upload as UploadModel;
use  app\index\model\Form as FormModel;
class Upload extends Controller
{
    public function uploadImg(){
        $file = request()->file('file');
        $timeStamp = input('post.timeStamp');
        $filename = input('post.filename');
        if(empty($file)){
            $result["code"] = "0001";
            $result["msg"] = "文件不存在";
        }else{
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public/uploads/'.date(("Ymd")),'');
            if($info){
                // 成功上传后 获取上传信息
                $name_path =str_replace('\\',"/",$info->getSavename());
                $result["code"] = '0000';
                $result["msg"] = '上传成功';
                $upload = new UploadModel();
                $formId = FormModel::where('time_stamp',$timeStamp)->find()->id;
                $upload -> form_id = $formId;
                $upload -> name = $filename;
                $upload -> url =  '/uploads/'.date(("Ymd")).'/'.$info->getSavename();
                $result = $upload -> save();
                return json_encode(
                    [
                        'code' => '0000',
                        'msg' => '上传成功'
                    ]
                );
            }else{
                return json_encode(
                    [
                        'code' => '0001',
                        'msg' => '上传图片'.$filename.'失败'
                    ]
                );
            }
        }

    }
    
}