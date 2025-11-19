layui.define(function (exports) {
    //记录被纳入的设备
    var removedata = '';

    layui.use(['form', 'table', 'laydate', 'suggest', 'formSelects'], function () {
        var form = layui.form, table = layui.table, formSelects = layui.formSelects, suggest = layui.suggest,
            laydate = layui.laydate;

        // 日期时间选择器
        laydate.render({
            elem: '#ID-laydate-type-datetime',
            type: 'datetime'
        });
        // 日期时间选择器
        laydate.render({
            elem: '#ID-laydate-type-datetime1',
            type: 'datetime'
        });


        formSelects.render('inventory_users', selectParams(1));
        formSelects.btns('inventory_users', selectParams(2));
        //初始化搜索建议插件
        suggest.search();

        layui.form.render();
        //初始化设备列表
        table.render({
            elem: '#addAssetsList'
            , limit: 100
            , limits: [100, 500, 2000]
            , loading: true
            , where: {
                removedata: removedata,
                action: 'addAssetsList'
            },
            height: 'full-500',
            toolbar: '#toolbarDemo'
            , defaultToolbar: []
            , url: addInventoryPlanUrl //数据接口
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
                {type: 'checkbox', fixed: 'left'},
                {
                    field: 'assid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assnum', fixed: 'left', title: '设备编码', minWidth: 180, align: 'center'}
                , {field: 'assets', fixed: 'left', title: '设备名称', minWidth: 180, align: 'center'}
                , {field: 'model', title: '规格型号', width: 180, align: 'center'}
                , {field: 'department', title: '使用科室', minWidth: 150, align: 'center'}
                , {field: 'category', title: '设备分类', minWidth: 150, align: 'center'}
                , {field: 'address', title: '设备使用位置', width: 120, align: 'center'}
                , {field: 'financeid', title: '财务分类', width: 130, align: 'center'}
                , {field: 'is_bind_inventory_label_id', title: '绑定状态', width: 100, align: 'center'}
                , {field: 'status_name', title: '当前状态', width: 100, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', width: 80, align: 'center'}
            ]]
            , done: function (res) {
                $('.assetsListTotal').html('&nbsp;&nbsp;&nbsp;查询到的设备列表(' + (res.total ? res.total : 0) + '台)');

            }
        });

        var existsHos = [];
        //监听设备列表工具条
        table.on('tool(addAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'add':
                    if (existsHos.length === 0) {
                        existsHos.push(rows.hospital_id);
                    } else {
                        if ($.inArray(rows.hospital_id, existsHos) < 0) {
                            layer.msg('请选择同一个医院的设备！', {icon: 2, time: 2000});
                            return false;
                        }
                    }
                    removedata += ',' + rows.assnum;
                    if (removedata.substr(0, 1) === ',') {
                        removedata = removedata.substr(1);
                    }
                    table.reload('addAssetsList', {
                        where: {
                            action: 'addAssetsList',
                            removedata: removedata
                        }
                    });
                    table.reload('delAssetsList', {
                        where: {
                            action: 'delAssetsList',
                            removedata: removedata
                        }
                    });
                    break;
            }
        });

        //初始化已纳入设备列表
        table.render({
            elem: '#delAssetsList'
            , limit: 99999
            // , limits: [100, 500, 2000]
            , loading: true
            , where: {
                removedata: removedata,
                action: 'delAssetsList'
            },
            height: 'full-500'
            , url: addInventoryPlanUrl //数据接口
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
            , cols: [[
                {
                    field: 'assid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assnum', fixed: 'left', title: '设备编码', minWidth: 180, align: 'center'}
                , {field: 'assets', fixed: 'left', title: '设备名称', minWidth: 180, align: 'center'}
                , {field: 'model', title: '规格型号', width: 180, align: 'center'}
                , {field: 'department', title: '使用科室', minWidth: 150, align: 'center'}
                , {field: 'category', title: '设备分类', minWidth: 150, align: 'center'}
                , {field: 'address', title: '设备使用位置', width: 120, align: 'center'}
                , {field: 'financeid', title: '财务分类', width: 130, align: 'center'}
                , {field: 'is_bind_inventory_label_id', title: '绑定状态', width: 100, align: 'center'}
                , {field: 'status_name', title: '当前状态', width: 100, align: 'center'}
                , {field: 'operation', title: '操作', fixed: 'right', width: 80, align: 'center'}
            ]]
            , done: function (res) {
                $('.delAssetsistTotal').html('&nbsp;&nbsp;&nbsp;已纳入计划设备列表(' + res.total + '台)');
            }
        });

        //监听已纳入设备列表工具条
        table.on('tool(delAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'del':
                    removedata = removedata.split(',');
                    removedata.splice($.inArray(rows.assnum, removedata), 1);
                    removedata = removedata.join(",");
                    if (!removedata) {
                        existsHos = [];
                    }
                    table.reload('addAssetsList', {
                        where: {
                            action: 'addAssetsList',
                            removedata: removedata
                        }
                    });
                    table.reload('delAssetsList', {
                        where: {
                            action: 'delAssetsList',
                            removedata: removedata
                        }
                    });
                    break;
            }
        });


        //一键纳入功能
        table.on('toolbar(addAssetsData)', function (obj) {
            var options = obj.config; // 获取当前表格属性配置项
            var checkStatus = table.checkStatus(options.id); // 获取选中行相关数据
            const {data} = checkStatus;
            if (data.length === 0) {
                layer.msg("请选择要纳入的设备", {icon: 2}, 1000);
                return false;
            }
            $.each(data, function (k, v) {
                removedata += ',' + v.assnum;
            });
            if (removedata.substr(0, 1) === ',') {
                removedata = removedata.substr(1);
            }
            //刷新
            if (removedata) {
                table.reload('addAssetsList', {where: {removedata: removedata, action: 'addAssetsList'}});
                table.reload('delAssetsList', {where: {removedata: removedata, action: 'delAssetsList'}});
            }
        });

        //搜索功能
        form.on('submit(addPatrolSearch)', function (data) {
            gloabOptions = data.field;
            gloabOptions.removedata = removedata;
            gloabOptions.action = 'addAssetsList';
            //刷新表格时，默认回到第一页
            console.log(gloabOptions)
            table.reload('addAssetsList', {
                url: addInventoryPlanUrl
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        form.on('submit(submit)', function (obj) {
            const data = obj.field;
            const target = $('#delAssetsList').next().find('.layui-table-main').find('tr');
            let inventory_plan_assets_nums = '';
            //获取设已纳入设备的编号
            target.each(function () {
                inventory_plan_assets_nums += $(this).find("td").eq(1).find('div').html() + ',';
            });
            if (!inventory_plan_assets_nums) {
                layer.msg("请选择要纳入的设备", {icon: 2}, 1000);
                return false;
            }
            const send_data = Object.assign({}, data)
            send_data.inventory_plan_assets_nums = inventory_plan_assets_nums.substring(0, inventory_plan_assets_nums.length - 1);
            if (send_data.hasOwnProperty('inventory_users')) {
                send_data.inventory_users = send_data.inventory_users.split(',')
            }
            if (!send_data.hasOwnProperty('is_push')) {
                send_data.is_push = "0";
            }
            console.log(send_data)
            submit($, send_data, addInventoryPlanUrl)

            return false;
        });

        //选择设备
        $("#addPatrolAssName").bsSuggest(
            returnAssets()
        )
        //设备编号搜索建议
        $("#addPatrolAssNum").bsSuggest(
            returnAssnum()
        )

        //选择分类
        $("#addPatrolAssCat").bsSuggest(
            returnCategory()
        );
    });
    exports('controller/inventory/addInventoryPlan', {});
});