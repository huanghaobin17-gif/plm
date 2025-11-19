var gloabOptions = {};
layui.define(function (exports) {
    layui.use(['form', 'table', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, tablePlug = layui.tablePlug

        layer.config(layerParmas());

        form.render();
        //第一个实例
        table.render({
            elem: '#inventoryPlanList'
            , limits: [100, 500, 1000]
            , limit: 100
            , loading: true
            , height: 'full-200' //高度最大化减去差值
            , url: inventoryPlanListUrl //数据接口
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
            },
            toolbar: '#LAY-InventoryPlan-InventoryPlan-inventoryPlanListToolbar',
            defaultToolbar: []
            , cols: [[
                {type: 'checkbox', fixed: 'left'},
                {
                    field: 'inventory_plan_id', title: '序号', width: 60, fixed: 'left', align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX
                    }
                }
                , {field: 'inventory_plan_no', fixed: 'left', title: '盘点单号', width: 160, align: 'center'}
                , {field: 'inventory_plan_name', fixed: 'left', title: '盘点名称', width: 160, align: 'center'}
                , {field: 'inventory_plan_status_name', title: '状态', width: 160, align: 'center',}
                , {field: 'inventory_plan_start_time', title: '开始时间', width: 160, align: 'center'}
                , {field: 'inventory_plan_end_time', title: '结束时间', width: 160, align: 'center'}
                , {
                    field: 'inventory_plan_hour', title: '时长', width: 160, align: 'center',
                    templet: function (d) {
                        if (d.inventory_plan_status != 4) {
                            return "";
                        }
                        var startDate = new Date(d.inventory_plan_start_time);
                        var endDate = new Date(d.inventory_plan_end_time);
                        if (isNaN(startDate) || isNaN(endDate)) {
                            return "";
                        }
                        var diff = endDate - startDate;

                        var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        diff -= days * (1000 * 60 * 60 * 24);

                        var hours = Math.floor(diff / (1000 * 60 * 60));
                        diff -= hours * (1000 * 60 * 60);

                        var mins = Math.floor(diff / (1000 * 60));
                        diff -= mins * (1000 * 60);

                        var seconds = Math.floor(diff / (1000));
                        diff -= seconds * (1000);
                        return `${days}天${hours}时${mins}分${seconds}秒`;
                    }
                }
                , {field: 'inventory_users', title: '盘点员', width: 160, align: 'center'}
                , {field: 'inventory_plan_asset_count', title: '计划数', width: 100, align: 'center'}
                , {field: 'inventory_plan_asset_status_not_count', title: '未盘点', width: 100, align: 'center'}
                , {field: 'inventory_plan_asset_status_normal_count', title: '正常数', width: 100, align: 'center'}
                , {field: 'inventory_plan_asset_status_abnormal_count', title: '异常数', width: 100, align: 'center'}
                , {field: 'is_push_name', title: '是否自动', width: 160, align: 'center'}
                , {field: 'remark', title: '备注', width: 160, align: 'center'}
                , {field: 'push_system_name', title: '下游系统名称', width: 160, align: 'center'}
                , {field: 'push_status_name', title: '推送状态', width: 160, align: 'center'}
                , {field: 'error_msg', title: '推送报错信息', width: 160, align: 'center'}
                , {field: 'push_time', title: '下推时间', width: 160, align: 'center'}
                , {field: 'receive_status_name', title: '接收状态', width: 160, align: 'center',}
                , {field: 'error_msg', title: '报错信息', width: 160, align: 'center'}
                , {field: 'receive_time', title: '接收时间', width: 160, align: 'center'}
                , {field: 'add_time', title: '创建时间', width: 160, align: 'center'}
                , {field: 'add_user', title: '创建人', width: 160, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 165,
                    align: 'center'
                }
            ]]
        });
        //监听表格上方工具条
        table.on('toolbar(inventoryPlanList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var checkStatus = table.checkStatus('inventoryPlanList');
            const {data: selected} = checkStatus
            const inventory_plan_id_arr = selected.map(item => item.inventory_plan_id)
            switch (layEvent) {
                case 'addInventoryPlan':
                    top.layer.open({
                        type: 2,
                        title: '新增盘点计划',
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table_reload()
                        }
                    });
                    break;
                case 'batchDelInventoryPlan':
                    console.log(selected)
                    if (selected.length === 0) {
                        layer.msg('请选择记录', {icon: 2})
                        return false
                    }
                    layer.confirm(`是否删除${selected.length}条盘点记录`, {icon: 3, title: "提示"}, function () {
                        submit($, {inventory_plan_ids: inventory_plan_id_arr.join(',')}, admin_name + '/InventoryPlan/batchDelInventoryPlan');
                    });
                    break;
                case 'batchReleaseInventoryPlan':
                    console.log(selected)
                    if (selected.length === 0) {
                        layer.msg('请选择记录', {icon: 2})
                        return false
                    }
                    layer.confirm(`是否发布${selected.length}条盘点记录`, {icon: 3, title: "提示"}, function () {
                        submit($, {inventory_plan_ids: inventory_plan_id_arr.join(',')}, admin_name + '/InventoryPlan/batchReleaseInventoryPlan');
                    });
                    break;
                case 'batchResetPushInventoryPlan':
                    console.log(selected)
                    if (selected.length === 0) {
                        layer.msg('请选择记录', {icon: 2})
                        return false
                    }
                    layer.confirm(`是否重新推送${selected.length}条盘点记录`, {
                        icon: 3,
                        title: "提示"
                    }, function (index) {
                        // submit(params, approveBorrowUrl,mobile_name+'/Notin/approve.html');
                        layer.close(index);
                    });
                    break;

            }
        });

        function submit($, params, url) {
            $.ajax({
                timeout: 30000,
                type: "POST",
                url: url,
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status === 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table_reload()
                        })
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                },
            });
        }

        //监听表格行的工具条
        table.on('tool(inventoryPlanList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showInventoryPlan':
                    top.layer.open({
                        type: 2,
                        // title: '【' + rows.assets + '】设备标签页',
                        title: '详情',
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'editInventoryPlan':
                    top.layer.open({
                        type: 2,
                        // title: '【' + rows.assets + '】设备标签页',
                        title: $(this).html(),
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table_reload()
                        }
                    });
                    break;
                case 'saveOrEndInventoryPlan':
                    top.layer.open({
                        type: 2,
                        // title: '【' + rows.assets + '】设备标签页',
                        title: $(this).html(),
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table_reload()
                        }
                    });
                    break;
            }
        });


        function table_reload() {
            table.reload('inventoryPlanList', {
                url: inventoryPlanListUrl
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        }

        // 搜索
        form.on('submit(inventoryPlanListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            table_reload()
            return false;
        });
    });
    exports('inventory/inventoryPlanList', {});
});




