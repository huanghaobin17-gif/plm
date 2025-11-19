layui.define(function(exports){
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.verify({
            title: function(value){
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '所填项首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '所填项不能全为数字';
                }
            },
            tel: function(value) {
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '所填项首尾不能出现下划线\'_\'';
                }
                if(!checkTel(value)){
                    return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
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
            submit($,params,params.url);
            return false;
        });
        form.on('select(change_hospital)', function (data) {
            var params = {};
            params.job_hospitalid = data.value;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Public/getAllUserSearch',
                data: params,
                dataType: "json",
                success: function (data) {
                    var html = '<option value=""></option>',departrespon = $('select[name="departrespon"]'),assetsrespon = $('select[name="assetsrespon"]');
                    departrespon.html(html);
                    assetsrespon.html(html);
                    $.each(data.value,function (i,item) {
                        html += '<option value="'+item.username+'">'+item.username+'</option>';
                    });
                    departrespon.append(html);
                    assetsrespon.append(html);
                    form.render('select');
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
        });
    });
    exports('controller/basesetting/integratedSetting/editdepartment', {});
});








