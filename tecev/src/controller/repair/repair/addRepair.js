layui.define(function(exports){
    layui.use(['form','laydate'], function () {
        var form = layui.form,laydate = layui.laydate;

        //报修时间元素渲染

        laydate.render({
            elem: '#repair_date' //指定元素
            , type: 'datetime',
            calendar: true
            ,value: current_time
            ,max: max
            , trigger: 'click'
            , done: function (value, date) {
                if (date.hours == 0 && date.minutes == 0 && date.seconds == 0) {
                    layer.confirm('当前选择日期暂无具体时间,是否需要补充具体时间？', {
                        btn: ['是', '否'],
                        title: '是否需要补充时间',
                        closeBtn: 0
                    }, function (index) {
                        layer.close(index);
                        setTimeout(function () {
                            $("#repair_date").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });
        //监听提交
        form.on('submit(save)', function (data) {
            form.verify({
                name: function(value,item){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '报修人首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '报修人不能全为数字';
                    }
                },
                tel: function(value,item) {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '报修电话首尾不能出现下划线\'_\'';
                    }
                    if(!checkTel(value)){
                        return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                    }
                }
            });
            var params = data.field;
            params.from=1;
            submit($, params, addRepairUrl);
            return false;
        });
    });
    exports('controller/repair/repair/addRepair', {});
});
