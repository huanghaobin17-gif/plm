layui.define(function(exports){
    layui.use(['layer', 'form', 'table','laydate'], function () {
        var layer = layui.layer
            , laydate = layui.laydate
            , form = layui.form;

        //初始化

        laydate.render({
            elem: '#check_time',
            type: 'date',
            format: 'yyyy-MM-dd',
            min: 0
        });

        form.render();
        //新增操作
        form.on('submit(subsidiaryCheck)', function (data) {
            var params = data.field;
            submit($, params, subsidiaryCheckUrl);
            return false;
        });
    });
    exports('controller/assets/subsidiary/subsidiaryCheck', {});
});