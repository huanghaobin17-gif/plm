layui.define(function(exports){
    layui.use(['form','formSelects'], function(){
        var form = layui.form,formSelects = layui.formSelects;
        //科室初始化
        formSelects.render('addBoxDepartment', selectParams(1));
        formSelects.btns('addBoxDepartment', selectParams(2), selectParams(3));
        //设备 多选框初始配置
        formSelects.render('addBoxAssets', selectParams(1));
        formSelects.btns('addBoxAssets', selectParams(2));
        //监听联动变化事件
        formSelects.on('addBoxDepartment', function () {
            setTimeout(function () {
                var departid = formSelects.value('addBoxDepartment', 'valStr');
                if (!departid) {
                    //local模式
                    formSelects.data('addBoxAssets', 'local', {arr: []});
                } else {
                    //server模式
                    formSelects.data('addBoxAssets', 'server', {
                        url: "addBox?action=getAssets&did=" + departid
                    });
                }
            }, 100)
        });

        //监听提交
        form.on('submit(addBox)', function(data){
            var url = admin_name+'/Box/addBox';
            params = data.field;
            submit($,params,url);
            return false;
        });
    });
    exports('controller/archives/box/addBox', {});
});