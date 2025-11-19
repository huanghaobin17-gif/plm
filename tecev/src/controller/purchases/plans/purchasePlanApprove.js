layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form;

        //监听提交
        form.on('submit(saveApprove)', function(data){
            var params = {};
            params = data.field;
            submit($,params,'purchasePlanApprove');
            return false;
        });
    });
    exports('controller/purchases/plans/purchasePlanApprove', {});
});