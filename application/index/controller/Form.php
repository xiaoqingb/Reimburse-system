<?php
namespace app\index\controller;
use app\index\model\Assets;
use app\index\model\BusinessTravel;
use app\index\model\result;
use app\index\model\TrafficDetail;
use think\Controller;

use app\index\model\Form as FormModel;
use app\index\model\Upload as UploadModel;
use think\Session;

class Form extends Controller
{
    // 前台
    public function addForm(){
        // 日期
        $date = input('post.date');
        // 类型
        $type = input('post.type');
        // 时间戳
        $timeStamp = input('post.timeStamp');
        // 各项总花费
        $expense = input('post.expense');
        $expense = json_decode($expense);
        $emptyNum = 0;
        /*判断是否金额为空*/
        foreach ($expense as $value){
            if($value === ''){
                $emptyNum++;
            }
        }
        if ($emptyNum === 9){
            return json_encode([
                'code' =>'0001',
                'msg' => '您并没有填写金额'
            ]);
        }

        // 填入表单的主要数据
        $form = new FormModel();
        $form->user_id = Session::get('id');
        $form->date = $date;
        $form->type = $type;
        $form->name = '王老师';
        $form-> next_manage= '0';
        $form->time_stamp = $timeStamp;
        $form->office = $expense[0];
        $form->entertain = $expense[1];
        $form->meeting = $expense[2];
        $form->train = $expense[3];
        $form->activity = $expense[4];
        $form->competition = $expense[5];
        $form->material = $expense[6];
        $form->travel  = $expense[7];
        $form->assets  = $expense[8];
        $form->next_manager = 1;
        $form->save();

        // 拿到表单的id
        $formId = $form->where('time_stamp',$timeStamp)->find()->id;
        $assetsArr = input('post.assets');
        $assetsArr = json_decode($assetsArr);
        foreach ($assetsArr as $assetsItem){
            $emptyNum= 0;
            // 滤除空行数据
            foreach ($assetsItem as $value){
                if($value === ''){
                    $emptyNum++;
                }
            }
            if ($emptyNum >= 18){
                continue;
            }
            $assets = new Assets();
            $assets->form_id = $formId;
            $assets->assets_name = $assetsItem->value_0;
            $assets->type = $assetsItem->value_1;
            $assets->specification = $assetsItem->value_2;
            $assets->brand= $assetsItem->value_3;
            $assets->price = $assetsItem->value_4;
            $assets->num= $assetsItem->value_5;
            $assets->money= $assetsItem->value_6;
            $assets->subject= $assetsItem->value_7;
            $assets->source= $assetsItem->value_8;
            $assets->use_direction= $assetsItem->value_9;
            $assets->storage_location = $assetsItem->value_10;
            $assets->supplier = $assetsItem->value_11;
            $assets->manufacturer = $assetsItem->value_12;
            $assets->buy_time  = $assetsItem->value_13;
            $assets->invoice_num  = $assetsItem->value_14;
            $assets->contract_num = $assetsItem->value_15;
            $assets->purchase_order = $assetsItem->value_16;
            $assets->puerchase_num  = $assetsItem->value_17;
            $assets->save();
        }
        $resultData = input('post.businessTravel');
        $resultData = json_decode($resultData);
        // 合计不为零才要录入
        if($resultData->basic->header[7] !== '0'){
            $businessTravel = new BusinessTravel();
            $header = $resultData->basic->header;
            $body = $resultData->basic->body;
            $detail = $resultData->detail;
            $businessTravel->form_id = $formId;

            // 插入表头数据
            $businessTravel->name = $header[0];
            $businessTravel->level = $header[1];
            $businessTravel->reason = $header[2];
            $businessTravel->begin_time  = $header[3];
            $businessTravel->end_time = $header[4];
            $businessTravel->begin_place = $header[5];
            $businessTravel->arrive_place = $header[6];
            $businessTravel->sum  = $header[7];

            // 插入表体数据
            $businessTravel->train = $body[0];
            $businessTravel->plane = $body[1];
            $businessTravel->bus = $body[2];
            $businessTravel->other_tools = $body[3];
            $businessTravel->accommodation_standard = $body[4];
            $businessTravel->accommodation_expense = $body[5];
            $businessTravel->food_standard = $body[6];
            $businessTravel->food_expense = $body[7];
            $businessTravel->traffic_standard = $body[8];
            $businessTravel->traffic_expend = $body[9];
            $businessTravel->train_expense = $body[10];
            $businessTravel->other = $body[11];
            $businessTravel->save();
            foreach ($detail as $traficDetailItem){
                $traficDetail = new TrafficDetail();
                if($traficDetailItem->value_0 !== ''){
                    $traficDetail->form_id = $formId;
                    $traficDetail->name = $traficDetailItem->value_0;
                    $traficDetail->time = $traficDetailItem->value_1;
                    $traficDetail->begin_place = $traficDetailItem->value_2;
                    $traficDetail->arrive_place = $traficDetailItem->value_3;
                    $traficDetail->kilometre = $traficDetailItem->value_4;
                    $traficDetail->reason = $traficDetailItem->value_5;
                    $traficDetail->save();
                }
            }

        }
        return(json_encode([
            'code' => '0000',
            'msg' => '添加成功'
        ]));

    }

    // 获取用户自己申请中的表单
    public function getUserFormList(){
        $page = input("get.page","1");
        //step 1.格式化数据
        $page=(int) $page;

        //step 2. 计算分页
        $form = new FormModel();
        $pageSize=3;
        $totalCount=$form->where('user_id',Session::get('id'))->where('status',0)->count();
        $pageCount=ceil($totalCount/$pageSize);

        //step 3. 获取分页数据
        $result=$form->where('user_id',Session::get('id'))->where('status',0)->page($page,$pageSize)->select();
        if(!$result){
            return json_encode(
                [
                    'code'=>'0002',
                    'msg'=>'获取表单时发生了一些问题'
                ]
            );
        }

        //step 4.获取每一条问题到底是谁发起的
        $formList=[];
        foreach($result as $formItem){
            array_push($formList,[
                "id"=>$formItem->id,
                "date"=>$formItem->date,
                "name"=>$formItem->name,
                "type"=>$formItem->type,
                "office"=>$formItem->office,
                "entertain"=>$formItem->entertain,
                "meeting"=>$formItem->meeting,
                "train"=>$formItem->train,
                "activity"=>$formItem->activity,
                "competition"=>$formItem->competition,
                "material"=>$formItem->material,
                "travel"=>$formItem->travel,
                "assets"=>$formItem->assets,
                "status"=>$formItem->status,
            ]);
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'pageCount' => $pageCount,
                    'forms' => $formList
                ]
            ]
        );
    }

    // 获取用户已被审核的表单
    public function getUserFormResult(){
        $page = input("get.page","1");
        //step 1.格式化数据
        $page=(int) $page;
        //step 2. 计算分页
        $form = new FormModel();
        $pageSize=3;
        $totalCount=$form->where('user_id',Session::get('id'))->where('status','>',0)->count();
        $pageCount=ceil($totalCount/$pageSize);

        //step 3. 获取分页数据
        $result=$form->where('user_id',Session::get('id'))->where('status','>',0)->page($page,$pageSize)->select();
        if(!$result){
            return json_encode(
                [
                    'code'=>'0002',
                    'msg'=>'获取表单时发生了一些问题'
                ]
            );
        }

        //step 4.获取每一条问题到底是谁发起的
        $formList=[];
        foreach($result as $formItem){
            array_push($formList,[
                "id"=>$formItem->id,
                "date"=>$formItem->date,
                "name"=>$formItem->name,
                "type"=>$formItem->type,
                "office"=>$formItem->office,
                "entertain"=>$formItem->entertain,
                "meeting"=>$formItem->meeting,
                "train"=>$formItem->train,
                "activity"=>$formItem->activity,
                "competition"=>$formItem->competition,
                "material"=>$formItem->material,
                "travel"=>$formItem->travel,
                "assets"=>$formItem->assets,
                "status"=>$formItem->status,
                "reason"=>$formItem->reason,
            ]);
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'pageCount' => $pageCount,
                    'forms' => $formList
                ]
            ]
        );
    }

    // 后台
    // 获取需要审核的表单
    public function getFormList(){
        $page = input("get.page","1");
        $level = Session::get('job')  === 4 ? 1 :2;
        //step 1.格式化数据
        $page=(int) $page;

        //step 2. 计算分页
        $form = new FormModel();
        $pageSize=3;
        $totalCount=$form->where('status',0)->where('process',$level)->count();
        $pageCount=ceil($totalCount/$pageSize);

        //step 3. 获取分页数据
        $result=$form->where('status',0)->where('process',$level)->page($page,$pageSize)->select();
        if(!$result){
            return json_encode(
                [
                    'code'=>'0002',
                    'msg'=>'获取表单时发生了一些问题'
                ]
            );
        }

        //step 4.获取每一条问题到底是谁发起的
        $formList=[];
        foreach($result as $formItem){
            array_push($formList,[
                "id"=>$formItem->id,
                "date"=>$formItem->date,
                "name"=>$formItem->name,
                "type"=>$formItem->type,
                "office"=>$formItem->office,
                "entertain"=>$formItem->entertain,
                "meeting"=>$formItem->meeting,
                "train"=>$formItem->train,
                "activity"=>$formItem->activity,
                "competition"=>$formItem->competition,
                "material"=>$formItem->material,
                "travel"=>$formItem->travel,
                "assets"=>$formItem->assets,
                "status"=>$formItem->status,
            ]);
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'pageCount' => $pageCount,
                    'forms' => $formList
                ]
            ]
        );
    }

    // 获取已审核的表单
    public function getFormResult(){
        $page = input("get.page","1");
        //step 1.格式化数据
        $page=(int) $page;

        //step 2. 计算分页
        $form = new FormModel();
        $pageSize=3;
        $totalCount=$form->where('status','>',0)->count();
        $pageCount=ceil($totalCount/$pageSize);

        //step 3. 获取分页数据
        $result=$form->where('status','>',0)->page($page,$pageSize)->select();
        if(!$result){
            return json_encode(
                [
                    'code'=>'0002',
                    'msg'=>'获取表单时发生了一些问题'
                ]
            );
        }

        //step 4.获取每一条问题到底是谁发起的
        $formList=[];
        foreach($result as $formItem){
            array_push($formList,[
                "id"=>$formItem->id,
                "date"=>$formItem->date,
                "name"=>$formItem->name,
                "type"=>$formItem->type,
                "office"=>$formItem->office,
                "entertain"=>$formItem->entertain,
                "meeting"=>$formItem->meeting,
                "train"=>$formItem->train,
                "activity"=>$formItem->activity,
                "competition"=>$formItem->competition,
                "material"=>$formItem->material,
                "travel"=>$formItem->travel,
                "assets"=>$formItem->assets,
                "status"=>$formItem->status,
                "reason"=>$formItem->reason
            ]);
        }
        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'pageCount' => $pageCount,
                    'forms' => $formList
                ]
            ]
        );
    }

    // 获取表单详情
    public function getFormDetail(){
        $id = input('get.id');
        $form = new FormModel();
        $formItem = $form->where('id', $id)->find();
        $basicData=[
            "id"=>$formItem->id,
            "date"=>$formItem->date,
            "name"=>$formItem->name,
            "type"=>$formItem->type,
            "office"=>$formItem->office,
            "entertain"=>$formItem->entertain,
            "meeting"=>$formItem->meeting,
            "train"=>$formItem->train,
            "activity"=>$formItem->activity,
            "competition"=>$formItem->competition,
            "material"=>$formItem->material,
            "travel"=>$formItem->travel,
            "assets"=>$formItem->assets,
            "status"=>$formItem->status,
        ];

        return json_encode(
            [
                'code' => '0000',
                'msg' => '获取成功',
                "data" => [
                    'basicData' => $basicData
                ]
            ]
        );
    }

    // 获取差旅费
    public  function getFormBusinessTravel(){
        $formId = input('get.id');
        $result = new BusinessTravel();
        $result = $result->where('form_id', $formId)->find();
        if(!$result){
            return json_encode([
                'code'=> '0001',
                'msg' => '该表单没有填写差旅费详情'
            ]);
        }
        $basicData = [
            'name'=>$result->name,
            'level'=>$result->level,
            'reason'=>$result->reason,
            'begin_time'=>$result->begin_time ,
            'end_time'=>$result->end_time,
            'begin_place'=>$result->begin_place,
            'arrive_place'=>$result->arrive_place,
            'train'=>$result->sum ,
            'plane'=>$result->sum ,
            'bus'=>$result->sum ,
            'other_tools'=>$result->sum ,
            'accommodation_standard'=>$result->sum ,
            'accommodation_expense'=>$result->sum ,
            'food_standard'=>$result->sum ,
            'food_expense'=>$result->sum ,
            'traffic_standard'=>$result->sum ,
            'traffic_expend'=>$result->sum ,
            'train_expense'=>$result->train_expense ,
            'other'=>$result->sum ,
            'sum'=>$result->sum
        ];
        $trafic = new TrafficDetail();
        $traficDetail = $trafic->where('form_id',$formId)->select();
        if (!$traficDetail){
            return json_encode([
                'code'=> '0000',
                'msg' => '获取成功',
                'data' => [
                    'basicData'=> $basicData
                ]
            ]);
        }
        $detailList = [];
        foreach ($traficDetail as $traficDetailItem) {
            array_push($detailList,[
                'form_id'=>$traficDetailItem->form_id,
                'name'=>$traficDetailItem->name,
                'time'=>$traficDetailItem->time,
                'begin_place'=>$traficDetailItem->begin_place,
                'arrive_place'=>$traficDetailItem->arrive_place,
                'kilometre'=>$traficDetailItem->kilometre,
                'reason'=>$traficDetailItem->reason,
            ]);
        }
        return json_encode([
            'code'=> '0000',
            'msg' => '获取成功',
            'data' => [
                'basicData'=> $basicData,
                'detailList'=>$detailList
            ]
        ]);
    }

    // 获取固定资产费详情
    public  function getFormAssets(){
        $formId = input('get.id');
        $Assets = new Assets();
        $assetsList = $Assets->where('form_id', $formId)->select();
        if(!$assetsList){
            return json_encode([
                'code'=> '0001',
                'msg' => '该表单没有填写固定资产费详情'
            ]);
        }
        $data= [];
        foreach($assetsList as $assetsItem){
            array_push($data,[
                "assets_name"=>$assetsItem->assets_name,
                "type"=>$assetsItem->type,
                "specification"=>$assetsItem->specification,
                "brand"=>$assetsItem->brand,
                "price"=>$assetsItem->price,
                "num"=>$assetsItem->num,
                "money"=>$assetsItem->money,
                "subject"=>$assetsItem->subject,
                "source"=>$assetsItem->source,
                "use_direction"=>$assetsItem->use_direction,
                "storage_location"=>$assetsItem->storage_location,
                "supplier"=>$assetsItem->supplier,
                "manufacturer"=>$assetsItem->manufacturer,
                "buy_time"=>$assetsItem->buy_time,
                "invoice_num"=>$assetsItem->invoice_num,
                "contract_num"=>$assetsItem->contract_num,
                "purchase_order"=>$assetsItem->purchase_order,
                "puerchase_num"=>$assetsItem->puerchase_num
            ]);
        }
        return json_encode([
            'code'=> '0000',
            'msg' => '获取成功',
            'data' => $data
        ]);
    }

    // 获取图片
    public function getFormImages(){
        $formId = input('get.id');
        $result = UploadModel::where('form_id',$formId)->select();
        if(!$result){
            return json_encode([
                'code'=> '0001',
                'msg' => '该表单没有上传图片'
            ]);
        }
        $images = [];
        foreach ($result as $image){
            array_push($images,[
                'name'=> $image->name,
                'imgUrl' =>$image->url
            ]);
        }
        return json_encode([
             'code'=> '0000',
             'msg' => '获取图片列表成功',
             'data'=>$images
        ]);
    }

    // 不批准
    public function rejectForm(){
        $formId = input('post.id');
        $reason = input('post.reason');
        $form = new FormModel();
        $result = $form->where('id',$formId)->update(['status' => '2', 'reason'=>$reason]);
        // if($result){
        //     return json_encode([
        //         'code'=>'0001',
        //         'msg' =>'操作失败'
        //     ]);
        // }
        return json_encode([
            'code'=>'0000',
            'msg' =>'操作成功'
        ]);
    }

    // 批准
    public function approveForm(){
        $formId = input('post.id');
        $form = new FormModel();
        $result = $form->where('id',$formId)->find();
        $process = $result->process;
        if($process === 1){
            $form->where('id', $formId)->update(['process'=>2]);
        }else{
            $form->where('id', $formId)->update(['status'=>1]);
        }

        return json_encode([
            'code'=>'0000',
            'msg' =>'操作成功'
        ]);
    }

    // 撤销表单
    public function revokeForm(){
        $formId = input('post.id');
        $form = new FormModel();
        $result = $form->where('id',$formId)->delete();
        $business = new BusinessTravel();
        $result = $business->where('form_id',$formId)->delete();
        $images = new UploadModel();
        $result = $images->where('form_id',$formId)->delete();
        $trafficDetail = new TrafficDetail();
        $result = $trafficDetail->where('form_id',$formId)->delete();
        $assets = new Assets();
        $result = $assets->where('form_id',$formId)->delete();

        return json_encode([
            'code'=>'0000',
            'msg' =>'撤销成功'
        ]);
    }

}