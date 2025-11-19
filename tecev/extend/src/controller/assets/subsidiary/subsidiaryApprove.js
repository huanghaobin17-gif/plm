layui.define(function(exports){
    layui.use(['layer', 'form', 'table'], function () {
        var layer = layui.layer
            , form = layui.form;

        //初始化
        form.render();
        //新增操作
        form.on('submit(subsidiaryApprove)', function (data) {
            var params = data.field;
            submit($, params, subsidiaryApproveUrl);
            return false;
        });
    });
    exports('controller/assets/subsidiary/subsidiaryApprove', {});
});
