<?php
return array(
	//'配置项'=>'配置值'
    // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    'URL_MODEL'             =>  1,

    //设置模板
    'LAYOUT_ON'=>true,

    //设置模板名称
    'LAYOUT_NAME'=>'layout',

    // 默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR'     =>  '/Home/View/Mobile/Base/dispatch_jump_error.tpl', 

    // 默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  '/Home/View/Mobile/Base/dispatch_jump_success.tpl',

    //'设置默认主题
    // 'DEFAULT_THEME' => 'Computer',

    // 默认false 表示URL区分大小写, true则表示不区分大小写
    'URL_CASE_INSENSITIVE'  => true,

    //设置产生错误时，弹出的默认页面，如（模板不存在等）,设置他可以不用写空操作
    // 'TMPL_EXCEPTION_FILE' =>'./Application/Home/View/Computer/Public/404.html',

    //默认访问的模块
    'DEFAULT_MODULE'       =>    'Home',

    //允许访问的模块
    'MODULE_ALLOW_LIST'    =>    array('Home','Manage',),

    //DEFAULT_MODULE和MODULE_ALLOW_LIST一起才能设置url中的Home省略
    

    

    //增加自定义函数文件
    'LOAD_EXT_FILE' => 'replace,public',

     //增加自定义的config文件
    'LOAD_EXT_CONFIG' => 'db',
);