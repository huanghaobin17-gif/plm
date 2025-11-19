layui.define(function(exports){
    layui.use(['layer', 'form','element','suggest'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,suggest = layui.suggest,element = layui.element;

        //初始化搜索建议插件
        suggest.search();

        form.on('checkbox(Choose)', function(data){
            if (data.elem.checked){
                var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
            }else{
                var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
            }
            form.render('checkbox');
        });
        form.verify({
            name: function (value, item) {
                if (value){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '模板名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '模板名称不能全为数字';
                    }
                }else {
                    return '模板名称不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(submit)', function (data) {
            var num = "";
            $("input[name='num']:checked").each(function(){
                if(num==''){
                    num = $(this).val();
                }else{
                    num += ","+$(this).val();
                }
            });
            params = data.field;
            params.num = num;
            if (data.field['name'] == ''){
                layer.msg("模板名称不能为空",{icon : 2},1000);
                return false;
            }else if(data.field['num'] == ''){
                layer.msg("请至少勾选一个保养明细",{icon : 2},1000);
                return false;
            }else {
                flag = true;
                $.each(tp,function(j,val){
                    if (data.field['name'] == val['name']){
                        layer.msg("已存在该名称的模板",{icon : 2},1000);
                        flag = false;
                        return false;
                    }
                });
                if(flag){
                    submit($,params,'addTemplate');
                    return false;
                }
            }
            return false;
        });
        $("#templateName").bsSuggest({
            url: admin_name+'/Public/getAllTemplate',
            effectiveFieldsAlias:{tpid:"序号",name:"模板名称"},
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "tpid",
            keyField: "name",
            listStyle: {
                "max-height": "375px", "max-width": "480px",
                "overflow": "auto", "width": "400px", "text-align": "center"
            },
            clearable: false
        })
    });
    exports('controller/patrol/patrolsetting/addtemplate', {});
});










