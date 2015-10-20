//后台eval中执行的函数写在这里
function ttt (a) {
    console.log($("#mask"));
}

function eva () {
    alert('eva');
    var text = $("#mask .modal-body").text();
    console.log(text);
    document.title = text;
}
