function submit(params,url,goUrl){
    $.ajax({
        timeout: 5000,
        type: "POST",
        url: url,
        data: params,
        dataType: "json",
        async: true,
        beforeSend:beforeSend,
        success: function (data) {
            if (data.status === 1) {
                $.toptip(data.msg, 'success');
                setTimeout(function(){//两秒后跳转
                    if(goUrl){
                        window.location.href = goUrl;
                    }
                },2000);
            }else{
                $.toptip(data.msg, 'error');
            }
        },
        error: function () {
            $.toptip('网络访问失败', 'error');
        },
        complete:complete
    });
}
function beforeSend(){
    layer.load(2);
}
function complete(){
    layer.closeAll('loading');
}
