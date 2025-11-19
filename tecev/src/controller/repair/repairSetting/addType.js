layui.define(function(exports){
    layui.use('form', function() {
        var form = layui.form, $ = layui.jquery;

        form.render();

        form.verify({
            type: function(value,item){
                if(value.length < 2){
                    return '类型名称至少2个字符';
                }
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '类型名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '类型名称不能全为数字';
                }
            }
        });

        //监听提交
        form.on('submit(save)', function (data) {
            var params=data.field;
            submit($,params,params.actionname);
            return false;
        })
    });
    exports('controller/repair/repairSetting/addType', {});
});







