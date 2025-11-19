layui.define(function(exports){

    layui.use(['form'], function() {
        var form = layui.form, $ = layui.jquery;
        //监听提交
        form.on('submit(add)', function (data) {
            params = data.field;
            if (params.update == 1){
                confirmaddSubmit($,params,'editTemplate?type=confirmAdd');
            }else{
                confirmaddSubmit($,params,'addTemplate?type=confirmAdd');
            }
            return false;
        })
    });

    function confirmaddSubmit($,params,url){
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
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
        var layerId=$('input[name="layerId"]').val();
        layerId=layerId.split(",");
        layer.msg(msg,{
            icon: iconnum,
            time: 2000
        }, function(){
            if(isclose == 1){
                parent.layer.close(index); //再执行关闭
                $.each(layerId, function(key, val) {
                    parent.layer.close(val);
                });
            }
        });
    }

//返回上一步
    $("#prev").click(function () {
        var index = parent.layer.getFrameIndex(window.name);
        parent.layer.close(index);
    });

//移除一行
    function delRow(k){
        $(k).parent().parent().remove();
    }
    exports('controller/patrol/patrolsetting/confirmadd', {});
});




