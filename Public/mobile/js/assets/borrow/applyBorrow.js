layui.use(['layer', 'form', 'table'], function () {
    var layer = layui.layer
        , table = layui.table
        , form = layui.form;

    //初始化
    form.render();
    //初始化下方导航栏菜单
    menuListSpread();
    $("#estimate_back").datetimePicker({
        min:getThisDate(),
        onClose:function (val) {
            console.log(val.displayValue);
            var date=val.displayValue;
            var estimate_back = $('#estimate_back');
            if (apply_borrow_back_start_time && apply_borrow_back_end_time) {
                var back_start = apply_borrow_back_start_time.split(':');
                var back_end = apply_borrow_back_end_time.split(':');
                if (back_start[0] <= date[3] && date[3] <= back_end[0]) {
                    //最后一个小时 分钟不能大于设置的分钟否则不合理
                    if (date[3] === parseInt(back_end[0]) && (back_end[1] < date[4])) {
                        $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                        estimate_back.addClass('border-red');
                        return false;
                    }
                    //第一个小时 分钟不能小于设置的分钟否则不合理
                    if (date[3] === parseInt(back_start[0]) && (back_start[1] > date[4] )) {
                        $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                        estimate_back.addClass('border-red');
                        return false;
                    }
                } else {
                    $.toptip('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, 'error');
                    estimate_back.addClass('border-red');
                    return false;
                }
            }
            estimate_back.removeClass('border-red');
        }
    });



    //监听通知-借入申请
    form.on('submit(confirm)', function (data) {
        var params = data.field;
        var apply_departid=$('select[name="apply_departid"]');
        if(apply_departid.length>0){
            if (!params.apply_departid) {
                $.toptip('请选择申请科室', 'error');
                return false;
            }
        }
        if (!params.borrow_reason) {
            $.toptip('请补充借调原因', 'error');
            return false;
        }
        if (params.estimate_back) {
            if ($('.border-red').length !== 0) {
                $.toptip('请选择正确的归还时间', 'error');
                return false;
            }
        } else {
            $.toptip('请选择归还时间', 'error');
            return false;
        }
        var checkStatus = table.checkStatus('subsidiaryData');
        var length = checkStatus.data.length;
        if (length > 0) {
            var assid = '';
            for (var i = 0; i < length; i++) {
                assid += checkStatus.data[i]['assid'] + ',';
            }
            params.subsidiary_assid = assid.substring(0, assid.length - 1);
            layer.confirm('是否连同附属设备一同借调?', function(index){
                submit(params, applyBorrowUrl,mobile_name+'/Borrow/borrowAssetsList');
                layer.close(index);
            });
        }else{
            submit(params, applyBorrowUrl,mobile_name+'/Borrow/borrowAssetsList');
        }
        return false;
    });

    //监听通知-结束进程
    form.on('submit(over)', function (data) {
        var params = {};
        params.type = 'end';
        params.borid = $('input[name="borid"]').val();
        layer.confirm('确认结束该借调进程吗?', function(index){
            submit(params, applyBorrowUrl,mobile_name+'/Borrow/borrowAssetsList');
            layer.close(index);
        });
        return false;
    });

    //转换静态表格
    table.init('subsidiary_table', {
        limit: 10 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
        //支持所有基础参数
    });

    function getThisDate() {
        var now=new Date();
        return now.getFullYear()+"-"+((now.getMonth()+1)<10?"0":"")+(now.getMonth()+1)+"-"+(now.getDate()<10?"0":"")+now.getDate();
    }


});