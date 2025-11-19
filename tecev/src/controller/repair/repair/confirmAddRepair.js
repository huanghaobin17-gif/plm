layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        //暂存该设备保养

        form.on('submit(suer)', function (data) {
            var url = $('input[name="action"]').val();
            var params = data.field;
            params.action='confirmAddRepair';
            params.from=1;
            submit($, params, url);
            return false;
        });
    });
    exports('controller/repair/repair/confirmAddRepair', {});
});









