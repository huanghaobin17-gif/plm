
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
            content: [showTender+'?action=showTenderDetail&id='+id]
        });
    });
});