layui.define(function(exports){
    layui.use(['layer', 'form','formSelects','suggest'], function() {
        var form = layui.form, $ = layui.jquery;
        form.render();
        form.verify({
            brand_name: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '品牌名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '品牌名称不能全为数字';
                }
            }
        });

        //监听提交
        form.on('submit(editBrandDic)', function (data) {
            var params = data.field;
            submit($,params,'editBrandDic');
            return false;
        });
    });
    exports('controller/basesetting/dictionary/editBrandDic', {});
});