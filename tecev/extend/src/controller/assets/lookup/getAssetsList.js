layui.define(function (exports) {
    // 定义一个全局变量 表格总数据数量
    var all_assids;

    //判断搜索建议的位置
    position = '';
    lookupGetAssetsListObj = $("#LAY-Assets-Lookup-getAssetsList");
    if (Math.floor(lookupGetAssetsListObj.find('.layui-form-item').width() / lookupGetAssetsListObj.find('.layui-inline').width()) == 3) {
        position = '';
    } else {
        position = 1;
    }
    var exportAssetsID = '';//导出设备assID
    window.localStorage.setItem('exportAssetsID', exportAssetsID);
//判断高级查询弹出框 宽高
    area = lookupGetAssetsListObj.find('.layui-card-header').width();
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'upload', 'tablePlug', 'soulTable'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate,
            table = layui.table, suggest = layui.suggest, upload = layui.upload, tablePlug = layui.tablePlug,
            soulTable = layui.soulTable;
        var orderField = '', orderType = '';
        //渲染所有多选下拉
        formSelects.render('orderType', selectParams(1));
        //科室 多选框初始配置
        formSelects.render('getAssetsListDepartment', selectParams(1));
        formSelects.btns('getAssetsListDepartment', selectParams(2));
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //初始化时间
        lay('.formatDate').each(function () {
            laydate.render(dateConfig(this));
        });

        //定义一个全局空对象
        var gloabOptions = {};
        isFirstxu = true;//用于判断是否刷新的变量
        table.render({
            elem: '#assetsLists'
            , limits: [20, 50, 100, 200, 500,1000]
            , loading: true
            , limit: 20
            , height: 'full-375'
            //, height: 'full-100' //高度最大化减去差值
            , autoSort: false
            , url: getAssetsList //数据接口
            , where: {
                sort: 'assid'
                , order: 'desc'
                , expect_assidStr: expect_assid
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Assets-Lookup-getAssetsListToolbar',
            defaultToolbar: ['filter']
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left', style: 'background-color: #f9f9f9;'},
                {
                    field: 'assid',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '序号',
                    width: 65,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            return d.LAY_INDEX
                        } else {
                            return '<span style="color: #FF5722;">' + d.LAY_INDEX + '</span>';
                        }
                    }
                }
                , {
                    field: 'assets',
                    fixed: 'left',

                    style: 'background-color: #f9f9f9;',
                    title: '设备名称',
                    width: 160,
                    align: 'center'
                }
                , {
                    field: 'assnum',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备编号',
                    width: 160,
                    align: 'center'
                }
                , {field: 'model', title: '规格型号', width: 140, align: 'center'}
                , {field: 'buy_price', title: '设备原值', sort: true, width: 120, align: 'center'}
                , {field: 'storage_date', title: '入库日期', width: 110, sort: true, align: 'center'}
                , {field: 'category', title: '设备分类', width: 150, align: 'center'}
                , {field: 'department', title: '科室名称', width: 150, align: 'center'}
                , {field: 'departperson', title: '科室负责人', width: 100, align: 'center'}
                , {
                    field: 'status_name',
                    title: '设备当前状态',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            switch (d.status) {
                                case 0:
                                    return d.status_name;
                                    break;
                                case 1:
                                    return '<span style="color: #FFB800;">' + d.status_name + '</span>';
                                    break;
                                case 5:
                                    return '<span style="color: #FFB800;">' + d.status_name + '</span>';
                                    break;
                                case 6:
                                    return '<span style="color: #FFB800;">' + d.status_name + '</span>';
                                    break;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.status_name + '</span>';
                        }
                    }
                }
                , {field: 'brand', title: '品牌', width: 140, align: 'center'}
                , {field: 'box_num', title: '档案盒编号', width: 150, align: 'center'}
                , {
                    field: 'is_lifesupport',
                    title: '生命支持类设备',
                    width: 140,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_lifesupport === '是') {
                                return '<span style="color: #FF5722;">' + d.is_lifesupport + '</span>';
                            } else {
                                return d.is_lifesupport;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_lifesupport + '</span>';
                        }
                    }
                }, {
                    field: 'is_firstaid',
                    title: '急救设备',
                    width: 90,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_firstaid == '是') {
                                return '<span style="color: #FF5722;">' + d.is_firstaid + '</span>';
                            } else {
                                return d.is_firstaid;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_firstaid + '</span>';
                        }
                    }
                }, {
                    field: 'is_special',
                    title: '特种设备',
                    width: 90,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_special == '是') {
                                return '<span style="color: #FF5722;">' + d.is_special + '</span>';
                            } else {
                                return d.is_special;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_special + '</span>';
                        }
                    }
                }, {
                    field: 'is_metering',
                    title: '计量设备',
                    width: 90,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_metering == '是') {
                                return '<span style="color: #FF5722;">' + d.is_metering + '</span>';
                            } else {
                                return d.is_metering;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_metering + '</span>';
                        }
                    }
                }, {
                    field: 'is_qualityAssets',
                    title: '质控设备',
                    width: 90,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_qualityAssets == '是') {
                                return '<span style="color: #FF5722;">' + d.is_qualityAssets + '</span>';
                            } else {
                                return d.is_qualityAssets;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_qualityAssets + '</span>';
                        }
                    }
                }, {
                    field: 'is_benefit',
                    title: '效益分析设备',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        if (d.status != 2) {
                            if (d.is_benefit == '是') {
                                return '<span style="color: #FF5722;">' + d.is_benefit + '</span>';
                            } else {
                                return d.is_benefit;
                            }
                        } else {
                            return '<span style="color: #FF5722;">' + d.is_benefit + '</span>';
                        }
                    }
                }
                , {field: 'helpcatid', title: '辅助分类', width: 100, align: 'center'}
                , {field: 'financeid', title: '财务分类', width: 90, align: 'center'}
                , {field: 'capitalfrom', title: '资金来源', width: 90, align: 'center'}
                , {field: 'assfromid', title: '设备来源', width: 90, align: 'center'}
                , {field: 'factory', title: '生产厂商', width: 240, align: 'center'}
                , {field: 'factory_user', title: '生产厂商联系人', width: 150, align: 'center'}
                , {field: 'factory_tel', title: '生产厂商联系电话', width: 150, align: 'center'}
                , {field: 'supplier', title: '供应商', width: 240, align: 'center'}
                , {field: 'supp_user', title: '供应商联系人', width: 120, align: 'center'}
                , {field: 'supp_tel', title: '供应商联系电话', width: 140, align: 'center'}
                , {field: 'repair', title: '维修公司', width: 240, align: 'center'}
                , {field: 'repa_user', title: '维修公司联系人', width: 150, align: 'center'}
                , {field: 'repa_tel', title: '维修联系电话', width: 150, align: 'center'}
                , {field: 'remark', title: '设备备注', width: 200, align: 'center'}
                , {field: 'assorignum', hide: true, title: '设备原编码', width: 160, align: 'center'}
                , {field: 'assorignum_spare', hide: true, title: '设备原编码(备用)', width: 180, align: 'center'}
                , {field: 'assets_level_name', hide: true, title: '管理类别', width: 150, align: 'center'}
                , {field: 'address', hide: true, title: '设备使用位置', width: 120, align: 'center'}
                , {field: 'managedepart', hide: true, title: '管理科室', width: 120, align: 'center'}
                , {field: 'patrol_xc_cycle', hide: true, title: '巡查周期(天)', width: 110, align: 'center'}
                , {field: 'patrol_pm_cycle', hide: true, title: '保养周期(天)', width: 110, align: 'center'}
                , {field: 'quality_cycle', hide: true, title: '质控周期(天)', width: 110, align: 'center'}
                , {field: 'metering_cycle', hide: true, title: '计量周期(天)', width: 110, align: 'center'}
                , {field: 'is_domesticName', hide: true, title: '国产&进口', width: 100, align: 'center'}
                , {field: 'serialnum', hide: true, title: '设备序列号', width: 140, align: 'center'}
                , {field: 'assetsrespon', hide: true, title: '设备负责人', width: 100, align: 'center'}
                , {field: 'factorynum', hide: true, title: '出厂编号', width: 140, align: 'center'}
                , {field: 'factorydate', hide: true, title: '出厂日期', width: 110, sort: true, align: 'center'}
                , {field: 'opendate', hide: true, title: '启用日期', sort: true, width: 110, align: 'center'}
                , {field: 'invoicenum', hide: true, title: '发票编号', width: 120, align: 'center'}
                , {field: 'paytime', hide: true, title: '付款日期', width: 110, align: 'center'}
                , {field: 'pay_statusName', hide: true, title: '是否付清', width: 100, align: 'center'}
                , {field: 'expected_life', hide: true, title: '预计使用年限', sort: true, width: 130, align: 'center'}
                , {field: 'residual_value', hide: true, title: '残净值率', sort: true, width: 100, align: 'center'}
                , {field: 'guarantee_date', hide: true, title: '保修截止日期', width: 120, sort: true, align: 'center'}
                , {field: 'depreciation_method', hide: true, title: '折旧方式', width: 120, align: 'center'}
                , {field: 'depreciable_lives', hide: true, title: '折旧年限', width: 86, align: 'center'}
                , {field: 'registration', hide: true, title: '注册证编号', width: 150, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    minWidth: 220,
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
            ]]
            , filter: {
                cache: true
            }
            , done: function (res, curr, count) {
                all_assids = res.all_assids;
                var pages = this.page.pages;
                var thisId = '#' + this.id;
                if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                    $(thisId).next().css('height', 'auto');
                    $(thisId).next().find('.layui-table-main').css('height', 'auto');
                } else {
                    table.resize(this.id); //重置表格尺寸
                }

                var tableElem = this.elem;
                // table render出来实际显示的table组件
                var tableViewElem = tableElem.next();
                var url = tableViewElem.find('.uploadPics').attr('data-url');
                var tr = $(tableViewElem).find('.layui-table-fixed-r').find('.layui-table-body').find('tr');
                $.each(tr, function (k, v) {
                    var assid = $(v).find('.uploadPics').attr('data-assid');
                    // 渲染当前页面的所有的上传设备图片按钮
                    upload.render({
                        elem: $(v).find('.uploadPics')
                        , url: url
                        , data: {
                            assid: assid,
                            action: 'uploadPic'
                        }
                        , multiple: true
                        , allDone: function (obj) {
                            //当文件全部被提交后，才触发
                            if (obj.total == obj.successful) {
                                layer.msg('上传设备图片成功', {icon: 1}, 1000);
                                $.ajax({
                                    type: "POST",
                                    url: url,
                                    data: {count: obj.successful, action: 'uploadPic', assid: assid},
                                    dataType: "json"
                                });
                                setTimeout(function () {
                                    table.reload('assetsLists', {
                                        url: getAssetsList
                                        , where: gloabOptions
                                        , page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                    });
                                }, 2000)
                            }
                        }
                    });
                });
                soulTable.render(this);
                isFirstxu = false;
                var storage = window.localStorage;
                var key = location.pathname + location.hash + this.id;
                if (!storage[key]) {
                    var json = JSON.stringify(this.cols);
                    storage.setItem(key, json);
                }

                //勾选已经选择了的设备
                var tmpArr = [];
                if (storage['exportAssetsID']) {
                    tmpArr = storage['exportAssetsID'].split(",");
                }
                //已勾选的ID
                var tr = $('.layui-table-fixed-l').find('tr');
                $.each(tr, function () {
                    $.each($(this).find('td'), function (k, v) {
                        if ($(v).attr('data-field') == 'assid') {
                            var fassid = $(v).find('div').html();
                            var index = $.inArray(fassid, tmpArr);
                            if (index >= 0) {
                                $(v).prev().find('.layui-unselect').addClass('layui-form-checked')
                            }
                        }
                    });
                });
            }
        });
        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var name = data.elem.name;
                var status = data.elem.checked;
                if (status) {
                    status = false;
                } else {
                    status = true;
                }
                var storage = window.localStorage;
                var key = location.pathname + location.hash + 'assetsLists';
                var json = JSON.parse(storage[key]);
                $.each(json[0], function (i, n) {
                    if (name == n.field) {
                        json[0][i]['hide'] = status;

                    }

                });
                var json = JSON.stringify(json);
                storage.setItem(key, json);

            }
        });

        //勾选复选框动作
        table.on('checkbox(assetsLists)', function (obj) {
            var storage = window.localStorage;
            var key = 'exportAssetsID';
            var tmpArr = [];
            if (storage[key]) {
                tmpArr = storage[key].split(",");
            }
            if (obj.type == 'all') {
                var checkStatus = table.checkStatus('assetsLists');
                var data = checkStatus.data;
                if (data.length == 0) {
                    var tr = $('.layui-table-main').find('tr');
                    $.each(tr, function () {
                        $.each($(this).find('td'), function () {
                            if ($(this).attr('data-field') == 'assid') {
                                tmpArr.remove($(this).find('div').html());
                            }
                        });
                    });
                } else {
                    $.each(data, function (i, n) {
                        var index = $.inArray(n.assid, tmpArr);
                        if (index < 0) {
                            //不存在
                            tmpArr.push(n.assid);
                        }
                    });
                }
            } else {
                var index = $.inArray(obj.data.assid, tmpArr);
                if (index >= 0) {
                    //存在
                    tmpArr.remove(obj.data.assid);
                } else {
                    //不存在
                    tmpArr.push(obj.data.assid);
                }
            }
            storage[key] = tmpArr.toString();
        });

        //如果是任务项闹钟点进来的
        var assidStr = $("input[name='assidStr']").val();
        if (assidStr != '') {
            table.reload('assetsLists', {
                url: getAssetsList
                , where: {assidStr: assidStr}
                , page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function () {
                    $("input[name='assidStr']").val('')
                }
            });
        }

        //操作栏按钮
        table.on('tool(assetsLists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showAssets'://显示主设备详情
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?assid=' + rows.assid]
                    });
                    break;
                case 'editAssets'://编辑主设备详情
                    top.layer.open({
                        id: 'editAssetss',
                        type: 2,
                        title: '编辑设备【' + rows.assets + '】',
                        area: ['1145px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?assid=' + rows.assid],
                        end: function () {
                            if (flag) {
                                table.reload('assetsLists', {
                                    url: getAssetsList
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
                    break;
                case 'deleteAssets':
                    //删除主设备详情
                    //先判断是否可以删除
                    var params = {};
                    var title = $(this).html() + '【' + rows.assets + '】';
                    params['assid'] = rows.assid;
                    params['type'] = 'is_del';
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
                                layer.confirm('设备删除后无法恢复，确定删除吗？', {
                                    btn: ['确定', '取消'],
                                    title: title
                                }, function (index) {
                                    var params = {};
                                    params['assid'] = rows.assid;
                                    params['type'] = 'del';
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
                                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                                    table.reload('assetsLists', {
                                                        url: getAssetsList
                                                        , where: gloabOptions
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

                    break;
                case 'showAssetLabel'://设备便签页
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备标签页',
                        area: ['450px', '380px'],
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?action=showAssetLabel&assid=' + rows.assid]
                    });
                    break;
                case 'increment'://附属设备
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】设备详情信息',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1050px', '100%'],
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?assid=' + rows.assid + '&change=5']
                    });
                    break;
                case 'mainAssets'://所属设备
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】所属设备详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url') + '?assid=' + rows.main_assid]
                    });
                    break;
            }
        });

        //列排序
        table.on('sort(assetsLists)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            orderField = obj.field;
            orderType = obj.type;
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('assetsLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //搜索按钮
        form.on('submit(assetsListsSearch)', function (data) {
            lookupGetAssetsListObj.find(".getAssetsListHighSearchStr").parents(".table-list").hide();
            gloabOptions = data.field;
            gloabOptions.assidStr = '/';
            if (gloabOptions.openDateStartDate && gloabOptions.openDateEndDate) {
                if (gloabOptions.openDateEndDate < gloabOptions.openDateStartDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (gloabOptions.paytimeStartDate && gloabOptions.paytimeEndDate) {
                if (gloabOptions.paytimeEndDate < gloabOptions.paytimeStartDate) {
                    layer.msg('付款时间设置不合理', {icon: 2});
                    return false;
                }
            }
            if (gloabOptions.buy_priceMin) {
                if (gloabOptions.buy_priceMax == '') {
                    layer.msg('请补充设备原值区间最大值', {icon: 2});
                    return false;
                }
            }
            if (gloabOptions.buy_priceMax) {
                if (gloabOptions.buy_priceMin == '') {
                    layer.msg('请补充设备原值区间最小值', {icon: 2});
                    return false;
                }
            }
            gloabOptions.type = '';
            table.reload('assetsLists', {
                url: getAssetsList
                , where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                , page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function (res, curr,count) {
                    all_assids = count;
                    var pages = this.page.pages;
                    var thisId = '#' + this.id;
                    if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else {
                        table.resize(this.id); //重置表格尺寸
                    }

                    var storage = window.localStorage;
                    //勾选已经选择了的设备
                    var tmpArr = [];
                    if (storage['exportAssetsID']) {
                        tmpArr = storage['exportAssetsID'].split(",");
                    }
                    //已勾选的ID
                    var tr = $('.layui-table-fixed-l').find('tr');
                    $.each(tr, function () {
                        $.each($(this).find('td'), function (k, v) {
                            if ($(v).attr('data-field') == 'assid') {
                                var fassid = $(v).find('div').html();
                                var index = $.inArray(fassid, tmpArr);
                                if (index >= 0) {
                                    $(v).prev().find('.layui-unselect').addClass('layui-form-checked')
                                }
                            }
                        });
                    });
                }
            });
            return false;
        });
        table.on('toolbar(assetsLists)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'addAssets'://添加主设备
                    top.layer.open({
                        id: 'addAssetss',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1145px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if (flag) {
                                table.reload('assetsLists', {
                                    url: getAssetsList
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
                    break;
                case 'batchAddAssets'://批量添加主设备
                    top.layer.open({
                        id: 'batchAddAssetss',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table.reload('assetsLists', {
                                url: getAssetsList
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    });
                    break;
                case 'batchEditAssets'://批量编辑主设备
                    top.layer.open({
                        id: 'batchEditAssetss',
                        type: 2,
                        title: $(this).html(),
                        scrollbar: false,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            table.reload('assetsLists', {
                                url: getAssetsList
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    });
                    break;
                case 'exportAssets'://批量导出主设备
                    var storage = window.localStorage;
                    if (!storage['exportAssetsID']) {
                        layer.msg('请选择要导出的设备！', {icon: 2, time: 1000});
                        return false;
                    }
                    var params = {};
                    var fields = '';
                    var field_arr = new Array();
                    $.each($('th'), function (index, e) {
                        if (e.className.indexOf('layui-hide') == '-1' && e.getAttribute("data-field") != 'operation' && e.getAttribute("data-field") != 'assid' && $.inArray(e.getAttribute("data-field"), field_arr) == -1) {
                            fields += e.getAttribute("data-field") + ',';
                            field_arr.push(e.getAttribute("data-field"));
                        }
                    });

                    params.assid = storage['exportAssetsID'];
                    params.fields = fields;
                    params.orderField = orderField;
                    params.orderType = orderType;
                    postDownLoadFile({
                        url: url,
                        data: params,
                        method: 'POST'
                    });
                    break;
                //2023.6.5新增
                case 'test_1':
                    const all_checked = table.checkStatus('assetsLists');
                    const {data: checked} = all_checked
                    let assids;
                    // console.log(assids)
                    let count;
                    if (checked.length > 0) {
                        assids = checked.map(item => item.assid)
                        count = assids.length
                    }else {
                        assids = all_assids
                        count = all_assids.length;
                    }
                    layer.open({
                        type: 1,
                        area: '350px',
                        move:false,
                        shade:0.5,
                        closeBtn:1,
                        resize: false,
                        // shadeClose: true,
                        title: '下推',
                        content: `
          <div class="layui-form" style="margin: 16px;">
            <div class="demo-login-container">
              <div class="layui-form-item">
                <div class="layui-inline">
      <label class="layui-form-label">系统名称：</label>
      <div class="layui-input-inline">
  <select>
      <option value="中科物联网">中科物联网</option>
    </select>
      </div>
    </div>
    </div>
    <div style="margin: 16px;text-align: center;color: red;">提示：将要下推${count}条记录</div>
              </div>
              <div class="layui-form-item" style="display: flex;justify-content: center">
                <button style="flex-grow: 1" class="layui-btn" lay-submit lay-filter="push">开始</button>
                <button style="flex-grow: 1" class="layui-btn layui-btn-primary" id="goBack">返回</button>
              </div>
            </div>
          </div>
        `,
                        success: function () {
                            // 对弹层中的表单进行初始化渲染
                            form.render();
                            // 表单提交事件
                            form.on('submit(push)', function (data) {
                                var field = data.field; // 获取表单字段值
                                $.ajax({
                                    type: "POST",
                                    url: '/A/Lookup/pushData',
                                    data: { assids:assids.join(",")},
                                    // dataType: "json",
                                    success: function () {
                                        layer.closeAll()
                                        layer.alert(`推送了${count}条记录`);
                                    }
                                });
                                return false; // 阻止默认 form 跳转
                            });

                            $("#goBack").on('click', function () {
                                layer.closeAll()
                            });
                        }
                    })
                    break;
            }
        });

        //设备名称搜索建议
        $("#getAssetsListAssets").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#getAssetsListCategory").bsSuggest(
            returnCategory('', position)
        );


        //设备编号搜索建议
        $("#getAssetsListAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='getAssetsListAssets']").val(data.assets);
            $("input[name='getAssetsListAssorignum']").val(data.assorignum);
        });

        //设备原编号搜索建议
        $("#getAssetsListAssorignum").bsSuggest(
            returnAssets('assets', 'assorignum')
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="getListAssetsNum"]').val(data.assnum);
            $('input[name="getListAssets"]').val(data.assets);
        });

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
                        highSearchStrHtml = storage['highSearchStr'];
                        lookupGetAssetsListObj.find(".getAssetsListHighSearchStr").parents(".table-list").show();
                        lookupGetAssetsListObj.find(".getAssetsListHighSearchStr").html(highSearchStrHtml);
                        storage.removeItem('highSearch');
                        storage.removeItem('highSearchStr');
                        highSearch.type = 'highSearch';
                        table.reload('assetsLists', {
                            url: getAssetsList
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
    exports('assets/lookup/getAssetsList', {});
});




