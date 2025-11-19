layui.use(['form'], function () {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();
    //监听提交
    form.on('submit(save)', function (data) {
        var params = data.field;
        if(!$.trim(params.scrap_reason)){
            $.toptip('请输入报废原因', 'error');
            return false;
        }
        if(params.type == 'edit'){
            layer.confirm('确认申请重审吗?',{icon: 3, title:'报废申请提交确认'}, function(index){
                submit(params, url, mobile_name+'/Scrap/getApplyList.html');
                layer.close(index);
            });
        }else{
            submit(params, url, mobile_name+'/Scrap/getApplyList.html');
        }
        return false;
    });

    //监听通知-结束进程
    form.on('submit(over)', function (data) {
        var params = {};
        params.type = 'end';
        params.scrid = $('input[name="scrid"]').val();
        layer.confirm('确认结束该报废进程吗?',{icon: 3, title:'报废申请提交确认'}, function(index){
            submit(params, url, mobile_name+'/Scrap/getApplyList.html');
            layer.close(index);
        });
        return false;
    });
});