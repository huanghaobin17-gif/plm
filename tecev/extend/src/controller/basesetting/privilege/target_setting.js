layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        form.render();

        //监听提交
        form.on('submit(save_setting)', function(data){
            var url = admin_name+'/Privilege/editRolePrivi';
            var params = {};
            params = data.field;
            params.type = 'target_setting';
            submit($,params,url);
            return false;
        });
    });
    exports('controller/basesetting/privilege/target_setting', {});
});

