layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        form.render();
        //监听提交
        form.on('submit(saveApprove)', function(data){
            var params = data.field;
            submit($,params,'approveOut');
            return false;
        });
    });
    exports('controller/purchases/purchaseCheck/approveOut', {});
});