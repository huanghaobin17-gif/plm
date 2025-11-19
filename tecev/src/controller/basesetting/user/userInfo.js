layui.define(function(exports){
    layui.use(['layer', 'form','element'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, element = layui.element;
        form.render();
        form.verify({
            username: function (value, item) { //value：表单的值、item：表单的DOM对象
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '用户名首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '用户名不能全为数字';
                }
            },
            password: [/^(?![a-z]+$)(?![A-Z]+$)(?![0-9]+$)(?![\W_]+$)[a-zA-Z0-9\W_]{8,30}$/, '密码必须8到18位，且大写字母 小写字母 数字 特殊字符，四种包括两种,且不能出现空格'],
            passwordconfirm: function (value) {
                if (value != $("input[name='newpassword']").val()) {
                    $("input[name='newpassword2']").val("");
                    return '确认密码与密码不一致';
                }
            }
        });
        //监听提交
        form.on('submit(saveInfo)', function (data) {
            params = data.field;
            if($.trim(params.email)){
                var regex = /^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/g;
                if (!regex.test(params.email)){
                    layer.msg("请输入正确的电子邮箱！",{icon : 2},1000);
                    return false;
                }
            }
            submit($,params,userInfoUrl);
            setTimeout(function(){
                layui.index.render();
            },2000);
            return false;
        });
        form.on('submit(savePassword)', function (data) {
            params = data.field;
            if(params.newpassword != params.newpassword2){
                layer.msg("新密码和确认密码不一致！",{icon : 2},1000);
                return false;
            }
            submit($,params,userInfoUrl);
            setTimeout(function(){
                layui.index.render();
                //window.location.href = '/#/User/userInfo.html';
            },2000);
            return false;
        })
        if (password == '1') {
            element.tabChange('docDemoTabBrief', 'password');
            layer.open({
              title: '请先修改密码'
              ,content: '密码必须8到18位，且大写字母 小写字母 数字 特殊字符，四种包括两种,且不能出现空格'
            });    
        }
    });
    exports('basesetting/user/userInfo', {});
});

