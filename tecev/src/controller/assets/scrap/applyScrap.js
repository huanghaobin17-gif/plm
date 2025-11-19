layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'tipsType'], function () {
        var form = layui.form,
            table = layui.table,
            tipsType = layui.tipsType;

        form.render();
        tipsType.choose();
        //提交报废申请
        form.on('submit(add)', function (data) {
            var params = data.field;
            var checkStatus = table.checkStatus('subsidiaryData');
            var length = checkStatus.data.length;
            if (length > 0) {
                var assid = '';
                for (var i = 0; i < length; i++) {
                    assid += checkStatus.data[i]['assid'] + ',';
                }
                params.subsidiary_assid = assid.substring(0, assid.length - 1);
            }
            if(params.subsidiary_assid){
                layer.confirm('确定连同附属设备一起报废？', {icon: 3, title: '报废确认'}, function (index) {
                    submit($, params, 'applyScrap');
                });
            }else{
                submit($, params, 'applyScrap');
            }
            return false;
        })
    });
    exports('controller/assets/scrap/applyScrap', {});
});

