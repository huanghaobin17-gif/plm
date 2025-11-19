layui.define(function(exports){
    layui.use('form', function() {
        var form = layui.form, $ = layui.jquery;

        form.render();

        //监听提交
        form.on('submit(save)', function (data) {
            var params = data.field;
            submit($,params,params.actionname+'?type=editProblem');
            return false;
        })
    });
    exports('controller/repair/repairSetting/editProblem', {});
});







