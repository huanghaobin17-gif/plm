layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'tipsType'], function () {
        var layer = layui.layer
            , laydate = layui.laydate
            , tipsType = layui.tipsType
            , table = layui.table
            , form = layui.form;

        //初始化
        form.render();
        tipsType.choose();


        //转换静态表格
        table.init('subsidiary_table', {
            limit: 10 //注意：请务必确保 limit 参数（默认：10）是与你服务端限定的数据条数一致
            //支持所有基础参数
        });

        laydate.render({
            elem: '#estimate_back',
            type: 'datetime',
            format: 'yyyy-MM-dd HH:mm',
            min: borrow_in_time,
            done: function (value, date, endDate) {
                var estimate_back = $('#estimate_back');
                if (date.hours == 0 && date.minutes == 0 && date.seconds == 0) {
                    layer.confirm('当前选择日期暂无具体时间,请选择具体时间。', {
                        btn: ['继续选择', '暂不填写'],
                        title: '请补充具体时间',
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#estimate_back").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                        $("#estimate_back").val("");
                    });
                } else {
                    if (apply_borrow_back_start_time && apply_borrow_back_end_time) {
                        var back_start = apply_borrow_back_start_time.split(':');
                        var back_end = apply_borrow_back_end_time.split(':');
                        if (back_start[0] <= date.hours && date.hours <= back_end[0]) {
                            //最后一个小时 分钟不能大于设置的分钟否则不合理
                            if (date.hours === parseInt(back_end[0]) && (back_end[1] < date.minutes)) {
                                layer.msg('归还时间范围' + apply_borrow_back_start_time + '至' + apply_borrow_back_end_time, {icon: 2});
                                estimate_back.addClass('border-red');
                                return false;
                            }
                            //第一个小时 分钟不能小于设置的分钟否则不合理
                            if (date.hours === parseInt(back_start[0]) && (back_start[1] > date.minutes )) {
                                layer.msg('归还时间范围' + apply_borrow_back_start_time + '至' + apply_borrow_back_end_time, {icon: 2});
                                estimate_back.addClass('border-red');
                                return false;
                            }
                        } else {
                            layer.msg('归还时间范围 ' + apply_borrow_back_start_time + ' 至 ' + apply_borrow_back_end_time, {icon: 2});
                            estimate_back.addClass('border-red');
                            return false;
                        }
                    }
                    estimate_back.removeClass('border-red');
                }
            }

        });


        //新增操作
        form.on('submit(addApplyBorrow)', function (data) {
            var params = data.field;
            if (!$.trim(params.borrow_reason)) {
                layer.msg('请补充借调原因', {icon: 2});
                return false;
            }
            if (params.estimate_back) {
                if ($('.border-red').length !== 0) {
                    layer.msg('请选择正确的归还时间', {icon: 2});
                    return false;
                }
            } else {
                layer.msg('请选择归还时间', {icon: 2});
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
                    submit($, params, applyBorrowUrl);
                    layer.close(index);
                });
            }else{
                submit($, params, applyBorrowUrl);
            }
            return false;
        });
    });
    exports('controller/assets/borrow/applyBorrow', {});
});