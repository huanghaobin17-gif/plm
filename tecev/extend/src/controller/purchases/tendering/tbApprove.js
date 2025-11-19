layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        form.render();
        //监听提交
        form.on('submit(saveReviewApp)', function(data){
            var url = "tbApprove";
            var params = data.field;
            submit($,params,url);
            return false;
        });

        //提示是否下载/预览
        $(document).on('click', '.downFile', function () {
            var params = {};
            params.path = $(this).data('path');
            params.filename = $(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
        });
    });
    exports('controller/purchases/tendering/tbApprove', {});
});
