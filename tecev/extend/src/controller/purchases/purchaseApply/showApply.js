layui.define(function(exports){
    layui.use('form', function(){

        //下载
        $(document).on('click','.downFile',function () {
            var params={};
            params.path= $(this).data('path');
            params.filename=$(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data:params,
                method:'POST'
            });
            return false;
        });
        //预览
        $(document).on('click','.showFile',function () {
            var path= $(this).data('path');
            var name=$(this).data('name');
            var url=admin_name+'/Tool/showFile';
            top.layer.open({
                type: 2,
                title: name + '相关文件查看',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['70%', '100%'],
                closeBtn: 1,
                content: [url +'?path=' + path + '&filename=' + name]
            });
            return false;
        });
    });
    exports('controller/purchases/purchaseApply/showApply', {});
});