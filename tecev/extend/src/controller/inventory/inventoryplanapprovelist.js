var gloabOptions = {};
layui.define(function (exports) {
    layui.use(['form', 'table', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        form.render();

        // 表格
        table.render({
            elem: '#inventoryplanapprovelist'
            , limits: [100, 500, 1000]
            , limit: 100
            , loading: true
            , height: 'full-200' //高度最大化减去差值
            , url: inventoryPlanApproveListUrl //数据接口
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
            defaultToolbar: []
            , data:[]
            , cols: [[
                {
                    field: 'repid', title: '序号', width: 60, align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX
                    }
                }
                , {field: 'inventory_plan_no', title: '盘点单号', minWidth: 160, align: 'center'}
                , {field: 'inventory_plan_name', title: '盘点名称', minWidth: 160, align: 'center'}
                , {field: 'app_user_status', title: '审批流程&状态', minWidth: 200, align: 'center'}
                , {field: 'inventory_plan_start_time', title: '开始时间', minWidth: 110, align: 'center'}
                , {field: 'inventory_plan_end_time', title: '结束时间', minWidth: 110, align: 'center'}
                , {field: 'add_time', title: '创建时间', minWidth: 110, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    width: 150,
                    align: 'center'
                }
            ]]
        });

        //监听工具条
        table.on('tool(inventoryplanapprovelist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showInventoryPlan':
                    top.layer.open({
                        type: 2,
                        title: '详情',
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'auditInventoryPlanApprove':
                    top.layer.open({
                        type: 2,
                        title: $(this).html(),
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url],
                        end: function(){
                            table_reload()
                        }
                    });
                    break;
            }
        });

        function table_reload(){
            table.reload('inventoryplanapprovelist', {
                url: inventoryPlanApproveListUrl
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        }

        //监听提交
        form.on('submit(inventoryplanapprovelistSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            table_reload()
            return false;
        });
    });
    exports('inventory/inventoryplanapprovelist', {});
});




