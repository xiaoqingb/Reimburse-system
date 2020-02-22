<?php
namespace app\index\controller;
use app\index\model\Assets;
use app\index\model\BusinessTravel;
use app\index\model\TrafficDetail;
use think\Controller;

use app\index\model\Form as FormModel;

class Form extends Controller
{
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
        $form->date = $date;
        $form->type = $type;
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
        // $assets->form_id = $
        $businessTravelData = input('post.businessTravel');
        $businessTravelData = json_decode($businessTravelData);
        // 合计不为零才要录入
        if($businessTravelData->basic->header[7] !== '0'){
            $businessTravel = new BusinessTravel();
            $header = $businessTravelData->basic->header;
            $body = $businessTravelData->basic->body;
            $traficDetail = $businessTravelData->detail;
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
            foreach ($traficDetail as $traficDetailItem){
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

    public function getFormList(){
        $page = input("get.page","1");
        //step 1.格式化数据
        $page=(int) $page;

        //step 2. 计算分页
        $form = new FormModel();
        $pageSize=7;
        $totalCount=$form->count();
        $pageCount=ceil($totalCount/$pageSize);

        //step 3. 获取分页数据
        $result=$form->page($page,$pageSize)->select();
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
}