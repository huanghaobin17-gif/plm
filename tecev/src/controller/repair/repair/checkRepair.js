layui.define(function(exports){
    layui.use(['form','laydate'], function () {
        var form = layui.form, $ = layui.jquery,laydate = layui.laydate;
        //报修时间元素渲染

        var nowDate = new Date();
        laydate.render({
            elem: '#check_date' //指定元素
            , type: 'datetime',
            calendar: true
            ,min:over_time
            //min: nowDate.getFullYear() + '-' + (nowDate.getMonth() + 1) < 10 ? '0' + (nowDate.getMonth() + 1) : (nowDate.getMonth() + 1) + '-' + (nowDate.getDate() + 1) < 10 ? '0' + (nowDate.getDate() + 1) : (nowDate.getDate() + 1)
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
                            $("#check_date").click();
                        }, 500)
                    }, function (index) {
                        layer.close(index);
                    });
                }
            }
        });
        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            submit($, params, params.action);
            return false;
        });
    });
    exports('controller/repair/repair/checkRepair', {});
});
