layui.define(function(exports){
    layui.use('form', function(){
        $(".showDetail").on('click',function () {
            var id = $(this).attr('data-id');
            top.layer.open({
                type: 2,
                title: '查看应标供应商信息',
                area: ['700px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [purchaseLifeList+'?action=showTenderDetail&id='+id]
            });
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
    exports('controller/purchases/purchaseLife/showLife', {});
});