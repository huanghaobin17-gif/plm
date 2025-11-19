layui.define(function(exports){
    layui.use(['layer','form'], function () {
        var form = layui.form, $ = layui.jquery,layer = layui.layer;
        //监听提交
        form.on('submit(save)', function (data) {
            var params = data.field;
            if(!$.trim(params.cancle_remark)){
                layer.msg('请填写撤回原因', {icon: 2}, 2000);
                return false;
            }
            submit($, params, params.action);
            return false;
        });
    });
    exports('controller/repair/search/cancelRepair', {});
});
