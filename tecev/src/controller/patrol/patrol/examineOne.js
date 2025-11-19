layui.define(function(exports){
    layui.use(['layer', 'form','element'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,element = layui.element;
        //暂存该设备保养
        form.on('submit(examine)', function (data) {
            var url = $('input[name="url"]').val();

            var params = data.field;
            params.action= $('input[name="action"]').val();
            examine($, params, url);
            return false;
        });
    });
    function examine($, params, url) {
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            success: function (data) {
                if (data) {
                    if (data.status == 1) {
                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                        layer.msg(data.msg, {
                            icon: 1,
                            time: 2000
                        }, function () {
                            parent.layer.close(index); //再执行关闭
                        });
                        return false;
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                } else {
                    layer.msg("数据异常！", {icon: 2}, 1000);
                }
            },
            error: function () {
                layer.msg("网络访问失败", {icon: 2}, 1000);
            }
        });
    }
    exports('controller/patrol/patrol/examineOne', {});
});






