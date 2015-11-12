<?php
    
    

    $arr = array(
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
    );
    
    return $arr;