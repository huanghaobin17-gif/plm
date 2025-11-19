layui.define(function(exports){
    //相关文件查看
    $("#showFile").on('click', function () {
        var scrid = $("input[name='scrid']").val();
        top.layer.open({
            id: 'showFiles',
            type: 2,
            title: '相关文件查看',
            scrollbar: false,
            area: ['70%', '100%'],
            closeBtn: 1,
            offset: 'r',//弹窗位置固定在右边
            content: [admin_name+'/Scrap/getScrapList?action=showFile&scrid=' + scrid]
        });
    });
    exports('controller/assets/scrap/showScrap', {});
});
