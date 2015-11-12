//后台eval中执行的函数写在这里

function eva () {
    alert('aaa');
    var text = $("#mask .modal-body").text();
    console.log(text);
    document.title = text;
}

function eva2 () {
    console.log('eva2');
}

function kaquan_pic1_click (_this) {
	// console.log(_this);
	$(_this).parents('.col-md-6').find('a:contains("确定上传")').click(function (){
		$(_this).attr('data-url', '?m=Manage&c=Coupon&a=uploadLogo');
		$(_this).attr('data-id', $("#kaquan_id").val());
	})
};
