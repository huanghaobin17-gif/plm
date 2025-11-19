layui.define(function(exports){
    layui.use(['layer', 'form', 'element','table','laydate'], function() {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table;

        form.on('submit(showAssets)',function (data) {
            var assid = $(this).attr('data-id');
            var assets = $(this).attr('data-assets');
            top.layer.open({
                type: 2,
                title: '【'+assets+'】设备详情信息',
                area: ['1050px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [admin_name+'/Lookup/showAssets.html'+'?assid='+assid]
            });
            return false;
        });
        form.on('submit(scanTemplate)',function (data) {
            var qsid = $(this).attr('data-id');
            var planName = $(this).attr('data-name');
            top.layer.open({
                type: 2,
                title: '【'+planName+'】模板预览',
                area: ['65%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [admin_name+'/Quality/scanTemplate.html'+'?qsid='+qsid]
            });
            return false;
        });
    });
    exports('controller/qualities/quality/showQualityPlan', {});
});
