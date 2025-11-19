layui.define(function(exports){
    layui.use(['layer', 'form','formSelects','laydate','table','suggest'], function(){
        var layer = layui.layer,
            form = layui.form;

        layer.config(layerParmas());
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
    exports('controller/purchases/tendering/showTbSubmit', {});
});