layui.define(function (exports) {
    layui.use(['table', 'laydate', 'upload', 'form', 'tablePlug', 'formSelects'], function () {
        var $ = layui.jquery, laydate = layui.laydate, upload = layui.upload, table = layui.table, form = layui.form,
            layer = layui.layer, tablePlug = layui.tablePlug, formSelects = layui.formSelects;
        // 实例化日期
        laydate.render({
            elem: '#batchAddBenefitStartDate',
            festival: true,
            value: new Date()
            , type: 'month'
        });
        laydate.render({
            elem: '#batchAddBenefitEndDate',
            festival: true,
            value: new Date()
            , type: 'month'
        });
        //渲染所有多选下拉
        formSelects.render('getAssetsListDepartment', selectParams(1));
        formSelects.btns('getAssetsListDepartment', selectParams(2), selectParams(3));
        //执行实例
        upload.render({
            elem: '#uploadBenefitFile', //绑定元素
            url: $(this).attr('data-url'),
            title: '上传文件',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            ext: 'xls|xlsx|xlsm',
            type: 'file',
            unwrap: false,
            auto: true,
            data: {
                "type": "upload",
                startDate: $('input[name="startDate"]').val(),
                endDate: $('input[name="endDate"]').val()
            },
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
                        table.reload('batchBenefitLists', {
                            url: admin_name+'/Benefit/batchAddBenefit.html'
                            , where: {"type": "getData"}
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                } else {
                    layer.msg(res.msg, {icon: 2, time: 2000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！', {icon: 2}, 1000);
            }
        });
        //导出模板
        $("#exploreBenefitModel").on('click', function () {
            var startDate = $('input[name="startDate"]').val();
            var endDate = $('input[name="endDate"]').val();
            var department = formSelects.value('getAssetsListDepartment', 'valStr');
            window.location.href = 'batchAddBenefit.html?type=exploreBenefitModel&startDate=' + startDate + '&endDate=' + endDate + '&department=' + department;
        });
        //列表数据
        table.render({
            elem: '#batchBenefitLists'
            //,height: '600'
            , limits: [10, 20, 50, 100, 200]
            , loading: true
            , url: admin_name+'/Benefit/batchAddBenefit.html' //数据接口
            , where: {
                type: 'getData'
                , sort: 'entryDate'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'entryDate' //排序字段，对应 cols 设定的各字段名
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left'
                }, {
                    field: 'tempid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'entryDate',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '录入月份',
                    width: 120,
                    align: 'center'
                }
                , {
                    field: 'assnum',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备编号',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'assets',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备名称',
                    width: 190,
                    align: 'center'
                }
                , {field: 'model', title: '设备型号', width: 170, align: 'center'}
                , {field: 'department', title: '所属科室', width: 120, align: 'center'}
                , {field: 'income', title: '月收入', edit: 'text', width: 110, align: 'center'}

                , {field: 'depreciation_cost', title: '折旧费', edit: 'text', width: 140, align: 'center'}
                , {field: 'material_cost', title: '材料费', edit: 'text', width: 140, align: 'center'}
                , {field: 'maintenance_cost', title: '维保费', edit: 'text', width: 140, align: 'center'}
                , {field: 'management_cost', title: '管理费', edit: 'text', width: 140, align: 'center'}
                , {field: 'operator', title: '操作人员数', edit: 'text', width: 140, align: 'center'}
                , {field: 'comprehensive_cost', title: '综合费', edit: 'text', width: 140, align: 'center'}
                , {field: 'interest_cost', title: '利息支出', edit: 'text', width: 140, align: 'center'}
                , {field: 'work_day', title: '工作天数', edit: 'text', width: 140, align: 'center'}
                , {field: 'work_number', title: '诊疗次数', edit: 'text', width: 130, align: 'center'}
                , {field: 'positive_rate', title: '诊疗阳性次数', edit: 'text', width: 140, align: 'center'}
                , {field: 'operation', fixed: 'right', title: '操作', width: 80, align: 'center'}
            ]]
            , done: function (res) {
                //上传完毕回调
                $.each($('.rquireCoin'), function (k, v) {
                    //因为不能直接帮TD赋值，所以在完成加载时找到异常项 赋值到父DIV
                    $(v).removeClass('rquireCoin').parent('div').addClass('rquireCoin');
                });

            }
        });
        //监听工具条
        table.on('tool(batchBenefitData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = admin_name+'/Benefit/batchAddBenefit.html';
            var params = {};
            params.tempid = data.tempid;
            if (layEvent === 'delTmpBenefit') { //删除
                params.type = 'delTmpBenefit';
                layer.confirm('确定删除该条数据吗？', {
                    icon: 3,
                    title: data.entryDate + '月 ' + data.assets + '收支明细'
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
                                    table.reload('batchBenefitLists', {
                                        url: admin_name+'/Benefit/batchAddBenefit.html'
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
                return false;
            }
        });
        //监听单元格编辑
        table.on('edit(batchBenefitData)', function (obj, e) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            params.tempid = data.tempid;
            params.value = value;
            params.cloum = cloum;
            params.type = 'updateData';
            //不能为空的字段
            var keyvalue = [];
            keyvalue['entryDate'] = '录入月份';
            keyvalue['assnum'] = '设备编号';
            keyvalue['assets'] = '设备名称';
            keyvalue['model'] = '设备型号';
            keyvalue['department'] = '所属科室';
            keyvalue['income'] = '月收入';
            keyvalue['work_number'] = '诊疗次数';
            keyvalue['depreciation_cost'] = '折旧费';
            keyvalue['material_cost'] = '材料费';
            keyvalue['maintenance_cost'] = '维保费';
            keyvalue['management_cost'] = '管理费';
            keyvalue['operator'] = '操作人员数量';
            keyvalue['comprehensive_cost'] = '综合费';
            keyvalue['interest_cost'] = '利息支出';
            keyvalue['work_day'] = '工作天数';
            keyvalue['positive_rate'] = '诊疗阳性次数';
            var TDobj = $(this);
            var is_do = true;
            var switchType;
            for (var item in keyvalue) {
                switch (cloum) {
                    case 'work_number':
                        switchType = 2;
                        break;
                    case 'work_day':
                        switchType = 2;
                        break;
                    case 'operator':
                        switchType = 2;
                        break;
                    case 'positive_rate':
                        switchType = 2;
                        break;
                    default :
                        switchType = 1;
                        break;
                }
                switch (switchType) {
                    case 1:
                        if (!check_price(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                    case  2:
                        if (!check_num(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                    case 3:
                        if (!check_Percentage(value)) {
                            is_do = false;
                            TDobj.parent().find('div').addClass('rquireCoin');
                        } else {
                            TDobj.parent().find('div').removeClass('rquireCoin');
                        }
                        break;
                }
                if (item == cloum && !is_do) {
                    layer.msg('修改失败！请输入正确的' + keyvalue[cloum] + '！', {icon: 2, time: 2000});
                    return false;
                }
            }
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Benefit/batchAddBenefit.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000});
                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });
        //批量删除数据
        $('#batchDel').on('click', function () {
            var checkStatus = table.checkStatus('batchBenefitLists');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要删除的数据！', {icon: 2, time: 1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'delTmpBenefit';
            for (j = 0, len = data.length; j < len; j++) {
                tempid += data[j]['tempid'] + ',';
            }
            params.tempid = tempid;
            layer.confirm('确定删除选中的数据吗？', {icon: 3, title: '批量删除数据'}, function (index) {
                $.ajax({
                    type: "POST",
                    url: admin_name+'/Benefit/batchAddBenefit.html',
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
                                table.reload('batchBenefitLists', {
                                    url: admin_name+'/Benefit/batchAddBenefit.html'
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
            if ($('.rquireCoin').length != 0) {
                layer.msg('请将有误的数据编辑正确！', {icon: 2, time: 1000});
                return false;
            }
            var checkStatus = table.checkStatus('batchBenefitLists');
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
                url: admin_name+'/Benefit/batchAddBenefit.html',
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
                            table.reload('batchBenefitLists', {
                                url: admin_name+'/Benefit/batchAddBenefit.html'
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
            if ($('.rquireCoin').length != 0) {
                layer.msg('请将有误的数据编辑正确！', {icon: 2, time: 1000});
                return false;
            }
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
                url: admin_name+'/Benefit/batchAddBenefit.html',
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
                            table.reload('batchBenefitLists', {
                                url: admin_name+'/Benefit/batchAddBenefit.html'
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
    exports('controller/benefit/benefit/batchAddBenefit', {});
});
