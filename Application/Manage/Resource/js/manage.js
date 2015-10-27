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
