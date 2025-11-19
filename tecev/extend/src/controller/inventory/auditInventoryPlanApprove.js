layui.define(function (exports) {
    layui.use(['form', 'table', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, tablePlug = layui.tablePlug;

        var tableData = [];

        initData()

        // 表格
        table.render({
            elem: '#tableList'
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
            },
            size:'sm'
            ,defaultToolbar:[]
            , page: false //开启分页
            , cols: [[
                {field: 'assetnum', title: '设备编码', minWidth: 180, align: 'center'}
                , {
                    field: 'assets',
                    title: '设备名称',
                    minWidth: 180,
                    align: 'center'
                }
                , {
                    field: 'departid', title: '使用科室', minWidth: 130, align: 'center',
                    templet: function (d) {
                        const findItem = department.find(item=>item.departid === d.departid)
                        if (findItem){
                            return findItem['department']
                        }else {
                            return '未知'
                        }
                    }
                }
                , {
                    field: 'address', title: '设备使用位置', minWidth: 120, align: 'center'
                }
                , {
                    field: 'is_plan', title: '账面状态', width: 100, align: 'center',
                    templet: function (d) {
                        return d.is_plan === '0' ? '不存在' : '存在'
                    }
                }
                , {
                    field: 'inventory_status', title: '实盘状态', minWidth: 90, align: 'center',
                    templet: function (d) {
                        switch (d.inventory_status){
                            case "0":
                                return '未盘点'
                            case "1":
                                return '正常'
                            case "2":
                                return '异常'
                        }
                    }
                }
                , {
                    field: 'reason', title: '原因', minWidth: 150, align: 'center'
                }
                , {field: 'result', title: '处理结果', minWidth: 130, align: 'center',
                    templet: function (d) {
                    if (d.inventory_status === '1'){
                        return ''
                    }else if (d.inventory_status === '2' && d.is_plan === '1'){
                        return '报废'
                    }else if (d.inventory_status === '2' && d.is_plan === '0'){
                        return '盘盈'
                    }
                    return ''
                    }
                    }
                , {
                    field: 'financeid', title: '财务分类', minWidth: 110, align: 'center',
                    templet: function (d) {
                        return assetsFinance[d.financeid]
                    }
                }
            ]],
            data: tableData
        });

        // 保存
        form.on('submit(save)', function (obj) {
            const field = obj.field
            const data = {
                "inventory_plan_id":inventoryPlanData['inventory_plan']['inventory_plan_id'],
                "is_adopt":field.is_adopt,
                "remark":field.remark
            }
            // console.log(data)
            submit($,data, auditInventoryPlanApproveUrl);
            return false;
        });

        // 初始化数据
        function initData(){
            $('.inventory_plan_no').html(inventoryPlanData['inventory_plan']['inventory_plan_no'])
            $('.inventory_plan_name').html(inventoryPlanData['inventory_plan']['inventory_plan_name'])
            $('.inventory_plan_start_time').html(inventoryPlanData['inventory_plan']['inventory_plan_start_time'])
            $('.inventory_plan_end_time').html(inventoryPlanData['inventory_plan']['inventory_plan_end_time'])
            $('.inventory_plan_status_name').html(inventoryPlanData['inventory_plan']['inventory_plan_status_name'])
            $('.inventory_users').html(inventoryPlanData['inventory_plan']['inventory_users'].join(','))
            tableData = [...inventoryPlanData['inventory_plan_assets']]
        }
    });
    exports('controller/inventory/auditInventoryPlanApprove', {});
});