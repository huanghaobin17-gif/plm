layui.use(['layer', 'form', 'table'], function () {
    var layer = layui.layer
        , table = layui.table
        , form = layui.form;

    //初始化
    form.render();
    //初始化下方导航栏菜单
    console.log(getThisDate(1));
    menuListSpread();

    $("#give_back_time").datetimePicker({
        min:getThisDate(),
        onClose:function (val) {
            var date=val.displayValue;
            var give_back_time = $('#give_back_time');
            if (apply_borrow_back_start_time && apply_borrow_back_end_time) {
                var back_start = apply_borrow_back_start_time.split(':');
                var back_end = apply_borrow_back_end_time.split(':');
                if (back_start[0] <= date[3] && date[3] <= back_end[0]) {
                    //最后一个小时 分钟不能大于设置的分钟否则不合理
                    if (date[3] === parseInt(back_end[0]) && (back_end[1] < date[4])) {
                        $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                        give_back_time.addClass('border-red');
                        return false;
                    }
                    //第一个小时 分钟不能小于设置的分钟否则不合理
                    if (date[3] === parseInt(back_start[0]) && (back_start[1] > date[4] )) {
                        $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                        give_back_time.addClass('border-red');
                        return false;
                    }
                } else {
                    $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                    give_back_time.addClass('border-red');
                    return false;
                }
            }
            give_back_time.removeClass('border-red');
        }
    });

    //监听通知-确认借入
    form.on('submit(giveBackCheck)', function (data) {
        var params = data.field;
        if (params.give_back_time) {
            if ($('.border-red').length !== 0) {
                $.toptip('请输入正确的归还时间', 'error');
                return false;
            }
        } else {
            $.toptip('请选择归还时间', 'error');
            return false;
        }
        submit(params, giveBackCheckUrl,mobile_name+'/Borrow/giveBackCheckList');
        return false;
    });




});