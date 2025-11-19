layui.define(function(exports){
    layui.use(['form', 'laydate'], function () {
        var form = layui.form,
            laydate = layui.laydate;

        //报修时间元素渲染
        laydate.render({
            elem: '#partsOutWareApplyAddtime' //指定元素
            ,max:now_date
            , type: 'datetime',
            calendar: true
            , trigger: 'click'
            , done: function (value, date) {
                if (date.hours === 0 && date.minutes === 0 && date.seconds === 0) {
                    layer.confirm('当前选择日期暂无具体时间,是否需要补充具体时间？', {
                        btn: ['是', '否'],
                        title: '是否需要补充时间',
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#partsOutWareApplyAddtime").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });
        //监听提交
        form.verify({
            leader: function (value) {
                if (!value) {
                    return '请选择领用人';
                }
            },
            addtime: function (value) {
                if (!value) {
                    return '请选择选择出库日期';
                }
            }
        });


        form.on('submit(addPartsOutWareApply)', function (data) {
            var params = data.field;
            var parts_tr = $('.parts_tr');
            if (parts_tr.length === 0) {
                layer.msg('异常申请单', {icon: 2, time: 3000});
                return false;
            }
            var error = false;
            var msg = '';
            var parts = '', parts_model = '', sum = '', apply_sum = '';
            $.each(parts_tr, function (key, value) {
                var parts_val = $(this).find('input[name="parts"]').val();
                var parts_model_val = $(this).find('input[name="parts_model"]').val();
                var apply_sum_val = $(this).find('input[name="apply_sum"]').val();
                var sum_val = $(this).find('input[name="sum"]').val();
                if (!check_num(sum_val)) {
                    error = true;
                    msg = '配件：' + parts_val + ' 请输入合理的出库数量';
                    return false;
                } else {
                    if (parseInt(sum_val) < parseInt(apply_sum_val)) {
                        error = true;
                        msg = '出库配件：' + parts_val + ' 数量不能少于申请的数量' + apply_sum_val;
                        return false;
                    }
                }
                parts += parts_val + '|';
                parts_model += parts_model_val + '|';
                sum += sum_val + '|';
                apply_sum += apply_sum_val + '|';
            });
            if (error) {
                layer.msg(msg, {icon: 2, time: 3000});
                return false;
            }
            params.parts = parts;
            params.parts_model = parts_model;
            params.sum = sum;
            params.apply_sum = apply_sum;
            params.action = 'partsOutWareApply';
            submit($, params, partsOutWareApplyUrl);
            return false;
        });
    });
    exports('controller/repair/repairParts/partsOutWareApply', {});
});
