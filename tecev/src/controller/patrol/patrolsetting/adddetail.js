layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;

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
        form.on('submit(add)', function (data) {
            params = data.field;
            var a=data.field['name'].split("\n");
            flag = true;
            $.each(a,function(j,val){
                if (val == ''){
                    flag = false;
                }else {
                    a[j]=$.trim(val);
                }
            });
            if (!flag){
                layer.msg('明细名称其中一行有空格，请检查后添加',{icon : 2},1000);
                return false;
            }else {
                params.name = a.join(',');
                params.require = data.field['require'].split("\n");
                params.require = params.require.join(",");
                submit($,params,'addPoints?type=addDetail');
                return false;
            }
        })
    });
    exports('controller/patrol/patrolsetting/adddetail', {});
});

