//sortable
/*var el = document.getElementById('items');
var sortable = Sortable.create(el);*/
var hide_home_view = false;
Sortable.create($("#user-tabs")[0], {
        animation: 150,});
//设置弹框语言
bootbox.setLocale('zh_CN');
window.localStorage.clear();
window.localStorage.sort = 'welcome ';

function tab (_this) {
    var text = $(_this).text();
    var dom = $(".sidebar-content a:contains(" + text + ")");
    var nav_dom = $("#user-tabs a:contains(" + text + ")");
    if (_this == $("#user-tabs .active")[0]) {
        return false;
    }
    if (nav_dom.length) {
        _this = nav_dom.parent()[0];
    }
    sortTab(text);
    createBreadCrumb(dom);
    clearNav(text);
    resetLeftNavActive(dom);
    openNav(text);
    setTimeout(function () {
        $(_this).addClass('active');
        $($(_this).find('a').attr('href')).addClass('active');
    },0);
    // return false;
};
function clearNav (text) {
    $("#user-tabs .active").removeClass('active');
    $("#user-tab-content .tab-pane.active").removeClass('active');
    if (checkTabExit(text)) {
        return false;
    } else {
        return true;
    }
};
function refresh (_this, url, data_tab, data, fn) {
    // var text = $(_this).text();
    var text = $("#sidebar a:contains(" + $(_this).text() + ")").attr('data-tab');
    var data_tab = data_tab || text;
    var u = url || getStorage(text + 'url') || getAlinkHref(text);
    // u = u == 'undefined' ? getAlinkHref(text) : u;
    setStorage(text + 'url', u);
    loadding($(_this).find('i'));
    $("#" + data_tab).html('');
    getTabContent(u, data_tab, function (response) {
            $("#" + data_tab).html(response);
            unloading($(_this).find('i'));
            $("#user-tab-content").trigger("initUploadifive", ["Hello","World!"]);
            if (fn) {
                fn();
            }
        }, data);
};
function loadding ($dom) {
    $dom.removeClass('gi gi-remove_2').addClass('fa fa-undo rotateInfi');
};
function unloading ($dom) {
    $dom.removeClass('fa fa-undo rotateInfi').addClass('gi gi-remove_2');
};
function sortTab (text) {
    var id = $("#sidebar a:contains(" + text + ")").attr('data-tab');
    var sort = window.localStorage.sort;
    if (sort.indexOf(id) != -1) {
        var sp = sort.split(id + ' ');
        window.localStorage.sort = sp.join('') + id + ' ';
    } else {
        window.localStorage.sort += id + ' ';
    }
};
function getPrevSort (id) {
    var sort = window.localStorage.sort;
    var sp = sort.split(id.substr(1) + ' ');
    var st = sp.join('');
    window.localStorage.sort = st;
    var tmp_arr = st.trim().split(' ');
    return tmp_arr[tmp_arr.length - 1];
};
function removeTab (_this) {
    var id = $(_this).parent().attr('href');
    $(_this).parents('li').remove();
    $(id).remove();
    var prevSort = getPrevSort(id);
    $("#user-tabs a").each(function (index, ele) {
        if ($(ele).attr('href') == ('#' + prevSort)) {
            $(ele).parent().addClass('active');
        }
    });
    // $("#user-tabs a:contains(" + prevSort + ")").parent().addClass('active');
    $('#' + prevSort).addClass('active');
    if (!checkNavEmpty()) {
        $("#user-nav-top").hide();
        $("#home-view").show();
        hide_home_view = false;
    };
    var $target = null;
    $("#sidebar a").each(function (index, ele) {
        if ($(ele).attr('data-tab') == prevSort) {
            $target = $(ele);
        }
    })
    resetLeftNavActive($target);
    createBreadCrumb($target);
    event.stopPropagation();
    event.preventDefault();
};
function createTabNav (text, data_tab) {
    $("#user-tabs").append($('<li onclick="return tab(this);" ondblclick="refresh(this);" class="active"><a onclick="return false;" href="#' + data_tab + '">' + text + '<i class="gi gi-remove_2" onclick="removeTab(this);"></i></a></li>'));
    $("#user-tab-content").append($('<div class="tab-pane active" id="' + data_tab + '">' + text + '</div>'));
};
function checkNavEmpty () {
    return $("#user-tabs li").length;
};
function getAlinkHref (text) {
    var dom = $(".sidebar-content a:contains(" + text + ")");
    return dom.attr('href');
};
function getTabContent (url, data_tab, fn, data) {
    $.ajax({
        type : 'post',
        data : data || {},
        url : url,
        success : fn || function (response) {
            $("#" + data_tab).html(response);
        }
    });
};
function checkTabExit (text) {
    return $("#user-tabs li:contains(" + text + ")").length;
};
function resetLeftNavActive (target) {
    $("#sidebar").find('.active').removeClass('active');
    $(target).addClass('active');
};
function createBreadCrumb (dom) {
    if ($(dom).parents('ul').prevAll('.sidebar-nav-menu').length) {
        var original = $(dom).parents('ul').prevAll('.sidebar-nav-menu').parent().prevAll('.sidebar-header').eq(0).text();
        var sub = $(dom).parents('ul').prevAll('.sidebar-nav-menu').eq(0).text();
        var t = $(dom).text();
        var crumb = '<li>' + original + '</li>';
        crumb += '<li>' + sub + '</li>';
        crumb += '<li>' + t + '</li>';
        $("#user-breadscrumb").html(crumb);
    } else {
        var original = $(dom).parent().prevAll('.sidebar-header').eq(0).text();
        var sub = $(dom).text();
        var crumb = '';
        if (original) {
            crumb = '<li>' + original + '</li>';
        }
        crumb += '<li>' + sub + '</li>';
        $("#user-breadscrumb").html(crumb);
    }
};

function setStorage (key, value) {
    window.localStorage[key] = value;
};

function getStorage (key) {
    return window.localStorage[key];
};

function openNav (text) {
    var bool = $("#sidebar a:contains(" + text + ")").parent().parent().prev().hasClass('sidebar-nav-menu');
    if (bool) {
        $("#sidebar a:contains(" + text + ")").parent().parent().prev().addClass('open');
        $("#sidebar a:contains(" + text + ")").parent().parent().css({display : 'block'});
    }
};

$(".ajaxLink").click(function () {
    var url = $(this).attr('href') || $(this).attr('data-href');
    var data_tab = $(this).attr('data-tab');
    var text = $(this).text();
    if (!hide_home_view) {
        hide_home_view = true;
        $('#home-view').hide();
        $("#user-nav-top").show();
    }
    tab(this);
    setTimeout(function () {
        var nav_dom = $("#user-tabs a:contains(" + text + ")");
        refresh(nav_dom, url, data_tab);
    },0);
    resetLeftNavActive(this);
    if (clearNav(text)) {
        createTabNav(text, data_tab);
    }
    return false;
});

$("#user-tab-content").click(function (ev) {
    var url = $(ev.target).attr('href') || $(ev.target).parents('a').attr('href');
    var data = $(ev.target).attr('data') || $(ev.target).parents('a').attr('data');
    //弹框功能
    if ($(ev.target).hasClass('ajaxLink') || $(ev.target).parents('a').hasClass('ajaxLink')) {
        $.ajax({
            type : 'post',
            data : data,
            url : url,
            success : function (response) {
                var response = JSON.parse(response);
                for (i in response) {
                    if (i == 'dialog') {
                        eval(response['dialog']);

                    } else if (i.indexOf('eval')  != -1) {
                        eval(response[i]);
                    }
                }
                form_commit();
            }
        });
        return false;
    //获取页面功能
    } else if ($(ev.target).hasClass('ajaxFetch') || $(ev.target).parents('a').hasClass('ajaxFetch')) {
        
        if (url.indexOf('?') !== -1) {
            var data = {};
            var paras = url.split('?');
            var d = paras[1].split('&');
            for (var i = 0; i < d.length; i++) {
                var tmp = d[i].split('=');
                data[tmp[0]] = tmp[1];
            }
        }
        var dom = $("#user-tabs .active");
        refresh(dom, url, null, data);
        return false;
    //删除功能
    } else if ($(ev.target).hasClass('ajaxDelete')) {
        bootbox.confirm({
            size: 'small',
            locale: 'zh_CN',
            message: "删除该数据后不能恢复，是否继续删除?", 
            callback: function(result){ 
                if (result) {
                    var url = $(ev.target).attr('href');
                    $.ajax({
                        type : 'get',
                        url : url,
                        success : function (response) {
                            var url = $("#sidebar .active").attr('href');
                            var response = $.parseJSON(response);
                            page(url, function () {
                                set_tip_left(response);
                            });
                        }
                    });
                }
            }
        });
       /* bootbox.confirm("删除该数据后不能恢复，是否继续删除?", function(result) {
          if (result) {
            var url = $(ev.target).attr('href');
            $.ajax({
                type : 'get',
                url : url,
                success : function (response) {
                    var url = $("#sidebar .active").attr('href');
                    var response = $.parseJSON(response);
                    page(url, function () {
                        set_tip_left(response);
                    });
                }
            });
          }
        }); */
        return false;
    }
});


//

function callBack (html) {
    $('#mask').remove();
    $('body').append(html);
    $('#mask').modal('show');
};

//
function showMask () {
    $("#mask").show();
};

//获取页面后初始化插件
$("#user-tab-content").bind("initUploadifive", function (event) {
  //init uploadify 
  if ($(this).find('.tab-pane.active .file_upload').length) {
    $(this).find('.tab-pane.active .file_upload').each(function (index, ele) {
        var queueID = $(ele).attr('queueID');
        var $file_name_div = $(this).parents('.col-md-6').find('.user-file-name');
        $(ele).uploadifive({
            'auto'             : false,
            'multi'            : false,
            'formData'         : {
                                   'timestamp' : timestamp,
                                   'token'     : token,
                                 },
            'buttonText'       : '选择上传文件',
            'queueID'          : queueID,
            'uploadScript'     : '/Application/Manage/Resource/uploadifive.php',
            'onAddQueueItem' : function(file) {
                $file_name_div.prev().hide();
                $file_name_div.html(file.name).show();
            },
            'onProgress'   : function(file, e) {
                /*if (e.lengthComputable) {
                    var percent = Math.round((e.loaded / e.total) * 100);
                }*/
                if ($(this).hasClass('weixin_upload')) {
                    // 判断如果是微信的上传,则由于上传素材可能比较慢,所以需要把提交按钮disabled掉
                    $(this).parents('form').find('.shenhe_submit').html('图片上传中...');
                    $(this).parents('form').find('.shenhe_submit').addClass('disabled');
                    
                }
                var percent = Math.round((e.loaded / e.total) * 100);
                var str = '<p class="user-upload-percent">已上传：' + percent + '%</p>';
                $file_name_div.html(str);
            },
            'onUploadComplete' : function(file, data) { 
                // console.log(file, data);
                $file_name_div.hide();
                $(this).parents('.col-md-6').find(".preview-img img").attr('src', data).show();
                $(this).parents('.col-md-6').find(".preview-img input[type='hidden']").val(data);
                // console.log($(this).parents('.col-md-6').find(".preview-img input[type='hidden']"));
                if ($(this).hasClass('weixin_upload')) {
                    var _that = $(this);
                    var url = $(this).attr('data-url') || '?m=Manage&c=Merchant&a=uploadTempImg1';
                    var id = $(this).attr('data-id');
                    // 判断如果是微信的上传,则需要调用临时上传图片接口
                    $.ajax({
                        type: 'post',
                        data: {filename : data, id : id},
                        // url: '?m=Manage&c=Merchant&a=uploadTempImg1',
                        url: url,
                        success : function  (data2) {
                            //把media_id的值赋给隐藏域,以便提交
                            if(data2.media_id)
                            {
                            //如果有media_id,则是提交资质
                            _that.parents('.col-md-6').find(".preview-img input[type='hidden']").eq(0).val(data2.media_id);
                            }
                            if(data2.logo_url)
                            {
                            //如果有logo_url,则是提交创券资料
                            _that.parents('.col-md-6').find(".preview-img input[type='hidden']").eq(0).val(data2.logo_url);
                            }
                            //上传微信接口成功,把提交的disabled属性去掉
                            _that.parents('form').find('.shenhe_submit').html('确定');
                            _that.parents('form').find('.shenhe_submit').removeClass('disabled');

                        }
                    })
                }
            }
        });
    });
  };
  //init ueditor
  if ($(this).find('.tab-pane.active .ueditor').length) {
    var scope = $("#sidebar .active").attr('data-tab');
    $(this).find('.tab-pane.active .ueditor').each(function (index, ele) {
        var id = $(ele).attr('id');
        if (window['myUEditor' + scope]) {
            // console.log(window['myUEditor' + scope] == window['myUEditor' + 'shop']);
            window['myUEditor' + scope].destroy();
        }
        window['myUEditor' + scope] = UE.getEditor(id);
    })
  }
  //init spinner
  $(".user-spinner")
      .spinner('delay', 2000) //delay in ms
      .spinner('changed', function(e, newVal, oldVal){
        //trigger lazed, depend on delay option.
        console.log(newVal, oldVal);
      })
      .spinner('changing', function(e, newVal, oldVal){
        // alert('aaa');
        //trigger immediately
      });

      $('.file_upload').bind('user_upload_fn', function () {
            var id = $(this).attr('id');
            if (window[id + '_click']) {
                window[id + '_click'](this);
            }
       });

       $('.file_upload').trigger('user_upload_fn');

});

//表单提交后
function form_commit () {
    $("#mask #wizard-form-submit").click(function () {
        var data = $("#mask #basic-wizard-form").serialize();
        $.ajax({
            type : 'post',
            data : data,
            url : $("#basic-wizard-form").attr('action'),
            success : form_commit_success_callback,
        })
    })
};

//表单提交
function submit_form (_this) {
    var data = $(_this).parents('form').serialize();
    $.ajax({
        type : 'post',
        data : data,
        url : $("#basic-wizard-form").attr('action'),
        success : form_commit_success_callback,
    })
};

function form_commit_success_callback (response) {
    var url = $("#sidebar .active").attr('href');
    var response = $.parseJSON(response);
    page(url, function () {
        set_tip_left(response);
    });
    $('#mask').modal('hide');
    // set_tip_left();
    
}

function set_tip_left (response) {
    $("#user-tip").fadeIn().find('div').hide();
    $("#user-tip").find('.' + response.state).html(response.msg).show();
    var l = ($(document).width() - $("#user-tip").outerWidth()) / 2;
    $("#user-tip").css({left : l});
    setTimeout(function () {
        $("#user-tip").fadeOut();
    }, 1500);
}

//数据分页刷新函数
function page (url, fn) {
    var data_tab = $("#user-tabs .active").find('a').attr('href').substr(1);
    var text = $("#user-tabs .active").text();
    if (!hide_home_view) {
        hide_home_view = true;
        $('#home-view').hide();
        $("#user-nav-top").show();
    }
    var t = $("#sidebar .active")[0];
    tab(t);
    setTimeout(function () {
        var nav_dom = $("#user-tabs a:contains(" + text + ")");
        refresh(nav_dom, url, data_tab, null, fn);
    },0);
    resetLeftNavActive(t);
    if (clearNav(text)) {
        createTabNav(text, data_tab);
    }
}

//返回按钮
function go_back () {
    var url = $("#sidebar .active").attr('href');
    page(url);
}
