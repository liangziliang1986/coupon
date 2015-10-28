<?php
return array(
	//'配置项'=>'配置值'
    //模板解析设置
    'TMPL_PARSE_STRING'     => array(
        '__PUBLIC__'      => '/Application/Manage/Resource',
        '__STATIC__'      => '/Application/Manage/Resource/static',
        '__PROUI__'      => '/Application/Manage/Resource/Proui',
        '__IMG__'        => '/Application/Manage/Resource/img',
    ),
    //增加自定义的config文件
    'LOAD_EXT_CONFIG' => 'manage',
);