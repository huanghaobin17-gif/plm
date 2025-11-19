layui.use(['form','laydate'], function () {
    var form = layui.form,laydate = layui.laydate;

    //初始化下方导航栏菜单
    menuListSpread();
    //转科时间元素渲染
    laydate.render({
        elem: '#transferdate' //指定元素
        ,min: today
    });
    //监听提交
    form.on('submit(save)', function (data) {
        var params = data.field;
        if(params.type == 'save'){
            layer.confirm('确认申请重审吗?',{icon: 3, title:'转科申请提交确认'}, function(index){
                submit(params, url,mobile_name+'/Transfer/getList.html');
                layer.close(index);
            });
        }else{
            submit(params, url,mobile_name+'/Transfer/getList.html');
        }
        return false;
    });
    //监听结束进程
    form.on('submit(over)', function (data) {
        var params = data.field;
        params.type = 'over';
        layer.confirm('确认结束该转科进程吗?',{icon: 3, title:'转科申请提交确认'}, function(index){
            submit(params, url,mobile_name+'/Transfer/getList.html');
            layer.close(index);
        });
        return false;
    });

    //选择科室
    $(".departments").select({
        title: "选择转入科室",
        closeText:'取消',
        // items: [
        //     {
        //         title: "iPhone 7",
        //         value: "009"
        //     }
        // ],
        items: departments,
        onClose:function(d){
            var value = d.data.values,title = d.data.titles;
            $("input[name='departid']").val(value);
            $("input[name='department']").val(title);
        }
    });
});