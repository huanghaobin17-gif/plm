layui.define(function(exports){
    layui.use(['form'], function() {
        var form = layui.form, $ = layui.jquery;

        form.render();

        form.verify({
            detail: function (value) {
                if (value){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '明细名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '明细名称不能全为数字';
                    }
                }else {
                    return '明细名称不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(edit)', function (data) {
            params = data.field;
            submit($,params,'editDetail');
            return false;
        })
    });
    exports('controller/patrol/patrolsetting/editdetail', {});
});
