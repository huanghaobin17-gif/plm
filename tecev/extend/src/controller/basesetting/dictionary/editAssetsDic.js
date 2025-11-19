layui.define(function(exports){
    layui.use(['layer', 'form','formSelects','suggest'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,formSelects = layui.formSelects,suggest = layui.suggest;
        form.render();
        //初始化搜索建议插件
        suggest.search();
        //管理科室 多选框初始配置
        formSelects.render('assets_category', selectParams(1));
        formSelects.btns('assets_category',selectParams(2));

        form.verify({
            assets: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '用户名首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '用户名不能全为数字';
                }
            }
        });

        //切换医院后获取相应科室
        form.on('select(hospital_id)',function (data) {
            $("#dic_category_edit").bsSuggest('destroy');
            var cathtml = initsuggestCate(data.value);
            if(cathtml == ''){
                $('select[name="catid"]').html('');
                form.render();
                layer.msg('医院未设置设备分类，请先设置分类！', {icon: 2,time:3000});
            }else{
                $('select[name="catid"]').html('');
                $('select[name="catid"]').html(cathtml);
                form.render();
            }
        });
        //监听提交
        form.on('submit(editAssetsDic)', function (data) {
            params = data.field;
            params.assets_category = formSelects.value('assets_category', 'valStr');
            submit($,params,'editAssetsDic');
            return false;
        });
        /*
         /选择字典类型
         */
        $("#dic_category_edit").bsSuggest(
            returnDicCategory()
        );
    });
    exports('controller/basesetting/dictionary/editAssetsDic', {});
});