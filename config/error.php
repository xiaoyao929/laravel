<?php
/**
 * 错误码
 */
return [
    'error100'=>[
    ],
    'error200'=>[
        '20001'=> '用户不存在',
        '20008'=> '用户未登陆或登陆失效', //重要改写请慎重
    ],
    'error300'=>[
    ],
    'error400'=>[
        '40001'=> '缺少必要参数！',
        '40002'=> '填写的券号有错误！',
        '40003'=> '填写的券号中存在相同的券类型！',
        '40004'=> '开始券号不能小于结束券号',
        '40005'=> '填写的券号不存在本仓库',
        '40006'=> '用户不存在或已经停用',
        '40007'=> '缺少实收合计',
    ],
    'error500'=>[
        '50001'=> '数据库存入失败',
    ],
    'error600'=>[
        '60001'=> '拒绝访问，跨站验证失败！',
    ],
    'error700'=>[],
    'error800'=>[],
    'error900'=>[],
];
