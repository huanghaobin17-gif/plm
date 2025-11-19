layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.verify({
            category: function(value){
                if(value.length < 2){
                    return '分类名称至少2个字符';
                }
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '分类名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '分类名称不能全为数字';
                }
            },
            hospital: function(value){
                if (!value){
                    return '请选择所属医院';
                }
            }
        });
        //监听提交
        form.on('submit(edit)', function (data) {
            params = data.field;
            params.url = $(this).attr('data-url');
            if(params.parentid == 0){
                layer.confirm('如修改父分类编码，会一并修改其所有子类编码的前值，确定修改吗', {icon: 3, title:'修改父分类【'+params.category+'】'}, function(index){
                    submit($,params,params.url);
                });
            }else{
                submit($,params,params.url);
            }
            return false;
        })
    });
    exports('controller/basesetting/integratedSetting/editcategory', {});
});






