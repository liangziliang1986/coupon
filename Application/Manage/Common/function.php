<?php
function compress_html ($string) { 
    $string = str_replace("\r\n", '', $string); //清除换行符 
    $string = str_replace("\n", '', $string); //清除换行符 
    $string = str_replace("\t", '', $string); //清除制表符 
    $pattern = array ( 
        "/> *([^ ]*) *</", //去掉注释标记 
        "/[\s]+/", 
        "/<!--[^!]*-->/", 
        "/\" /", 
        "/ \"/", 
        "'/\*[^*]*\*/'" 
    ); 
    $replace = array ( 
        ">\\1<", 
        " ", 
        "", 
        "\"", 
        "\"", 
        "" 
    ); 
    return preg_replace($pattern, $replace, $string); 
} 

function get_left_nav (){
    $arr = array();
    
    $is_super = M('User')->where(array('id'=>$_SESSION['user']['id']))->getField('is_super');
    switch ($is_super) {
        case '2':
            //超级管理员,最高权限
            $arr = array(
                    array(
                        'name'  => '商户管理',
                        'icon'  => 'gi gi-shopping_cart',
                        'sub'   => array(
                            array(
                                'name'  => '增加商户',
                                'en_name'  => '增加商户',
                                'url'   => '/manage/merchant/verify',
                                'class'  => 'ajaxLink',
                            ),
                            array(
                                'name'  => '商户列表',
                                'en_name'  => '商户列表',
                                'url'   => '/manage/merchant/index',
                                'class'  => 'ajaxLink',
                            ),
                        ),
                    ),
                    array(
                        'name'  => '卡券管理',
                        'icon'  => 'gi gi-shopping_cart',
                        'sub'   => array(
                            array(
                                'name'  => '创建卡券',
                                'en_name'  => '创建卡券',
                                'url'   => '/manage/coupon/createList',
                                'class'  => 'ajaxLink',
                            ),
                            array(
                                'name'  => '卡券列表',
                                'en_name'  => '卡券列表',
                                'url'   => '/manage/coupon/index',
                                'class'  => 'ajaxLink',
                            ),
                            
                        ),
                    ),
                );
            
            break;
        case '1':
            //业务员,权限中等
            $arr = array(
                array(
                    'name'  => '商户管理',
                    'icon'  => 'gi gi-shopping_cart',
                    'sub'   => array(
                        array(
                            'name'  => '商户列表',
                            'en_name'  => '商户列表',
                            'url'   => '/manage/merchant/index',
                            'class'  => 'ajaxLink',
                        ),
                    )
                ),
            );
            break;
        
        default:
            //普通商户,最低权限
            $arr = array(
                array(
                    'name'  => '商户管理',
                    'icon'  => 'gi gi-shopping_cart',
                    'sub'   => array(
                        array(
                           'name'  => '商户信息',
                           'en_name'  => '商户信息',
                           'url'   => '/manage/merchant/info',
                           'class'  => 'ajaxLink',
                           'icon'  => 'gi gi-stopwatch'
                        ),
                        array(
                           'name'  => '发起授权',
                           'en_name'  => 'auth',
                           'url'   => '/manage/auth/index',
                           'class'  => 'ajaxLink',
                           'icon'  => 'gi gi-stopwatch'
                        ),
                    )
                ),
                array(
                        'name'  => '卡券管理',
                        'icon'  => 'gi gi-shopping_cart',
                        'sub'   => array(
                            array(
                                'name'  => '卡券列表',
                                'en_name'  => '卡券列表',
                                'url'   => '/manage/coupon/myCoupon',
                                'class'  => 'ajaxLink',
                            ),
                            array(
                                'name'  => '卡券核销',
                                'en_name'  => '卡券核销',
                                'url'   => '/manage/coupon/consume',
                                'class'  => 'ajaxLink',
                            ),
                        ),
                    ),
            );
            break;
    }
    return $arr;
}