layui.define(function (exports) {
    layui.use(['table', 'upload', 'form', 'suggest', 'formSelects', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload, suggest = layui.suggest, formSelects = layui.formSelects,
            tablePlug = layui.tablePlug;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        //初始化搜索建议插件
        suggest.search();

        //渲染所有多选下拉
        formSelects.render('batchEditDepartment', selectParams(1));
        formSelects.btns('batchEditDepartment', selectParams(2), selectParams(3));
        //获取数据
        table.render({
            elem: '#batchEditAssetsLists'
            , limits: [5, 10, 20, 50, 100, 200, 500]
            , loading: true
            , limit: 10
            , url: batchEditAssets //数据接口
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , height: 510
            , where: {
                sort: 'adddate'
                , order: 'asc'
                , type: 'getData'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
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
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    fixed: 'left'
                }, {
                    field: 'assid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    unresize: false,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    title: '<i style="color: red;">* </i>设备编码',
                    width: 150,
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'assets',
                    title: '<i style="color: red;">* </i>设备名称',
                    width: 150,
                    edit: 'text',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assorignum',
                    title: '<i style="color: red;">* </i>设备原编码',
                    width: 180,
                    fixed: 'left',
                    event: 'assorignum',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assorignum_spare',
                    title: '设备原编码(备用)',
                    width: 180,
                    event: 'assorignum_spare',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'serialnum',
                    title: '<i style="color: red;">* </i>设备序列号',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'registration',
                    title: '注册证编号',
                    width: 140,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'category',
                    title: '<i style="color: red;">* </i>设备分类',
                    width: 180,
                    event: 'cate',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assets_level_name',
                    title: '管理类别',
                    width: 120,
                    event: 'assets_level_name',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'department',
                    title: '<i style="color: red;">* </i>使用科室',
                    width: 160,
                    event: 'department',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'helpcatid',
                    title: '辅助分类',
                    width: 120,
                    event: 'helpcat',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'financeid',
                    title: '<i style="color: red;">* </i>财务分类',
                    width: 120,
                    event: 'finance',
                    unresize: false,
                    align: 'center'
                }, {field: 'brand', title: '品牌', width: 100, edit: 'text', unresize: false, align: 'center'}
                , {field: 'is_domesticName', title: '国产&进口', width: 120, edit: 'text', align: 'center'}
                , {
                    field: 'model',
                    title: '<i style="color: red;">* </i>规格/型号',
                    width: 160,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'patrol_xc_cycle',
                    title: '巡查周期(天)',
                    width: 110,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'patrol_pm_cycle',
                    title: '保养周期(天)',
                    width: 110,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'quality_cycle',
                    title: '质控周期(天)',
                    width: 140,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'metering_cycle',
                    title: '计量周期(天)',
                    width: 110,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'unit', title: '单位', width: 100, edit: 'text', unresize: false, align: 'center'}
                , {field: 'factorynum', title: '出厂编号', width: 140, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'factorydate',
                    title: '出厂日期',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'storage_date',
                    title: '<i style="color: red;">* </i>入库日期',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'opendate',
                    title: '<i style="color: red;">* </i>启用日期',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'capitalfrom',
                    title: '<i style="color: red;">* </i>资金来源',
                    width: 120,
                    event: 'capitalfrom',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'assfromid',
                    title: '<i style="color: red;">* </i>设备来源',
                    width: 120,
                    event: 'assfrom',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'invoicenum', title: '发票编号', width: 140, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'assetsrespon',
                    title: '<i style="color: red;">* </i>设备负责人',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'buy_price',
                    title: '<i style="color: red;">* </i>原值(元)',
                    width: 130,
                    sort: true,
                    edit: 'text',
                    align: 'center'
                }, {
                    field: 'paytime',
                    title: '付款时间',
                    width: 140,
                    edit: 'text',
                    align: 'center'
                }, {
                    field: 'pay_statusName',
                    title: '是否付清',
                    width: 120,
                    edit: 'text',
                    align: 'center'
                }, {
                    field: 'expected_life',
                    title: '<i style="color: red;">* </i>预计使用年限',
                    width: 130,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'residual_value',
                    title: '<i style="color: red;">* </i>残净值率(%)',
                    width: 140,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'is_firstaid', title: '急救设备', width: 120, edit: 'text', unresize: false, align: 'center'}
                , {field: 'is_special', title: '特种设备', width: 120, edit: 'text', unresize: false, align: 'center'}
                , {field: 'is_metering', title: '计量设备', width: 120, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'is_qualityAssets',
                    title: '质控设备',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'is_patrol', title: '保养设备', width: 120, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'is_benefit',
                    title: '效益分析设备',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'is_lifesupport',
                    title: '生命支持类设备',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'guarantee_date',
                    title: '<i style="color: red;">* </i>保修截止日期',
                    width: 140,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'depreciable_lives',
                    title: '<i style="color: red;">* </i>折旧年限',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'depreciation_method',
                    title: '<i style="color: red;">* </i>折旧方式',
                    width: 120,
                    event: 'depremethod',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'factory',
                    title: '<i style="color: red;">* </i>生产厂家',
                    width: 200,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'factory_user',
                    title: '厂家联系人',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'factory_tel',
                    title: '厂家联系电话',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'supplier',
                    title: '<i style="color: red;">* </i>供应商',
                    width: 200,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'supp_user',
                    title: '供应商联系人',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'supp_tel',
                    title: '供应商联系电话',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'repair',
                    title: '维修公司',
                    width: 200,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'repa_user',
                    title: '维修公司联系人',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'repa_tel',
                    title: '维修公司联系电话',
                    width: 150,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'remark', title: '设备备注', width: 300, edit: 'text', unresize: false, align: 'center'},
                {
                    field: 'inventory_label_id',
                    title: '标签ID',
                    width: 300,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                },
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听排序
        table.on('sort(batchEditAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('batchEditAssetsLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //搜索按钮
        form.on('submit(assetsListsSearch_1)', function (data) {
            gloabOptions = data.field;
            var assetsType = '';
            $("input[name='assetsType']:checked").each(function (k, v) {
                assetsType += "," + $(v).val();
            });
            gloabOptions.assetsType = assetsType;
            var table = layui.table;
            table.reload('batchEditAssetsLists', {
                url: batchEditAssets
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //监听工具条
        table.on('tool(batchEditAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = batchEditAssets;
            var params = {};
            params['assid'] = data.assid;
            params['type'] = 'updateData';
            if (layEvent === 'delTmpAssets') { //删除
                //do something
                params['type'] = $(this).attr('data-type');
                layer.confirm('确定删除该条数据吗？', {
                    icon: 3,
                    title: $(this).html() + '【' + data.assets + '】'
                }, function (index) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend: function () {
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success: function (data) {
                            layer.closeAll('loading');
                            if (data.status == 1) {
                                layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                    table.reload('batchEditAssetsLists', {
                                        url: batchEditAssets
                                        , where: {"type": "getData"}
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
            } else if (layEvent === 'assorignum') {
                var olddata = removeHtmlTag(data.assorignum);
                layer.prompt({
                    formType: 2
                    , title: '修改设备名称为 【' + data.assets + '】 的设备原编码值'
                    , value: olddata
                }, function (value, index) {
                    if (!$.trim(value)) {
                        layer.msg('设备原编码不能为空', {icon: 2, time: 2000});
                        return false;
                    }
                    params[obj.event] = value;
                    //更新数据库
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status == 1) {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    assorignum: value
                                });
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 2000});
                            }
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2, time: 2000});
                        }
                    });
                });
            } else if (layEvent === 'cate') {
                form.render('select');
                var cate = $('select[name="catid"]');
                cate[0].options[data.catid].selected = true;
                layer.open({
                    id: 'cates',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的设备分类',
                    area: ['480px', '500px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#cateList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent === 'assets_level_name') {
                form.render('select');
                var assets_level = $('select[name="assets_level"]');
                assets_level[0].options[data.assets_level].selected = true;
                layer.open({
                    id: 'assets_level_names',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的管理类别',
                    area: ['450px', '600px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#assets_level_name'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent === 'department') {
                var department = $('select[name="department"]');
                department[0].options[data.departid].selected = true;
                layer.open({
                    id: 'departments',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的使用科室',
                    area: ['450px', '500px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#departList'),
                    end: function () {

                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent == 'helpcat') {
                layer.open({
                    id: 'helpcats',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的辅助分类',
                    area: ['450px', '400px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#helpcatList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent == 'finance') {
                layer.open({
                    id: 'finances',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的财务分类',
                    area: ['450px', '400px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#financeList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent == 'capitalfrom') {
                layer.open({
                    id: 'capitalfroms',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的资金来源',
                    area: ['450px', '400px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#capitalfromList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent == 'assfrom') {
                layer.open({
                    id: 'assfroms',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的设备来源',
                    area: ['450px', '400px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#assfromList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent == 'depremethod') {
                layer.open({
                    id: 'depremethods',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的折旧方法',
                    area: ['450px', '400px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#methodList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            }
            //监听提交分类
            form.on('submit(saveCate)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.catid) {
                    layer.msg("请选择设备分类！", {icon: 2, time: 2000});
                    return false;
                }
                params['catid'] = p.catid;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    catid: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交医疗器械类别
            form.on('submit(saveAssetsLevel)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                params['assets_level'] = p['assets_level'];
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                switch (data.newdata) {
                                    case '1':
                                        newLevelName = 'Ⅰ类';
                                        break;
                                    case '2':
                                        newLevelName = 'Ⅱ类';
                                        break;
                                    case '3':
                                        newLevelName = 'Ⅲ类';
                                        break;
                                    default:
                                        newLevelName = '';
                                        break;
                                }
                                obj.update({
                                    assets_level_name: newLevelName
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交科室
            form.on('submit(saveDepart)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.department) {
                    layer.msg("请选择设备使用科室！", {icon: 2, time: 2000});
                    return false;
                }
                params['departid'] = p.department;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    departid: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交辅助分类
            form.on('submit(saveHelpcat)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.helpcat) {
                    layer.msg("请选择设备辅助分类！", {icon: 2, time: 1000});
                    return false;
                }
                params['helpcatid'] = p.helpcat;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    helpcatid: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交财务分类
            form.on('submit(saveFinance)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.finance) {
                    layer.msg("请选择设备财务分类！", {icon: 2, time: 1000});
                    return false;
                }
                params['financeid'] = p.finance;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    financeid: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交资金来源
            form.on('submit(saveCapitalfrom)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.capitalfrom) {
                    layer.msg("请选择资金来源！", {icon: 2, time: 1000});
                    return false;
                }
                params['capitalfrom'] = p.capitalfrom;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    capitalfrom: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交设备来源
            form.on('submit(saveAssfrom)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.assfrom) {
                    layer.msg("请选择设备来源！", {icon: 2, time: 1000});
                    return false;
                }
                params['assfromid'] = p.assfrom;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    assfromid: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
            //监听提交折旧方式
            form.on('submit(saveMethod)', function (data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var p = data.field;
                if (!p.depreciation_method) {
                    layer.msg("请选择折旧方式！", {icon: 2, time: 1000});
                    return false;
                }
                params['depreciation_method'] = p.depreciation_method;
                var url = batchEditAssets;
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                layer.close(index);
                                //同步更新表格和缓存对应的值
                                obj.update({
                                    depreciation_method: data.newdata
                                });
                            });
                        } else {
                            layer.msg(data.msg, {icon: 2, time: 2000});
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2, time: 2000});
                    }
                });
                return false;
            });
        });
        //监听单元格编辑
        table.on('edit(batchEditAssetsData)', function (obj) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            params['assid'] = data.assid;
            params[cloum] = value;
            params.type = 'updateData';
            //不能为空的字段
            var keyvalue = [];
            keyvalue['assets'] = '设备名称';
            keyvalue['assorignum'] = '设备原编码';
            keyvalue['serialnum'] = '设备序列号';
            keyvalue['cate'] = '设备分类';
            keyvalue['department'] = '使用科室';
            keyvalue['finance'] = '财务分类';
            keyvalue['model'] = '规格/型号';
            keyvalue['storage_date'] = '入库日期';
            keyvalue['opendate'] = '启用日期';
            keyvalue['capitalfrom'] = '资金来源';
            keyvalue['assfrom'] = '设备来源';
            keyvalue['assetsrespon'] = '设备负责人';
            keyvalue['buy_price'] = '原值(元)';
            keyvalue['expected_life'] = '预计使用年限';
            keyvalue['residual_value'] = '残净值率(%)';
            keyvalue['guarantee_date'] = '保修截止日期';
            keyvalue['depreciable_lives'] = '折旧年限';
            keyvalue['depreciation_method'] = '折旧方式';
            keyvalue['factory'] = '生产厂家';
            keyvalue['supplier'] = '供应商';
            if (!value) {
                for (var item in keyvalue) {
                    if (item == cloum) {
                        layer.msg('修改失败！' + keyvalue[cloum] + '不能为空！', {icon: 2, time: 2000});
                        return false;
                    }
                }
            }
            if (cloum == 'factory_tel' || cloum == 'supp_tel' || cloum == 'repa_tel') {
                if (value) {
                    //验证联系方式
                    // var isMobile=/^1[3|4|5|7|8|9][0-9]\d{4,8}$/;
                    // var isPhone=/^((0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/;
                    flag = true;
                    // if(!isMobile.test(value) && !isPhone.test(value)){
                    if (!checkTel(value)) {
                        layer.msg('修改无效，请正确填写联系电话，例如:13800138000或020-12345678！', {icon: 2, time: 2000});
                        return false;
                    }
                }
            }
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: batchEditAssets,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {

                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });
        //监听表格复选框选择
        // table.on('checkbox(batchAssetsData)', function(obj){
        //     console.log(obj)
        // });
        var $ = layui.$, active = {
            getCheckData: function () { //获取选中数据
                var checkStatus = table.checkStatus('batchEditAssetsLists');
                var data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            , getCheckLength: function () { //获取选中数目
                var checkStatus = table.checkStatus('batchEditAssetsLists')
                    , data = checkStatus.data;
                layer.msg('选中了：' + data.length + ' 个');
            }
            , isAll: function () { //验证是否全选
                var checkStatus = table.checkStatus('batchEditAssetsLists');
                layer.msg(checkStatus.isAll ? '全选' : '未全选')
            }
        };
        //批量维护数据
        $('#batchEdit').on('click', function () {
            var checkStatus = table.checkStatus('batchEditAssetsLists');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要维护的设备！', {icon: 2, time: 1000});
                return false;
            }
            var assid = '';
            var params = {};
            params.type = 'batchEdit';
            for (j = 0, len = data.length; j < len; j++) {
                assid += data[j]['assid'] + ',';
            }
            var flag = 1;
            var url = $(this).attr('data-url');
            url += '?assid=' + assid + '&type=batchEdit';
            top.layer.open({
                id: 'batchEdits',
                type: 2,
                title: $(this).html(),
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                scrollbar: false,
                maxmin: true,
                area: ['90%', '98%'],
                closeBtn: 1,
                content: [url],
                end: function () {
                    if (flag) {
                        table.reload('batchEditAssetsLists', {
                            url: batchEditAssets
                            , where: gloabOptions
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
            return false;
            // params.assid = assid;
            // layer.confirm('确定删除选中的数据吗？', {icon: 3, title:'批量删除数据'}, function(index){
            //     $.ajax({
            //         type:"POST",
            //         url:batchEditAssets,
            //         data:params,
            //         //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
            //         beforeSend:function(){
            //             layer.load(1);
            //         },
            //         //成功返回之后调用的函数
            //         success:function(data){
            //             layer.closeAll('loading');
            //             if(data.status == 1){
            //                 layer.msg(data.msg,{icon : 1,time:1000},function(){
            //                     table.reload('batchEditAssetsLists', {
            //                         url: batchEditAssets
            //                         ,where: {"type":"getData"}
            //                         ,page: {
            //                             curr: 1 //重新从第 1 页开始
            //                         }
            //                     });
            //                 });
            //             }else{
            //                 layer.msg(data.msg,{icon : 2});
            //             }
            //         },
            //         //调用出错执行的函数
            //         error: function(){
            //             //请求出错处理
            //             layer.msg('服务器繁忙', {icon: 5});
            //         }
            //     });
            //     layer.close(index);
            // });
        });
        //保存选中数据
        $('#uploadSel').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var checkStatus = table.checkStatus('batchEditAssetsLists');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要保存的数据！', {icon: 2, time: 1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'save';
            for (j = 0, len = data.length; j < len; j++) {
                tempid += data[j]['tempid'] + ',';
            }
            params.tempid = tempid;
            $.ajax({
                type: "POST",
                url: batchEditAssets,
                data: params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchEditAssetsLists', {
                                url: batchEditAssets
                                , where: {"type": "getData"}
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

        //保存当页数据
        $('#uploadAll').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var tempid = '';
            var params = {};
            params.type = 'save';
            var tr = $('.layui-table-main').find('tr');
            $.each(tr, function () {
                $.each($(this).find('td'), function () {
                    if ($(this).attr('data-field') == 'tempid') {
                        tempid += ($(this).attr('data-content')) + ',';
                    }
                });
            });
            if (tempid == '') {
                layer.msg('没有要保存的数据！', {icon: 2, time: 1000});
                return false;
            }
            params.tempid = tempid;
            $.ajax({
                type: "POST",
                url: batchEditAssets,
                data: params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchEditAssetsLists', {
                                url: batchEditAssets
                                , where: {"type": "getData"}
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
        //设备名称搜索建议
        $("#getAssetsListAssets").bsSuggest(
            returnAssets()
        );

        //分类搜索建议

        $("#getAssetsListCategory").bsSuggest(
            returnCategory('', 1)
        );

        //设备编号搜索建议

        $("#getAssetsListAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='getAssetsListAssets']").val(data.assets);
            $("input[name='getAssetsListAssorignum']").val(data.assorignum);
        })

        $(".highSearch").click(function () {
            layer.open({
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['1145px', '100%'],
                type: 2,
                title: '<i class="layui-icon layui-icon-search"></i>' + $(this).html(),
                scrollbar: false,
                maxmin: false, //显示最小化按钮
                //offset: ['103px', '235px'],
                resize: false,
                move: false,
                //anim: 0, //动画风格
                //area: [(area+11)+'px', '500px'],
                closeBtn: 1,
                content: [admin_name + '/Public/getAssetsListHighSearch'],
                success: function (layero) {
                    var cssText = $(layero).attr("style") + ";box-shadow:none !important;";
                    $(layero).css('cssText', cssText);
                },
                end: function () {
                    var storage = window.localStorage, highSearch = {};
                    //方式是高级查询
                    if (storage['highSearch'] != undefined) {
                        highSearch = JSON.parse(storage['highSearch']);
                        storage.removeItem('highSearch');
                        highSearch.type = 'getData';
                        table.reload('batchEditAssetsLists', {
                            url: batchEditAssets
                            , where: highSearch
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    }
                }
            });
            return false;
        });
    });
    exports('controller/assets/lookup/batcheditassets', {});
});
