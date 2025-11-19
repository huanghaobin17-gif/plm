layui.define(function(exports){
    var table = {};
    layui.use('table', function(){
        table = layui.table;
    });
    layui.use(['layer', 'form'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer;
        form.verify({
            factory: function (value) {
                value = $.trim(value);
                if(value == ''){
                    return '请填写生产厂商！';
                }
            },
            supplier: function (value) {
                value = $.trim(value);
                if(value == ''){
                    return '请填写供应商！';
                }
            }
        });
        //监听提交
        form.on('submit(saveFactory)', function (data) {
            var params = data.field;
            params.action = 'editFactory';
            // var isMobile=/^1[3|4|5|7|8|9][0-9]\d{4,8}$/;
            // var isPhone=/^((0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/;
            if($.trim(params.factory_tel)){
                if (/(^\_)|(\__)|(\_+$)/.test(params.factory_tel)) {
                    layer.msg("所填项首尾不能出现下划线",{icon : 2},1000);
                    return false;
                }
                if(!checkTel(params.factory_tel)){
                    layer.msg("请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符",{icon : 2},1000);
                    return false;
                }
            }
            if($.trim(params.supp_tel)){
                if (/(^\_)|(\__)|(\_+$)/.test(params.supp_tel)) {
                    layer.msg("所填项首尾不能出现下划线",{icon : 2},1000);
                    return false;
                }
                // if(!isMobile.test(params.supp_tel) && !isPhone.test(params.supp_tel)){
                if(!checkTel(params.supp_tel)){
                    layer.msg("请填写正确的厂商联系手机号码、固话(如：020-12345678)",{icon : 2},1000);
                    return false;
                }
            }
            if($.trim(params.repa_tel)){
                if (/(^\_)|(\__)|(\_+$)/.test(params.repa_tel)) {
                    layer.msg("所填项首尾不能出现下划线",{icon : 2},1000);
                    return false;
                }
                if(!checkTel(params.repa_tel)){
                    layer.msg("请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符",{icon : 2},1000);
                    return false;
                }
            }
            submit($,params,'addAssets');
            return false;
        })
    });
    exports('controller/assets/lookup/editFactory', {});
});
