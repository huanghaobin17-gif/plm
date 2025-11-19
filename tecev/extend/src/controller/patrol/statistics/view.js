layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.verify({
            priceMin: function (value,item) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            },
            priceMax: function (value,item) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            }
        });
        //监听搜索按钮
        form.on('submit(eventquery)', function (data) {
            params = data.field;
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            showChart(params);
            return false;
        });
        //数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            params = data.field;
            params.assnum = $('input[name="assnum"]').val();
            params.cid = $('input[name="cid"]').val();
            params.startDate = $('input[name="startDate"]').val();
            params.endDate = $('input[name="endDate"]').val();
            // params.base64Data = myChart.getDataURL({
            //     pixelRatio: 1.2,//像素精度
            //     backgroundColor: '#404a59'
            // });
            postDownLoadFile({
                url:url,
                data:params,
                method:'POST'
            });
            return false;
        });
    });
    /**
     * post请求无法直接发送请求下载excel文档，是因为我们在后台改变了响应头的内容：
     * Content-Type: application/vnd.ms-excel
     * 致post请求无法识别这种消息头,导致无法直接下载。
     * 解决方法：
     * 改成使用form表单提交方式即可
     */
    var postDownLoadFile = function (options) {
        var config = $.extend(true, { method: 'POST' }, options);
        var $iframe = $('<iframe id="down-file-iframe" />');
        var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
        $form.attr('action', config.url);
        for (var key in config.data) {
            $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
        }
        $(document.body).append($iframe);
        $iframe.append($form);
        $form[0].submit();
        $iframe.remove();
    };
    exports('controller/patrol/statistics/view', {});
});