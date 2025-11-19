layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery;
        form.verify({
            Type: function (value) {
                if (value){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '类别名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '类别名称不能全为数字';
                    }
                }else {
                    return '类别名称不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(add)', function (data) {
            params = data.field;
            submit($,params,'addPoints');
            return false;
        })
    });
    exports('controller/patrol/patrolsetting/addpoints', {});
});

