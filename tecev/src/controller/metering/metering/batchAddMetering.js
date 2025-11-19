layui.define(function(exports){
    layui.use(['table', 'laydate', 'upload', 'form', 'tablePlug'], function () {
        var $ = layui.jquery, laydate = layui.laydate, upload = layui.upload, table = layui.table, form = layui.form,
            layer = layui.layer, tablePlug = layui.tablePlug;
        //执行实例
        upload.render({
            elem: '#uploadMeteringFile', //绑定元素
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
                        table.reload('batchMeteringList', {
                            url: admin_name+'/Metering/batchAddMetering.html'
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
        $("#exploreMeteringModel").on('click', function () {
            var is_metering = '';
            $("input[name='is_metering']:checked").each(function () {
                is_metering = $(this).val();
            });
            var departid=$('select[name="departid"]').val();
            window.location.href = 'batchAddMetering.html?type=exploreMeteringModel&is_metering='+is_metering+'&departid='+departid;
        });
        //列表数据
        table.render({
            elem: '#batchMeteringList'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , url: admin_name+'/Metering/batchAddMetering.html' //数据接口
            , where: {
                type: 'getData'
                , sort: 'tempid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'tempid' //排序字段，对应 cols 设定的各字段名
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
                    fixed: 'left'
                }, {
                    field: 'tempid',
                    title: '序号',
                    width: 80,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'department', fixed: 'left', title: '科室名称', width: 150, align: 'center'}
                , {field: 'assets', fixed: 'left', title: '设备名称', width: 170, align: 'center'}
                , {field: 'model', title: '设备型号', edit: 'text', width: 120, align: 'center'}
                , {field: 'unit', title: '单位', edit: 'text', width: 100, align: 'center'}
                , {field: 'factory', title: '生产厂商', edit: 'text', width: 150, align: 'center'}
                , {field: 'productid', title: '产品序列号', edit: 'text', width: 120, align: 'center'}
                , {field: 'mcategory', event: 'mcategory', title: '计量分类', edit: 'text', width: 120, align: 'center'}
                , {field: 'cycle', title: '周期', edit: 'text', width: 100, align: 'center'}
                , {field: 'test_way', title: '检定方式  院内/院外', edit: 'text', width: 160, align: 'center'}
                , {field: 'next_date', title: '下次待检日期', edit: 'text', width: 150, align: 'center'}
                , {field: 'respo_user', title: '计量负责任人', edit: 'text', width: 150, align: 'center'}
                , {field: 'remind_day', title: '提前提醒天数', edit: 'text', width: 140, align: 'center'}
                , {field: 'status', title: '计划状态  启用/暂停', edit: 'text', width: 160, align: 'center'}
                , {field: 'remark', title: '备注', edit: 'text', width: 140, align: 'center'}
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
        table.on('tool(batchMeteringData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = admin_name+'/Metering/batchAddMetering.html';
            var params = {};
            var layer_index = 0;
            params.tempid = data.tempid;
            params.type = 'updateData';
            if (layEvent === 'delTmpMetering') { //删除
                params.type = 'delTmpMetering';
                layer.confirm('确定删除该条数据吗？', {
                    icon: 3,
                    title: data.assets + '计量计划'
                }, function (index) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: params,
                        dataType: "json",
                        beforeSend: function () {
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success: function (data) {
                            layer.closeAll('loading');
                            if (data.status == 1) {
                                layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                    table.reload('batchMeteringList', {
                                        url: admin_name+'/Metering/batchAddMetering.html'
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
            } else if (layEvent === 'mcategory') {
                form.render('select');
                layer.open({
                    type: 1,
                    title: '修改设备:' + data.assets + ' 的计量分类',
                    area: ['450px', '300px'],
                    offset: '10px',
                    shade: [0.8, '#393D49'],
                    shadeClose: true,
                    anim: 5,
                    resize: false,
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: $('#mcategoryList'),
                    end: function () {
                        //console.log('end');
                    },
                    success: function (layero, index) {
                        form.render('select');
                        layer_index = index;
                    }
                });
            }
            //监听修改计量分类
            form.on('submit(saveMcategory)', function (data) {
                if (!data.field.mcid) {
                    layer.msg("请选择设备分类！", {icon: 2, time: 2000});
                    return false;
                }
                params.mcid = data.field.mcid;
                var url = admin_name+'/Metering/batchAddMetering.html';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: url,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                layer.close(layer_index);
                                //同步更新表格和缓存对应的值
                                table.reload('batchMeteringList', {
                                    url: admin_name+'/Metering/batchAddMetering.html'
                                    , where: {"type": "getData"}
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
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
        table.on('edit(batchMeteringData)', function (obj, e) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};

            params[cloum] = value;
            params.tempid = data.tempid;
            params.type = 'updateData';


            //需要验证的字段
            var keyvalue = [];
            keyvalue['next_date'] = '下次待检日期(必须大于于当天)';
            keyvalue['cycle'] = '周期';
            keyvalue['test_way'] = '检定方式';
            keyvalue['status'] = '计划状态';
            keyvalue['remind_day'] = '提前提醒天数';

            var TDobj = $(this).parent().find('div');
            var is_do = true;
            for (var item in keyvalue) {
                if (cloum === 'cycle') {
                    if (value !== '') {
                        if (!check_num(value)) {
                            is_do = false;
                            TDobj.addClass('rquireCoin');
                        } else {
                            TDobj.removeClass('rquireCoin');
                        }
                    }
                }
                if (cloum === 'next_date') {
                    if (!value) {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        var dateReg = /^\d{4}(-)\d{1,2}\1\d{1,2}$/;
                        if (!dateReg.test(value)) {
                            is_do = false;
                            TDobj.addClass('rquireCoin');
                        } else {
                            var dB = new Date(value.replace(/-/g, "/"));
                            if (new Date() > dB) {
                                is_do = false;
                                TDobj.addClass('rquireCoin');
                            } else {
                                TDobj.removeClass('rquireCoin');
                            }
                        }
                    }
                }
                if (cloum === 'test_way') {
                    if (value !== '院内' && value !== '院外') {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        TDobj.removeClass('rquireCoin');
                    }
                }
                if (cloum === 'status') {
                    if (value !== '启用' && value !== '暂停') {
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    } else {
                        TDobj.removeClass('rquireCoin');
                    }
                }
                if(cloum==='remind_day'){
                    if(value<=0){
                        is_do = false;
                        TDobj.addClass('rquireCoin');
                    }else{
                        TDobj.removeClass('rquireCoin');

                    }
                }
                if (item === cloum && !is_do) {
                    layer.msg('修改失败！请输入正确的' + keyvalue[cloum] + '！', {icon: 2, time: 2000});
                    return false;
                }
            }
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Metering/batchAddMetering.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        TDobj.removeClass('rquireCoin');
                        layer.msg(data.msg, {icon: 1, time: 2000});
                    } else {
                        TDobj.addClass('rquireCoin');
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
            var checkStatus = table.checkStatus('batchMeteringList');
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要删除的数据！', {icon: 2, time: 1000});
                return false;
            }
            var tempid = '';
            var params = {};
            params.type = 'delTmpMetering';
            for (j = 0, len = data.length; j < len; j++) {
                tempid += data[j]['tempid'] + ',';
            }
            params.tempid = tempid;
            layer.confirm('确定删除选中的数据吗？', {icon: 3, title: '批量删除数据'}, function (index) {
                $.ajax({
                    type: "POST",
                    url: admin_name+'/Metering/batchAddMetering.html',
                    data: params,
                    dataType: "json",
                    beforeSend: function () {
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        layer.closeAll('loading');
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 1000}, function () {
                                table.reload('batchMeteringList', {
                                    url: admin_name+'/Metering/batchAddMetering.html'
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
            console.log($('.rquireCoin'));
            if ($('.rquireCoin').length != 0) {
                layer.msg('请将有误的数据编辑正确！', {icon: 2, time: 1000});
                return false;
            }
            var checkStatus = table.checkStatus('batchMeteringList');
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
                url: admin_name+'/Metering/batchAddMetering.html',
                data: params,
                dataType: "json",
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchMeteringList', {
                                url: admin_name+'/Metering/batchAddMetering.html'
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
                url: admin_name+'/Metering/batchAddMetering.html',
                data: params,
                dataType: "json",
                beforeSend: function () {
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success: function (data) {
                    layer.closeAll('loading');
                    if (data.status == 1) {
                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                            table.reload('batchMeteringList', {
                                url: admin_name+'/Metering/batchAddMetering.html'
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
    exports('controller/metering/metering/batchAddMetering', {});
});
