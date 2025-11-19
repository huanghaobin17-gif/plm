layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;
        form.verify({
            rolename: function(value){
                if(value.length < 2){
                    return '角色名称至少2个字符';
                }
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '角色名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d$/.test(value)) {
                    return '角色名称不能全为数字';
                }
            }
        });
        //监听提交
        form.on('submit(addRole)', function(data){
            var url = admin_name+'/Privilege/addRole';
            params = data.field;
            submit($,params,url);
            return false;
        });
    });
    exports('controller/basesetting/privilege/addRole', {});
});