layui.define(function(exports){
    layui.use(['form','formSelects'], function(){
        var form = layui.form,formSelects = layui.formSelects;
        form.render();
        //科室初始化
        formSelects.render('addBoxDepartment', selectParams(1));
        formSelects.btns('addBoxDepartment', selectParams(2), selectParams(3));
        //设备 多选框初始配置
        formSelects.render('addBoxAssets', selectParams(1));
        formSelects.btns('addBoxAssets', selectParams(2));
        //监听联动变化事件
        formSelects.on('addBoxDepartment', function () {
            var box_id = $('input[name="id"]').val();
            setTimeout(function () {
                var departid = formSelects.value('addBoxDepartment', 'valStr');
                if (!departid) {
                    //local模式
                    formSelects.data('addBoxAssets', 'local', {arr: []});
                } else {
                    //server模式
                    formSelects.data('addBoxAssets', 'server', {
                        url: "editBox?action=getAssets&id="+box_id+"&did=" + departid
                    });
                }
            }, 100)
        });

        //监听提交
        form.on('submit(editBox)', function(data){
            var url = admin_name+'/Box/editBox';
            params = data.field;
            submit($,params,url);
            return false;
        });
    });
    exports('controller/archives/box/editBox', {});
});