// 关闭单个
function submit($,params,url){
    $.ajax({
        timeout: 30000,
        type: "POST",
        url: url,
        data: params,
        dataType: "json",
        async: true,
        beforeSend:beforeSend,
        success: function (data) {
            if (data.status == 1) {
                CloseWin(data.msg);
            }else{
                layer.msg(data.msg,{icon : 2},1000);
            }
        },
        error: function () {
            layer.msg("网络访问失败",{icon : 2},1000);
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
//关闭页面
function CloseWin(msg,num,close) {
    var iconnum = arguments[1] == 2 ? arguments[1] : 1;
    var isclose = arguments[2] == 0 ? arguments[2] : 1;
    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
    layer.msg(msg,{
        icon: iconnum,
        time: 2000
    }, function(){
        if(isclose == 1){
            parent.layer.close(index); //再执行关闭
        }
    });
}
//关闭全部弹窗
function submitCloseAll($,params,url){
    $.ajax({
        timeout: 5000,
        type: "POST",
        url: url,
        data: params,
        dataType: "json",
        beforeSend:beforeSend,
        success: function (data) {
            if (data.status == 1) {
                CloseAllWin(data.msg);
            }else{
                layer.msg(data.msg,{icon : 2},1000);
            }
        },
        error: function () {
            layer.msg("网络访问失败",{icon : 2},1000);
        },
        complete:complete
    });
}
function CloseAllWin(msg, num, close) {
    var iconnum = arguments[1] == 2 ? arguments[1] : 1;
    var isclose = arguments[2] == 0 ? arguments[2] : 1;
    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
    layer.msg(msg,{
        icon: iconnum,
        time: 2000
    }, function(){
        if(isclose == 1){
            parent.layer.closeAll(); //再执行关闭
            //parent.location.reload(); // 父页面刷新
        }
    });
}
