layui.define(function (exports) {
    layui.use(['table', 'upload', 'form', 'tablePlug'], function () {
        var $ = layui.jquery, upload = layui.upload, tablePlug = layui.tablePlug;
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        //上传
        upload.render({
            elem: '#uploadAssetsFile', //绑定元素
            url: $(this).attr('data-url'),
            title: '上传文件',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            ext: 'xls|xlsx|xlsm',
            type: 'file',
            unwrap: false,
            auto: true,
            data: {"type": "upload"},
            multiple: true,
            before: function (input) {
                //返回的参数item，即为当前的input DOM对象
                layer.load(2);
            },
            done: function (res) {
                //上传完毕回调
                layer.closeAll('loading');
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1, time: 2000}, function () {
                        //刷新表格数据
                        table.reload('batchAssetsLists', {
                            url: batchAddAssets
                            , where: {"type": "getData"}
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                } else {
                    layer.msg(res.msg, {icon: 2, time: 3000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！', {icon: 2}, 1000);
            }
        });
        //到出模板
        $("#exploreAssetsModel").on('click', function () {
            window.location.href = 'batchAddAssets.html?type=exploreAssetsModel';
        });
        //获取数据
        table.render({
            elem: '#batchAssetsLists'
            , limits: [10, 20, 50, 100, 200, 500, 1000]
            , loading: true
            , limit: 10
            , url: batchAddAssets //数据接口
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , where: {
                sort: 'hospital_code'
                , order: 'desc'
                , type: 'getData'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'hospital_code' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                    field: 'tempid',
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
                    field: 'hospital_code',
                    title: '<i style="color: red;">* </i>医院代码',
                    width: 100,
                    edit: 'text',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'assets',
                    title: '<i style="color: red;">* </i>设备名称',
                    width: 180,
                    edit: 'text',
                    fixed: 'left',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'assorignum',
                    title: '设备原编码',
                    width: 140,
                    fixed: 'left',
                    event: 'assorignum',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'assorignum_spare',
                    title: '设备原编码(备用)',
                    width: 180,
                    event: 'assorignum_spare',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'serialnum',
                    title: '设备序列号',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'registration',
                    title: '注册证编号',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }, {
                    field: 'cate',
                    title: '<i style="color: red;">* </i>设备分类',
                    width: 180,
                    event: 'cate',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assets_level_name',
                    title: '管理类别',
                    width: 180,
                    event: 'assets_level_name',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'department',
                    title: '<i style="color: red;">* </i>使用科室',
                    width: 150,
                    event: 'department',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'helpcat', title: '辅助分类', width: 150, event: 'helpcat', unresize: false, align: 'center'}
                , {
                    field: 'finance',
                    title: '财务分类',
                    width: 150,
                    event: 'finance',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'brand', title: '品牌', width: 100, edit: 'text', unresize: false, align: 'center'}
                , {field: 'is_domesticName', title: '国产&进口', width: 120, edit: 'text', align: 'center'}
                , {field: 'model', title: '规格/型号', width: 120, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'patrol_xc_cycle',
                    title: '巡查周期(天)',
                    width: 140,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'patrol_pm_cycle',
                    title: '保养周期(天)',
                    width: 140,
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
                    width: 140,
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
                    title: '入库日期',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'opendate',
                    title: '启用日期',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'capitalfrom',
                    title: '资金来源',
                    width: 120,
                    event: 'capitalfrom',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'assfrom',
                    title: '设备来源',
                    width: 120,
                    event: 'assfrom',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'invoicenum', title: '发票编号', width: 140, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'assetsrespon',
                    title: '设备负责人',
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
                }
                , {field: 'paytime', title: '付款时间', width: 140, edit: 'text', align: 'center'}
                , {field: 'pay_statusName', title: '是否付清', width: 120, edit: 'text', align: 'center'}
                , {
                    field: 'expected_life',
                    title: '预计使用年限',
                    width: 130,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'residual_value',
                    title: '残净值率(%)',
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
                    title: '保修截止日期',
                    width: 140,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'depreciable_lives',
                    title: '折旧年限',
                    width: 120,
                    sort: true,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'depreciation_method',
                    title: '折旧方式',
                    width: 120,
                    event: 'depremethod',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'factory', title: '生产厂家', width: 200, edit: 'text', unresize: false, align: 'center'}
                , {
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
                , {field: 'supplier', title: '供应商', width: 200, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'supp_user',
                    title: '供应商联系人',
                    width: 120,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {
                    field: 'supp_tel',
                    title: '供应商联系电话',
                    width: 130,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'repair', title: '维修公司', width: 200, edit: 'text', unresize: false, align: 'center'}
                , {
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
                , {field: 'remark', title: '设备备注', width: 300, edit: 'text', unresize: false, align: 'center'}
                , {
                    field: 'inventory_label_id',
                    title: '标签ID',
                    width: 300,
                    edit: 'text',
                    unresize: false,
                    align: 'center'
                }
                , {field: 'operation', title: '操作', fixed: 'right', width: 80, align: 'center'}
            ]]
            , done: function (res, curr, count) {
                var error_tempid = res.error_tempid;
                var tableElem = this.elem;
                // table render出来实际显示的table组件
                var tableViewElem = tableElem.next();
                var tr = $(tableViewElem).find('.layui-table-main').find('tr');
                $.each(tr, function (k, v) {
                    var row_tempid = $(v).find('td').eq(1).attr('data-content');
                    if ($.inArray(row_tempid, error_tempid) >= 0) {
                        $(v).css('background', '#FBE5E5');
                    }
                });
                var tr1 = $(tableViewElem).find('.layui-table-fixed-l>.layui-table-body').find('tr');
                $.each(tr1, function (k, v) {
                    var row_tempid = $(v).find('td').eq(1).attr('data-content');
                    if ($.inArray(row_tempid, error_tempid) >= 0) {
                        $(v).css('background', '#FBE5E5');
                    }
                });
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听排序
        table.on('sort(batchAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('batchAssetsLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //监听工具条
        table.on('tool(batchAssetsData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = batchAddAssets;
            var params = {};
            params['tempid'] = data.tempid;
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
                                    table.reload('batchAssetsLists', {
                                        url: batchAddAssets
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
            } else if (layEvent === 'assorignum_spare') {
                var olddata = removeHtmlTag(data.assorignum_spare);
                layer.prompt({
                    formType: 2
                    , title: '修改设备名称为 【' + data.assets + '】 的设备原编码(备用)'
                    , value: olddata
                }, function (value, index) {
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
                                    assorignum_spare: value
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
                layer.open({
                    id: 'cates',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的设备分类',
                    area: ['450px', '600px'],
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
                layer.open({
                    id: 'departments',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的使用科室',
                    area: ['450px', '600px'],
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
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                    }
                });
            } else if (layEvent === 'helpcat') {
                layer.open({
                    id: 'helpcats',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的辅助分类',
                    area: ['450px', '500px'],
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
            } else if (layEvent === 'finance') {
                layer.open({
                    id: 'finances',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的财务分类',
                    area: ['450px', '500px'],
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
            } else if (layEvent === 'capitalfrom') {
                layer.open({
                    id: 'capitalfroms',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的资金来源',
                    area: ['450px', '500px'],
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
            } else if (layEvent === 'assfrom') {
                layer.open({
                    id: 'assfroms',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的设备来源',
                    area: ['450px', '500px'],
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
            } else if (layEvent === 'depremethod') {
                layer.open({
                    id: 'depremethods',
                    type: 1,
                    title: '修改设备名称为 【' + data.assets + '】 的折旧方法',
                    area: ['450px', '500px'],
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
                params['cate'] = p.catid;
                var url = batchAddAssets;
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
                                    cate: data.newdata
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
                var url = batchAddAssets;
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
                params['department'] = p.department;
                var url = batchAddAssets;
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
                                    department: data.newdata
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
                params['helpcat'] = p.helpcat;
                var url = batchAddAssets;
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
                                    helpcat: data.newdata
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
                params['finance'] = p.finance;
                var url = batchAddAssets;
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
                                    finance: data.newdata
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
                var url = batchAddAssets;
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
                params['assfrom'] = p.assfrom;
                var url = batchAddAssets;
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
                                    assfrom: data.newdata
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
                var url = batchAddAssets;
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
        table.on('edit(batchAssetsData)', function (obj) {
            var layuiTableObj = $(".layui-table");
            var td = layuiTableObj.find("tr td");
            var repeat = [];
            var inventoryLabelIds = []
            td.each(function (k, v) {
                if ($(v).attr("data-field") == 'serialnum') {
                    if ($(v).find('div').html() != '' && $(v).find('div').html() != '/') {
                        repeat.push($(v).find('div').html())
                    }
                }
                if ($(v).attr("data-field") == 'inventory_label_id') {
                    if ($(v).find('div').html() != '' && $(v).find('div').html() != '/') {
                        inventoryLabelIds.push($(v).find('div').html())
                    }
                }
            });
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            var repeatResult = $.inArray(value, repeat);
            if (repeatResult != -1) {
                var repeatEditTd = {}, i = 0;
                td.each(function (k, v) {
                    if ($(v).attr("data-field") == 'serialnum') {
                        repeatEditTd[i] = v;
                        i++;
                    }
                });
                layer.msg('设备序列号重复', {icon: 2, time: 2000});
                $(this).val("/");
                setTimeout(function () {
                    $(repeatEditTd[$(obj.tr.selector).attr("data-index")]).find('div').css("color", 'red');
                    $(repeatEditTd[$(obj.tr.selector).attr("data-index")]).find('div').html("/");
                }, 1900);
                return false;
            } else {
                td.each(function (k, v) {
                    if ($(v).attr("data-field") == 'serialnum') {
                        $(v).find('div').css("color", '#666');
                    }
                });
            }

            var inventoryLabelIdsResult = $.inArray(value, inventoryLabelIds);
            if (inventoryLabelIdsResult != -1) {
                var inventoryLabelIdsEditTd = {}, i = 0;
                td.each(function (k, v) {
                    if ($(v).attr("data-field") == 'inventory_label_id') {
                        inventoryLabelIdsEditTd[i] = v;
                        i++;
                    }
                });
                layer.msg('标签ID重复', {icon: 2, time: 2000});
                $(this).val("");
                setTimeout(function () {
                    $(inventoryLabelIdsEditTd[$(obj.tr.selector).attr("data-index")]).find('div').css("color", 'red');
                    $(inventoryLabelIdsEditTd[$(obj.tr.selector).attr("data-index")]).find('div').html("");
                }, 1900);
                return false;
            } else {
                td.each(function (k, v) {
                    if ($(v).attr("data-field") == 'inventory_label_id') {
                        $(v).find('div').css("color", '#666');
                    }
                });
            }

            params['tempid'] = data.tempid;
            params[cloum] = value;
            params.type = 'updateData';
            //不能为空的字段
            var keyvalue = [];
            keyvalue['assets'] = '设备名称';
            keyvalue['assorignum'] = '设备原编码';
            //keyvalue['serialnum'] = '设备序列号';
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
                    // if(!isMobile.test(value) && !isPhone.test(value)){
                    if (!checkTel(value)) {
                        layer.msg('修改无效，请正确填写联系电话，例如:13800138000或020-12345678！', {icon: 2, time: 2000});
                        return false;
                    }
                }
            }
            if (cloum == 'serialnum') {
                if (value == '') {
                    params.serialnum = '/';
                }
            }
            var old = $(this).prev().text();
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: batchAddAssets,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {

                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                        if (obj.field == "factorynum") {
                            setTimeout(function () {
                                obj.update({factorynum: old});
                            }, 100);
                        }
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
                var checkStatus = table.checkStatus('batchAssetsLists');
                var data = checkStatus.data;
                layer.alert(JSON.stringify(data));
            }
            , getCheckLength: function () { //获取选中数目
                var checkStatus = table.checkStatus('batchAssetsLists')
                    , data = checkStatus.data;
                layer.msg('选中了：' + data.length + ' 个');
            }
            , isAll: function () { //验证是否全选
                var checkStatus = table.checkStatus('batchAssetsLists');
                layer.msg(checkStatus.isAll ? '全选' : '未全选')
            }
        };
        //批量删除数据
        $('#batchDel').on('click', function () {
            var checkStatus = table.checkStatus('batchAssetsLists');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要删除的数据！', {icon: 2, time: 1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'delTmpAssets';
            for (j = 0, len = data.length; j < len; j++) {
                tempid += data[j]['tempid'] + ',';
            }
            params.tempid = tempid;
            layer.confirm('确定删除选中的数据吗？', {icon: 3, title: '批量删除数据'}, function (index) {
                $.ajax({
                    type: "POST",
                    url: batchAddAssets,
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
                                table.reload('batchAssetsLists', {
                                    url: batchAddAssets
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
        });
        //保存选中数据
        $('#uploadSel').on('click', function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            var checkStatus = table.checkStatus('batchAssetsLists');
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
                url: batchAddAssets,
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
                            table.reload('batchAssetsLists', {
                                url: batchAddAssets
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
                url: batchAddAssets,
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
                            table.reload('batchAssetsLists', {
                                url: batchAddAssets
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
    });
    exports('controller/assets/lookup/batchaddassets', {});
});
