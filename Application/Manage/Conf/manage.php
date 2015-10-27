<?php
    return array(
        'TEMPLATE' => array(
            'name'          => 'vsonter',
            'version'       => '1.2',
            'author'        => 'vsonter',
            'robots'        => 'noindex, nofollow',
            'title'         => '微时代卡券',
            'description'   => '微时代卡券管理',
            
            'header_navbar' => 'navbar-default',
            
            'header'        => '',
            
            'sidebar'       => 'sidebar-partial sidebar-visible-lg sidebar-no-animations',
            
            'footer'       => '',
            
            'main_style'    => '',
            
            'theme'         => '',
            
            'header_content'=> '',
            'active_page'   => basename($_SERVER['PHP_SELF'])
        ),

        
        'PRIMARY_NAV' => array(
            array(
                'name'  => '常规设置',
                'opt'   => '<a href="javascript:void(0)" data-toggle="tooltip" title="Quick Settings"><i class="gi gi-settings"></i></a>' .
                           '<a href="javascript:void(0)" data-toggle="tooltip" title="Create the most amazing pages with the widget kit!"><i class="gi gi-lightbulb"></i></a>',
                'url'   => 'header',
            ),
            array(
                'name'  => '表单组件',
                'en_name'  => 'form',
                'url'   => '/manage/index/test',
                'url'   => '/manage/form/index',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-stopwatch'
            ),
            array(
                'name'  => '授权',
                'en_name'  => 'auth',
                'url'   => '/manage/auth/index',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-stopwatch'
            ),
            array(
                'name'  => '弹窗',
                'en_name'  => 'modal',
                'url'   => '/manage/modal/index',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-cutlery'
            ),
            array(
                'name'  => '分页',
                'en_name'  => 'page',
                'url'   => '/manage/page/index',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-book'
            ),
            array(
                'name'  => '酒店',
                'en_name'  => 'hotail',
                'url'   => '/manage/index/test4',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-home'
            ),
            array(
                'name'  => '赌场',
                'en_name'  => 'gambling',
                'url'   => '/manage/index/test4',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-coins'
            ),
            array(
                'name'  => '交通',
                'en_name'  => 'traffic',
                'url'   => '/manage/index/test4',
                'class'  => 'ajaxLink',
                'icon'  => 'gi gi-road'
            ),
            array(
                'name'  => 'Design Kit',
                'opt'   => '<a href="javascript:void(0)" data-toggle="tooltip" title="Quick Settings"><i class="gi gi-settings"></i></a>',
                'url'   => 'header'
            ),
            
            array(
                'name'  => '插件',
                'icon'  => 'gi gi-shopping_cart',
                'sub'   => array(
                    array(
                        'name'  => 'uploadify',
                        'en_name'  => 'uploadify',
                        'url'   => '/manage/plugin/uploadifive',
                        'class'  => 'ajaxLink',
                    ),
                    array(
                        'name'  => 'UEditor',
                        'en_name'  => 'UEditor',
                        'url'   => '/manage/plugin/ueditor',
                        'class'  => 'ajaxLink',
                    ),
                    array(
                        'name'  => 'Validation',
                        'en_name'  => 'Validation',
                        'url'   => '/manage/plugin/test',
                        'class'  => 'ajaxLink',
                    ),
                    array(
                        'name'  => 'Wizard',
                        'url'   => 'page_forms_wizard.php'
                    )
                )
            ),
            array(
                'name'  => '其他',
                'icon'  => 'gi gi-bookmark',
                'sub'   => array(
                    array(
                        'name'  => '我的附近',
                        'url'   => 'page_icons_fontawesome.php'
                    ),
                    array(
                        'name'  => 'Glyphicons Pro',
                        'url'   => 'page_icons_glyphicons_pro.php'
                    )
                )
            ),

        ),
    );