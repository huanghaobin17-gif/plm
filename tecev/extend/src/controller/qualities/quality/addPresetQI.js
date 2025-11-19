layui.define(function(exports){
    layui.use(['layer', 'form', 'element', 'table', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, tablePlug = layui.tablePlug;
        //先更新页面部分需要提前渲染的控件
        form.render();

        //数据表格
        table.render({
            elem: '#getBasisList'
            , limits: [10, 20]
            , loading: true
            , limit: 10
            , url: addPresetQIUrl //数据接口
            , toolbar: '#getBasisListToolbar'
            , defaultToolbar: false
            , where: {action: 'getBasisList'}
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {
                pageName: 'page' //页码的参数名称，默认：page
                , limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , cols: [[
                {
                    field: 'qdbid', title: '序号', width: 65, align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'basis', title: '检测依据标题', width: 300, align: 'center'},
                {field: 'adduser', title: '发布者', width: 140, align: 'center'},
                {field: 'adddate', title: '发布时间', width: 180, align: 'center'},
                {field: 'operation', title: '操作', minWidth: 150, align: 'center', fixed: 'right'}
            ]]
        });

        //监听操作
        table.on('tool(getBasisListData)', function (obj) {
            var rows = obj.data;
            var layEvent = obj.event;
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showBasis'://显示依据内容
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.basis + '】依据内容',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [addPresetQIUrl + '?qdbid=' + rows.qdbid + '&action=showBasis'],
                        end: function () {
                            if (flag) {
                                table.reload('getBasisList', {
                                    url: addPresetQIUrl
                                    , where: {action: 'getBasisList'}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'editBasis'://编辑依据
                    top.layer.open({
                        type: 2,
                        title: '编辑【' + rows.basis + '】依据内容',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [addPresetQIUrl + '?qdbid=' + rows.qdbid + '&action=editBasis'],
                        end: function () {
                            if (flag) {
                                table.reload('getBasisList', {
                                    url: addPresetQIUrl
                                    , where: {action: 'getBasisList'}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'deleteBasis'://删除依据
                    layer.confirm('检测依据删除后无法恢复，确定删除吗？', {
                        icon: 3,
                        title: $(this).html() + '【' + rows.basis + '】'
                    }, function (index) {
                        var params = {};
                        params['action'] = 'deleteBasis';
                        params['qdbid'] = rows.qdbid;
                        $.ajax({
                            type: "POST",
                            url: addPresetQIUrl,
                            data: params,
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                layer.closeAll('loading');
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('getBasisList', {
                                            url: addPresetQIUrl
                                            , where: {action: 'getBasisList'}
                                            , page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                } else {
                                    layer.msg(data.msg, {icon: 2});
                                }
                            },
                            //调用出错执行的函数
                            error: function () {
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 5});
                            }
                        });
                        layer.close(index);
                    });
                    break;
            }
        });

        table.on('toolbar(getBasisListData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addBasis'://新增检测依据
                    top.layer.open({
                        id: 'addnotices',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        closeBtn: 1,
                        content: [addPresetQIUrl + '?action=addBasis'],
                        end: function () {
                            if (flag) {
                                table.reload('getBasisList', {
                                    url: addPresetQIUrl
                                    , where: {action: 'getBasisList'}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            //心率
            var heartRate = data.field['heartRate'].split("\n");
            $.each(heartRate,function(k,v){
                heartRate[k]=$.trim(v);
            });
            //呼吸率
            var breathRate = data.field['breathRate'].split("\n");
            $.each(breathRate,function(k,v){
                breathRate[k]=$.trim(v);
            });
            //无创血压设定值
            var pressure = data.field['pressure'].split("\n");
            $.each(pressure,function(k,v){
                pressure[k]=$.trim(v);
            });
            //血氧饱和度设定值
            var BOS = data.field['BOS'].split("\n");
            $.each(BOS,function(k,v){
                BOS[k]=$.trim(v);
            });
            //流程检测设定值
            var flow = data.field['flow'].split("\n");
            $.each(flow,function(k,v){
                flow[k]=$.trim(v);
            });
            //阻塞报警检测设定值
            var block = data.field['block'].split("\n");
            $.each(block,function(k,v){
                block[k]=$.trim(v);
            });
            //释放能量设定值
            var energesis = data.field['energesis'].split("\n");
            $.each(energesis,function(k,v){
                energesis[k]=$.trim(v);
            });
            //充电时间设定值
            var charge = data.field['charge'].split("\n");
            $.each(charge,function(k,v){
                charge[k]=$.trim(v);
            });


            //潮气量
            var humidity = data.field['humidity'].split("\n");
            $.each(humidity,function(k,v){
                humidity[k]=$.trim(v);
            });
            //强制通气频率
            var aeration = data.field['aeration'].split("\n");
            $.each(aeration,function(k,v){
                aeration[k]=$.trim(v);
            });
            //吸入氧浓度
            var IOI = data.field['IOI'].split("\n");
            $.each(IOI,function(k,v){
                IOI[k]=$.trim(v);
            });
            //吸气压力水平
            var IPAP = data.field['IPAP'].split("\n");
            $.each(IPAP,function(k,v){
                IPAP[k]=$.trim(v);
            });
            //呼气末正压
            var PEEP = data.field['PEEP'].split("\n");
            $.each(PEEP,function(k,v){
                PEEP[k]=$.trim(v);
            });
            //单极电切
            var Unipolar_cutting = data.field['Unipolar_cutting'].split("\n");
            $.each(Unipolar_cutting,function(k,v){
                Unipolar_cutting[k]=$.trim(v);
            });
            //单极电凝
            var Unipolar_coagulation = data.field['Unipolar_coagulation'].split("\n");
            $.each(Unipolar_coagulation,function(k,v){
                Unipolar_coagulation[k]=$.trim(v);
            });
            //双极电切
            var Bipolar_resection = data.field['Bipolar_resection'].split("\n");
            $.each(Bipolar_resection,function(k,v){
                Bipolar_resection[k]=$.trim(v);
            });
            //双极电凝
            var Bipolar_coagulation = data.field['Bipolar_coagulation'].split("\n");
            $.each(Bipolar_coagulation,function(k,v){
                Bipolar_coagulation[k]=$.trim(v);
            });

            params.heartRate = heartRate.join(',');
            params.breathRate = breathRate.join(',');
            params.pressure = pressure.join(',');
            params.BOS = BOS.join(',');
            params.flow = flow.join(',');
            params.block = block.join(',');
            params.energesis = energesis.join(',');
            params.charge = charge.join(',');
            params.humidity = humidity.join(',');
            params.aeration = aeration.join(',');
            params.IOI = IOI.join(',');
            params.IPAP = IPAP.join(',');
            params.PEEP = PEEP.join(',');
            params.Unipolar_cutting = Unipolar_cutting.join(',');
            params.Unipolar_coagulation = Unipolar_coagulation.join(',');
            params.Bipolar_resection = Bipolar_resection.join(',');
            params.Bipolar_coagulation = Bipolar_coagulation.join(',');
            submit($,params,'addPresetQI');
            return false;
        })
    });
    exports('controller/qualities/quality/addPresetQI', {});
});
