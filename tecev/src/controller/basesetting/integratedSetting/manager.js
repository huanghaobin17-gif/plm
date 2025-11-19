layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.verify({
            manager: function(value){
                if (!value) {
                    return '';
                }
            }
        });
        //监听提交
        form.on('submit(add)', function (data) {
            params = data.field;
            params.action = 'setApproveUser';
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/IntegratedSetting/department.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg,{icon:1,time:1000},function () {
                                parent.layer.close(index); //再执行关闭
                            });
                        }else{
                            layer.msg(data.msg,{icon : 2},1000);
                        }
                    }else {
                        layer.msg("数据异常！",{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            return false;
        })
    });
    exports('controller/basesetting/integratedSetting/manager', {});
});







