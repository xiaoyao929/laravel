<?php

namespace App\Dao;

use Illuminate\Support\Facades\{Config};

class SupportDao
{
    /**
     * 新增券种
     * @param   array  $data  发往支撑数据
     * @return  mixed
     */
    public static function createType($data)
    {
        $send = [
            'ActivityCreateReq'=> [
                'TransactionID'     => getSysSeq(),
                'ISSPID'            => $data['node_id'],
                'SystemID'          => Config::get('support.apiConfig.issSysTemId'),
//                'RelationID'        => $data['node_id'],        //额外的
                'ActivityInfo'      => [
                    'ActivityName'     => $data['detail_name'],
                    'ActivityShortName'=> $data['name'],
                    'UseRangeID'       => $data['group_id'],    //'0000008210', 终端组
                    'ActivityType'     => '9',                  //--激活标志 0-激活 1-条码激活辅助码未激活 9-未激活
                    'BeginTime'        => date('YmdHis'),
                    'EndTime'          => date('YmdHis',time()+ 3600*24*365*10),
                    'PrintTimes'       => '1'                   //打印联数，不用但仍需同步过去
                ],
                'VerifyMode'=> [
                    'UseTimesLimit'         => '1',
                    'UseAmtLimit'           => '0'
                ],
                'GoodsInfo'=> [
                    'GoodsName'             => $data['detail_name'],
                    'GoodsShortName'        => $data['name'],
                    'GoodsType'             => '1',
//                    'SalePrice'             => $data['price'],
//                    'CustomNo'              => $data['custom_no']       //自定义编号
                ],
                'DefaultParam'  => [
                    'SendClass'     => 'IMG',
                    'Messages'      => [
                            'Sms'       => [
                                'Text'      => 'GC',
                            ],
                            'Mms'           => [
                                'Subject'   => 'GC',
                                'Text'      => 'GC'
                            ]
                    ],
                    'PrintText'         => $data['name'],
                    'UseTimes'          => '1',
                    'UseAmt'            => $data['price']/100,
                    'PasswordTryTimes'  => '0',
                    'PasswordType'      => ''
                ]
            ]
        ];
        $result = SupportApiDao::couponType($send);
        //正常后去支撑配置活动
        if(isset($result['Status']) && $result['Status']['StatusCode'] == '0000' && isset($result['ActivityID'])){
            $activityConfig = [
                    'BatchMakeCardConfigReq'    => [
                            'TransactionID'     => getSysSeq(),
                            'ISSPID'            => $data['node_id'],
                            'PlatformID'        => Config::get('support.apiConfig.issSysTemId'),
                            'MakeCardInfo'      => [
                                'BatchNo'       => $result['ActivityID'],
                                'RecvFlag'      => 1,
                                'RecvAddr'      => Config::get('support.apiConfig.ftp_url'),
                                'ImgSize'       => 1,
                                'ImgNameRule'   => '%foreign_seq%',
                                'ActiveFlag'    => '9',
                                'EncryptType'   => '0',
                                'EncryptAppend' => ''
                            ]
                    ]
            ];
            $configResult = SupportApiDao::makeCoupon($activityConfig,'activity_config');
            if(!isset($configResult['Status']['StatusCode'])){
                $result = [
                    'Status'    => [
                        'StatusCode'    => '-1',
                        'StatusText'    => '接口返回异常'
                    ]
                ];
            }
            if($configResult['Status']['StatusCode'] != '0000'){
                //配置活动失败
                $result = $configResult;
            }

        }
        return $result;

    }
    /**
     * 制券申请
     * @param   array  $data  发往支撑数据
     * @return  mixed
     */
    public static function applyMake($data)
    {
        $config     = Config::get('support.apiConfig');
        $send = array(
                "GenerateReq" => array(
                        "TransactionID" => 	getSysSeq().'--'.$data['id'],
                        "StartSeq"	=>   str_pad($data['start_flow_no'],10,"0",STR_PAD_LEFT),
                        "Count"		=>	$data['amount'],
                        // "StartDate" =>  date('YmdHis',strtotime($data['begin_time'])),
                        //改成立即开始活动
                        "StartDate" =>	date('YmdHis'),
                        "EndDate"	=>	date('YmdHis',strtotime($data['end_time'])),
                        "Param"=> array(
                                "NodeID" => $data['node_id'],
                                "BatchNo"=> $data['activity_id'],       //券种类型里的活动号
                                "PlatFormID" => Config::get('support.apiConfig.issSysTemId'),
                                "Times" => '1',                         //验证次数
                                "Money" => empty($data['price'])?0:$data['price']/100,
                                 "NotifyUrl" => urlencode($config['make_back']),
                        ),

                ),

        );
        
        $result = SupportApiDao::makeCoupon($send,'make_url');
        return $result;

    }
    /**
     * 券作废
     * @param   array  $data  发往支撑数据
     * @return  mixed
     */
    public static function voidCoupon($data)
    {
        $send = [
            'RepealAllReq'     => [
                'TransactionID'     => getSysSeq(),
                'NodeID'            => $data['node_id'],
                'PlatFormID'        => Config::get('support.apiConfig.issSysTemId'),
            ],

        ];
        if(!is_array($data['item'])){
            return ['Status'=>['StatusCode'=>'-1','StatusText'=>'本地[item格式错误]']];
        }
        foreach ($data['item'] as $key => $value){
            $send['RepealAllReq']['Item'][] = [
                    'StartSeq'      => $value['start_flow_no'],
                    'Count'         => $value['amount']
            ];
        }
        $result = SupportApiDao::makeCoupon($send,'coupon_status');
        return $result;
    }
    /**
     * 券激活
     * @param   array  $data  发往支撑数据
     * @return  mixed
     */
    public static function activate($data)
    {
        $send = [
                'ActivateAllReq'     => [
                        'TransactionID'     => getSysSeq(),
                        'NodeID'            => $data['node_id'],
//                        'IPSrc'             => Config::get('support.apiConfig.issSysTemId'),
                        'PlatFormID'        => Config::get('support.apiConfig.issSysTemId'),
//                        'NewBeginUseTime'   => '',      //延时激活(暂时不用)
                ],

        ];

        if(!is_array($data['item'])){
            return ['Status'=>['StatusCode'=>'-1','StatusText'=>'本地[item格式错误]']];
        }
        foreach ($data['item'] as $key => $value){
            $send['ActivateAllReq']['Item'][] = [
                    'StartSeq'      => $value['start_flow_no'],
                    'Count'         => $value['amount']
            ];
        }
        $result = SupportApiDao::makeCoupon($send,'coupon_status');
        return $result;
    }



}