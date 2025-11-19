layui.use(['layer', 'form', 'table'], function () {
    var layer = layui.layer
        , table = layui.table
        , form = layui.form;

    //初始化
    form.render();
    //初始化下方导航栏菜单
    menuListSpread();
    $("#reminder_time").datetimePicker({});


    //监听通知-确认借入
    form.on('submit(reminderCheck)', function (data) {
        var params = data.field;
        params.action = 'Reminder_acceptance';
        params.reminder_time = $("#reminder_time").val();
        params.borid = $("#borid").val();
        if (!params.reminder_time) {
            $.toptip('请补充确认归还时间', 'error');
            return false;
        }
        layer.confirm('确认该设备已归还，并提醒对方验收设备？',{icon: 3, title:'微信信息发送提醒'}, function(index){
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: ReminderCheckUrl,
                data: params,
                dataType: "json",
                async: true,
                success: function (res) {
                    if (res.code === 200) {
                        layer.msg(res.msg);
                    }
                    else {
                        layer.msg(res.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
                complete:function () {
                    layer.close(index);
                }
            });
        });
        return false;
    });


});