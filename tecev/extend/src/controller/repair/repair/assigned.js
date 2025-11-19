layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            submit($, params, params.action);
            return false;
        })
    });
    exports('controller/repair/repair/assigned', {});
});
