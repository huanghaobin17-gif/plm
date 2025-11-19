
layui.define(function(exports){
    layui.use('form', function() {
        var form = layui.form, $ = layui.jquery;
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
        //监听下拉框变化
        form.on('select(change)', function(data){
            if (data.value != 0){
                $(".change").css("display","none");
                $("input[name='catenum']").removeAttr("lay-verify");
            }else{
                $(".change").css("display","block");
                $("input[name='catenum']").Attr("lay-verify","required|number");
            }
        });
        //监听提交
        form.on('submit(add)', function (data) {
            params = data.field;
            params.url = $(this).attr('data-url');
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: params.url,
                data: params,
                dataType: "json",
                async: true,
                before:layer.load(2),
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000},function () {
                            parent.location.reload();
                        });
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:layer.closeAll('loading')
            });
            //submit($,params,params.url);
            return false;
        })
    });
    exports('controller/basesetting/integratedSetting/addcategory', {});
});






