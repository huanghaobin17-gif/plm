layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'laydate'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate;
        //先更新页面部分需要提前渲染的控件
        form.render();
        //日期初始化
        laydate.render({
            elem: '#next_date',
            festival: true,
            min: '1'
        });

        //修改操作
        form.on('submit(save)', function (data) {
            var params = data.field;
            if (!check_num(params.categorys)) {
                layer.msg('请选择计量分类', {icon: 2});
                return false;
            }
            if (!params.next_date) {
                layer.msg('请补充下次待检日期', {icon: 2});
                return false;
            }
            if (!check_num(params.remind_day) || params.remind_day <= 0) {
                layer.msg('请补充正确的提前提醒天数', {icon: 2});
                return false;
            }
            console.log(params);
            var url = admin_name+'/Metering/saveMetering';
            submit($, params, url);
            return false;
        });

        //新增分类
        $('#addMCategory').click(function () {
            layer.open({
                type: 1,
                title: '新增计量分类',
                area: ['450px', '300px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addMCategoryBody'),
                end: function () {
                    $('textarea[name="categorysTitle"]').val('');
                }
            });
        });
        //确认添加分类
        form.on('submit(addCategorys)', function (data) {

            if (!data.field.categorysTitle) {
                layer.msg("请输入分类名称", {icon: 2}, 1000);
                return false;
            }

            var categorysTitle=data.field.categorysTitle.split("\n");

            data.field.categorysTitle='';
            $.each(categorysTitle,function (k,v) {
                if(v){
                    data.field.categorysTitle+=','+v;
                }
            });
            var params = {};
            params.categorys = data.field.categorysTitle;
            params.type = 'addCategorys';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/addMetering',
                data: params,
                dataType: "json",
                beforeSend: beforeSend,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {
                            icon: 1,
                            time: 1000
                        }, function () {
                            var html = '';
                            $.each(data.result, function (key, value) {
                                html += '<option value="' + value.mcid + '">' + value.mcategory + '</option>';
                            });
                            $('select[name="categorys"]').html(html);
                            form.render('select');
                        });
                    }else{
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete: complete
            });
            return false;
        });
    });
    exports('controller/metering/metering/saveMetering', {});
});