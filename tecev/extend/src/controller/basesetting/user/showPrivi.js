layui.define(function(exports){
    layui.use(['form','formSelects','tipsType'], function() {
        var form = layui.form, $ = layui.jquery,formSelects = layui.formSelects,tipsType=layui.tipsType;
        //初始化tips
        tipsType.choose();

        //管理科室 多选框初始配置
        formSelects.render('department', selectParams(1));
        formSelects.btns('department',selectParams(2),selectParams(3));

        //监听提交
        form.on('submit(save)', function (data) {
            params = {};
            params.userid = $('input[name="userid"]').val();
            params.department = formSelects.value('department', 'valStr');
            params.action = 'managerhos';
            submit($,params,'getUserList');
            return false;
        });


    });
    exports('controller/basesetting/user/showPrivi', {});
});

