layui.define(function (exports) {
    layui.use(['form', 'table', 'tablePlug', 'suggest'], function () {
        var form = layui.form, table = layui.table, tablePlug = layui.tablePlug, suggest = layui.suggest;

        //初始化搜索建议插件
        suggest.search();

        var tableData = [];

        initData()

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
            height: '500px',
            size: 'sm'
            , toolbar: '#toolbarDemo'
            , defaultToolbar: []
            , page: false //开启分页
            , cols: [[
                {
                    field: 'assetnum', title: '设备编码', minWidth: 180, align: 'center',
                    templet: function (d) {
                        if (d.is_plan === '1') {
                            return d.assetnum
                        } else {
                            return ` <div class="input-group">
                                        <input type="text" class="form-control bsSuggest assnumSearch" value="${d.assetnum ? d.assetnum : ''}">
                                            <ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu"></ul>
                                    </div>`
                        }
                    }
                }
                , {
                    field: 'assets',
                    title: '设备名称',
                    minWidth: 180,
                    align: 'center',
                    templet: function (d) {
                        // if (d.is_plan === '1') {
                        return d.assets ? d.assets : ''
                        // }
                        // let html = ''
                        // dicAssets.forEach(item=>{
                        //     html += `<option value="${item.assets}" ${d.assets === item.assets ? 'selected' : ''}>${item.assets}</option>`
                        // })
                        // return `
                        //    <select lay-search lay-filter="assets">
                        //          ${html}
                        //    </select>
                        // `
                    }
                }
                , {
                    field: 'departid', title: '使用科室', minWidth: 130, align: 'center',
                    templet: function (d) {
                        // if (d.is_plan === '1') {
                        const findItem = department.find(item => item.departid === d.departid)
                        if (findItem) {
                            return findItem['department']
                        } else {
                            return ''
                        }
                        // }
                        // let html = ''
                        // department.forEach(item=>{
                        //     html += `<option value="${item.departid}" ${d.departid === item.departid ? 'selected' : ''}>${item.department}</option>`
                        // })
                        // return `
                        //    <select lay-search lay-filter="department">
                        //          ${html}
                        //    </select>
                        // `
                    }
                }
                , {
                    field: 'address', title: '设备使用位置', minWidth: 120, align: 'center',
                    templet: function (d) {
                        // if (d.is_plan === '1') {
                        return d.address ? d.address : ''
                        // }
                        // return `<input type="text" class="layui-input addressInput" value="${d.address ?d.address :''}">`
                    }
                }
                , {
                    field: 'is_plan', title: '是否计划内', width: 100, align: 'center',
                    templet: function (d) {
                        return d.is_plan === '0' ? '否' : '是'
                    }
                }
                , {
                    field: 'inventory_status', title: '实盘状态', minWidth: 90, align: 'center',
                    templet: function (d) {
                        // if (d.is_plan === '0'){
                        //     return '异常'
                        // }
                        return `
                               <select lay-search lay-filter="inventory_status">
                                  <option value="0" ${d.inventory_status === '0' ? 'selected' : ''}>未盘点</option>
                                  <option value="1" ${d.inventory_status === '1' ? 'selected' : ''}>正常</option>
                                  <option value="2" ${d.inventory_status === '2' ? 'selected' : ''}>异常</option>
                               </select>
                            `
                    }
                }
                , {
                    field: 'reason', title: '原因', minWidth: 150, align: 'center',
                    templet: function (d) {
                        if (d.inventory_status === '2') {
                            return `<input type="text" class="layui-input reasonInput" value="${d.reason ? d.reason : ''}">`
                        }
                        return d.reason ? d.reason : ''
                    }
                }
                , {
                    field: 'result', title: '处理结果', minWidth: 130, align: 'center',
                    templet: function (d) {
                        if (d.inventory_status === '1') {
                            return ''
                        } else if (d.inventory_status === '2') {
                            // else if (d.inventory_status === '2' && d.is_plan === '1'){
                            return '报废'
                        }
                        // else if (d.inventory_status === '2' && d.is_plan === '0'){
                        //     return '盘盈'
                        // }
                        return ''
                    }
                }
                , {
                    field: 'financeid', title: '财务分类', minWidth: 110, align: 'center',
                    templet: function (d) {
                        return  assetsFinance[d.financeid] ? assetsFinance[d.financeid] : '';

                        // let html = ''
                        // assetsFinance.forEach((item, index) => {
                        //     html += `<option value="${index}" ${parseInt(d.financeid) === index ? 'selected' : ''}>${item}</option>`
                        // })
                        // return `
                        //    <select lay-search lay-filter="finance">
                        //          ${html}
                        //    </select>
                        // `
                    }
                }, {
                    field: 'inventory_user', title: '盘点员', minWidth: 110, align: 'center',
                    templet: function (d) {
                        // if (d.is_plan === '1') {
                        //     return assetsFinance[d.financeid]
                        // }
                        let html = '<option value="">请选择</option>'
                        inventoryPlanData['inventory_plan']['inventory_users'].forEach(item => {
                            html += `<option value="${item}" ${d.inventory_user === item ? 'selected' : ''}>${item}</option>`
                        })
                        return `
                           <select lay-search lay-filter="inventory_user">
                                 ${html}
                           </select>
                        `
                    }
                }
                , {
                    field: 'action', title: '操作', width: 100, align: 'center',
                    templet: function (d) {
                        return d.is_plan === '0' ? '<button type="button" lay-event="del" class="layui-btn layui-btn-xs">删除</button>' : ''
                    }
                }
            ]],
            data: tableData,
            done: function () {

                /*
 /选择设备编号 搜索功能
 */
                function returnAssnum(removedata = '') {
                    var value = {
                        url: saveOrEndInventoryPlanUrl + '?action=addAssetAll&removedata=' + removedata,
                        effectiveFields: ["assnum", "assets"],
                        searchFields: ["assnum"],
                        effectiveFieldsAlias: {assnum: "设备编号", assets: "设备名称"},
                        ignorecase: false,
                        showHeader: true,
                        listStyle: {
                            "max-height": "330px", "max-width": "500px",
                            "overflow": "auto", "width": "500px", "text-align": "center"
                        },
                        showBtn: false,     //不显示下拉按钮
                        delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                        idField: "assnum",
                        keyField: "assnum",
                        clearable: false
                    };
                    return value;
                }

                //设备编号搜索建议
                let assetnumArr = tableData.map(item => item.assetnum);
                assetnumArr = assetnumArr.filter(item => item)
                $(".assnumSearch").bsSuggest(
                    returnAssnum(assetnumArr.join(','))
                ).on('onSetSelectValue', function (e, keyword, data) {
                    console.log(data)
                    const table_index = $(e.target).parents('tr').attr('data-index')
                    tableData[table_index]['assetnum'] = data.assnum
                    tableData[table_index]['assets'] = data.assets
                    tableData[table_index]['departid'] = data.departid
                    tableData[table_index]['address'] = data.address
                    tableData[table_index]['financeid'] = data.financeid
                    table_reload()
                    console.log(tableData)
                });

                // // 监听设备位置
                // $(".addressInput").on('input',debounce(function (arg){
                //     const {target} = arg[0]
                //     const table_index = $(this).parents('tr').attr('data-index')
                //     tableData[table_index]['address'] = target.value
                //     table_reload()
                // },2000))
                // 监听原因
                $(".reasonInput").on('input', debounce(function (arg) {
                    const {target} = arg[0]
                    const table_index = $(this).parents('tr').attr('data-index')
                    tableData[table_index]['reason'] = target.value
                    table_reload()
                }, 2000))
            }
        });

        // // 监听设备名称select
        // form.on('select(assets)', function(data){
        //     const table_index = $(data.elem).parents('tr').attr('data-index')
        //     tableData[table_index]['assets'] = data.value
        //     table_reload()
        // });
        //
        // // 监听使用科室select
        // form.on('select(department)', function(data){
        //     const table_index = $(data.elem).parents('tr').attr('data-index')
        //     tableData[table_index]['departid'] = data.value
        //     const findItem = department.find(item=>item.departid === data.value)
        //     tableData[table_index]['address'] = findItem['address']
        //     table_reload()
        // });

        // 监听实盘状态select
        form.on('select(inventory_status)', function (data) {
            const table_index = $(data.elem).parents('tr').attr('data-index')
            tableData[table_index]['inventory_status'] = data.value
            table_reload()
        });

        // 监听财务分类select
        form.on('select(finance)', function (data) {
            const table_index = $(data.elem).parents('tr').attr('data-index')
            tableData[table_index]['financeid'] = data.value
            table_reload()
        });

        // 监听盘点员select
        form.on('select(inventory_user)', function (data) {
            const table_index = $(data.elem).parents('tr').attr('data-index')
            tableData[table_index]['inventory_user'] = data.value
            table_reload()
        });


        table.on('toolbar(tableList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            switch (layEvent) {
                case 'add':
                    tableData.push({
                        'is_plan': '0',
                        'inventory_status': '0',
                        'assets': null,
                        'departid': null,
                        'reason': '',
                        'financeid': null,
                        'address': null,
                        'inventory_plan_assets_id': null,
                        'inventory_user': inventoryPlanData['inventory_plan']['inventory_users'][0],
                    })
                    console.log(tableData)
                    table_reload()
                    break;
            }
        });

        // 删除
        table.on('tool(tableList)', function (obj) {
            const index = $(obj.tr[0]).attr('data-index')
            tableData.splice(index, 1)
            table_reload()
        });


        // 暂存
        form.on('submit(temp_save)', function () {
            const data = {
                "inventory_plan_id": inventoryPlanData['inventory_plan']['inventory_plan_id'],
                "operate": 'save',
                "inventory_plan_assets": tableData
            }
            submit($, data, saveOrEndInventoryPlanUrl);
            return false;
        });


        // 保存
        form.on('submit(save)', function () {
            const data = {
                "inventory_plan_id": inventoryPlanData['inventory_plan']['inventory_plan_id'],
                "operate": 'end',
                "inventory_plan_assets": tableData
            }
            console.log(data)
            submit($, data, saveOrEndInventoryPlanUrl);
            return false;
        });

        // 初始化数据
        function initData() {
            $('.inventory_plan_no').html(inventoryPlanData['inventory_plan']['inventory_plan_no'])
            $('.inventory_plan_name').html(inventoryPlanData['inventory_plan']['inventory_plan_name'])
            $('.inventory_plan_start_time').html(inventoryPlanData['inventory_plan']['inventory_plan_start_time'])
            $('.inventory_plan_end_time').html(inventoryPlanData['inventory_plan']['inventory_plan_end_time'])
            $('.inventory_plan_status_name').html(inventoryPlanData['inventory_plan']['inventory_plan_status_name'])
            $('.inventory_users').html(inventoryPlanData['inventory_plan']['inventory_users'].join('、'))
            tableData = [...inventoryPlanData['inventory_plan_assets']]
        }

        // 重载表格
        function table_reload() {
            table.reload('tableList', {
                data: tableData
            });
        }

        //防抖函数
        function debounce(fn, delay) {
            var timeOut = null;//存放settimeout返回值
            return function (e) {
                clearTimeout(timeOut);//清除定时器
                timeOut = setTimeout(() => {
                    fn.call(this, arguments);
                }, delay)
            }
        }
    });
    exports('controller/inventory/saveOrEndInventoryPlan', {});
});