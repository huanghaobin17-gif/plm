layui.use('form', function() {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();

    /*确认并提交数据*/
    form.on('submit(yes)', function(data){
        var params = data.field;
        params.res = 1;
        submit(params, url,mobile_name+'/Transfer/checkLists.html');
        return false;
    });

    form.on('submit(no)', function(data){
        var params = data.field;
        params.res = 2;
        submit(params, url,mobile_name+'/Transfer/checkLists.html');
        return false;
    });
});