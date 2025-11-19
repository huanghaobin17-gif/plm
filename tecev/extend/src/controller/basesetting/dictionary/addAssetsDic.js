layui.define(function(exports){
    layui.use(['layer', 'form','formSelects','suggest'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,formSelects = layui.formSelects,suggest = layui.suggest;
        //初始化搜索建议插件
        suggest.search();

        //管理科室 多选框初始配置
        formSelects.render('assets_category', selectParams(1));
        formSelects.btns('assets_category',selectParams(2));

        form.verify({
            assets: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '设备名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '设备名称不能全为数字';
                }
            }
        });

        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            params.assets_category = formSelects.value('assets_category', 'valStr');
            submit($,params,'addAssetsDic');
            return false;
        });

        var cathtml = initsuggestCate();
        $('select[name="catid"]').html('');
        $('select[name="catid"]').html(cathtml);
        form.render();
        getDicCategory();
    });
    function initsuggestCate() {
        var html = '<option value=""></option>';
        $.ajax({
            type: "POST",
            url: admin_name+'/Public/getAllCategorySearch',
            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
            //成功返回之后调用的函数
            async:false,
            success: function (data) {
                if(data.value.length > 0){
                    $.each(data.value,function (i,item) {
                        if(item.parentid > 0){
                            html += '<option value="'+item.catid+'"> ➣ '+item.category+'</option>';
                        }else{
                            html += '<option value="'+item.catid+'">'+item.category+'</option>';
                        }
                    });
                }else{
                    html = '';
                }
            }
        });
        return html;
    }
    function getDicCategory() {
        /*
         /选择字典类型
         */
        $("#dic_category_add").bsSuggest('init',
            returnDicCategory()
        );
    }
    exports('controller/basesetting/dictionary/addAssetsDic', {});
});