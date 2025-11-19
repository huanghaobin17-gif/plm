layui.define(function (exports) {
    layui.use(['form','table','laydate'], function () {
        var form = layui.form,table = layui.table,laydate = layui.laydate;
        //初始化渲染
        form.render('checkbox');
        //日常保养年月选择器
        laydate.render({
            elem: '#dc_month'
            ,type: 'month'
        });
        //巡查保养年月选择器
        laydate.render({
            elem: '#rc_month'
            ,type: 'month'
        });
        //预防性维护年月选择器
        laydate.render({
            elem: '#pm_month'
            ,type: 'month'
        });
        table.render({
            elem: '#dcLists'
            , limits: [10, 20, 50, 100, 200]
            , loading: true
            , limit: 10
            ,size: 'sm'
            ,title: '设备【'+assnum+'】日常保养记录'
            , url: url //数据接
            , where: {
                action:'level_list',
                assnum:assnum,
                level:1,
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-PatrolRecords-dcLists',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
                {
                    field: 'execid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'patrol_name',hide:true,title: '计划名称',width: 200,align: 'center'}
                , {field: 'patrol_num',title: '计划编号',width: 150,align: 'center'}
                , {field: 'report_num',hide:true,title: '报告编号',width: 150,align: 'center'}
                , {field: 'patrol_laravel_name',hide:true,title: '保养级别',width: 120,align: 'center'}
                , {field: 'assets',title: '设备名称',width: 130,align: 'center'}
                , {field: 'assnum',title: '设备编号',width: 130,align: 'center'}
                , {field: 'model',title: '规格型号',width: 130,align: 'center'}
                , {field: 'department',title: '使用科室',width: 160,align: 'center'}
                , {field: 'execute_user',title: '执行人',width: 80,align: 'center'}
                , {field: 'asset_status',title: '执行结果',width: 140,align: 'center'}
                , {field: 'finish_time',title: '完成时间',width: 125,align: 'center'}
                , {field: 'unusual_detail',title: '异常项/明细项',width: 110,align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 100,
                    align: 'center'
                }
            ]]
        });
        table.render({
            elem: '#rcLists'
            , limits: [10, 20, 50, 100, 200]
            , loading: true
            , limit: 10
            ,size: 'sm'
            ,title: '设备【'+assnum+'】巡查保养记录'
            , url: url //数据接
            , where: {
                action:'level_list',
                assnum:assnum,
                level:2,
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-PatrolRecords-rcLists',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
                {
                    field: 'execid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'patrol_name',hide:true,title: '计划名称',width: 200,align: 'center'}
                , {field: 'patrol_num',title: '计划编号',width: 150,align: 'center'}
                , {field: 'report_num',hide:true,title: '报告编号',width: 150,align: 'center'}
                , {field: 'patrol_laravel_name',hide:true,title: '保养级别',width: 120,align: 'center'}
                , {field: 'assets',title: '设备名称',width: 130,align: 'center'}
                , {field: 'assnum',title: '设备编号',width: 130,align: 'center'}
                , {field: 'model',title: '规格型号',width: 130,align: 'center'}
                , {field: 'department',title: '使用科室',width: 160,align: 'center'}
                , {field: 'execute_user',title: '执行人',width: 80,align: 'center'}
                , {field: 'asset_status',title: '执行结果',width: 140,align: 'center'}
                , {field: 'finish_time',title: '完成时间',width: 125,align: 'center'}
                , {field: 'unusual_detail',title: '异常项/明细项',width: 110,align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 100,
                    align: 'center'
                }
            ]]
        });
        table.render({
            elem: '#pmLists'
            , limits: [10, 20, 50, 100, 200]
            , loading: true
            , limit: 10
            ,size: 'sm'
            ,title: '设备【'+assnum+'】预防性维护记录'
            , url: url //数据接
            , where: {
                action:'level_list',
                assnum:assnum,
                level:3
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-PatrolRecords-pmLists',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
                {
                    field: 'execid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'patrol_name',hide:true,title: '计划名称',width: 200,align: 'center'}
                , {field: 'patrol_num',title: '计划编号',width: 150,align: 'center'}
                , {field: 'report_num',hide:true,title: '报告编号',width: 150,align: 'center'}
                , {field: 'patrol_laravel_name',hide:true,title: '保养级别',width: 120,align: 'center'}
                , {field: 'assets',title: '设备名称',width: 130,align: 'center'}
                , {field: 'assnum',title: '设备编号',width: 130,align: 'center'}
                , {field: 'model',title: '规格型号',width: 130,align: 'center'}
                , {field: 'department',title: '使用科室',width: 160,align: 'center'}
                , {field: 'execute_user',title: '执行人',width: 80,align: 'center'}
                , {field: 'asset_status',title: '执行结果',width: 140,align: 'center'}
                , {field: 'finish_time',title: '完成时间',width: 125,align: 'center'}
                , {field: 'unusual_detail',title: '异常项/明细项',width: 110,align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 100,
                    align: 'center'
                }
            ]]
        });

        //监听工具条
        table.on('tool(dcData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var cycid = $(this).attr('data-id');
            switch (layEvent){
                case 'showRecord':
                    top.layer.open({
                        type: 2,
                        offset: 'r',
                        title: '日常保养【'+rows.patrol_num+'】保养报告',
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        scrollbar: false,
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url]
                    });
                    return false;
                    break;
                case 'downRecord':
                    //下载保养报告
                    var params = {};
                    params.action = 'downpdf';
                    params.assnum = assnum;
                    params.cycid = cycid;
                    postDownLoadFile({
                        url: url,
                        data: params,
                        method: 'POST'
                    });
                    return false;
                    break;
            }
        });
        table.on('toolbar(dcData)', function(obj){
            var event =  obj.event;
            switch(event){
                case 'batchPrintReport'://添加用户
                    var checkStatus = table.checkStatus('dcLists');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if (length == 0) {
                        top.layer.msg('请选择要打印的报告', {icon: 2});
                        return false;
                    }
                    var cycids = '';
                    for (var i = 0; i < length; i++) {
                        var tmpId = checkStatus.data[i]['cycid'];
                        cycids += tmpId + ',';
                    }
                    cycids = cycids.substring(0, cycids.length - 1);
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: admin_name+'/PatrolRecords/printReports',
                        data: {"assnums": assnum, "printStyle": "3", "cycids": cycids},
                        dataType: "html",
                        async: false,
                        beforeSend: beforeSend,
                        success: function (data) {
                            $('#Patrol_report').html(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2}, 1000);
                        },
                        complete: complete
                    });
                    var patrol_report = $('#Patrol_report');
                    patrol_report.show();
                    patrol_report.printArea();
                    patrol_report.hide();
                    return false;
                    break;
            }
        });
        form.on('submit(searchDc)', function (data) {
            var finish_time = data.field.finish_time;
            table.reload('dcLists', {
                url: url //数据接
                ,where: {
                    action:'level_list',
                    assnum:assnum,
                    finish_time:finish_time,
                    level:1
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('tool(rcData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var cycid = $(this).attr('data-id');
            switch (layEvent){
                case 'showRecord':
                    top.layer.open({
                        type: 2,
                        offset: 'r',
                        title: '巡查保养【'+rows.patrol_num+'】保养报告',
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        scrollbar: false,
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url]
                    });
                    return false;
                    break;
                case 'downRecord':
                    //下载保养报告
                    var params = {};
                    params.action = 'downpdf';
                    params.assnum = assnum;
                    params.cycid = cycid;
                    postDownLoadFile({
                        url: url,
                        data: params,
                        method: 'POST'
                    });
                    return false;
                    break;
            }
        });
        table.on('toolbar(rcData)', function(obj){
            var event =  obj.event;
            switch(event){
                case 'batchPrintReport'://添加用户
                    var checkStatus = table.checkStatus('rcLists');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if (length == 0) {
                        top.layer.msg('请选择要打印的报告', {icon: 2});
                        return false;
                    }
                    var cycids = '';
                    for (var i = 0; i < length; i++) {
                        var tmpId = checkStatus.data[i]['cycid'];
                        cycids += tmpId + ',';
                    }
                    cycids = cycids.substring(0, cycids.length - 1);
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: admin_name+'/PatrolRecords/printReports',
                        data: {"assnums": assnum, "printStyle": "3", "cycids": cycids},
                        dataType: "html",
                        async: false,
                        beforeSend: beforeSend,
                        success: function (data) {
                            $('#Patrol_report').html(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2}, 1000);
                        },
                        complete: complete
                    });
                    var patrol_report = $('#Patrol_report');
                    patrol_report.show();
                    patrol_report.printArea();
                    patrol_report.hide();
                    return false;
                    break;
            }
        });
        form.on('submit(searchRc)', function (data) {
            var finish_time = data.field.finish_time;
            table.reload('rcLists', {
                url: url //数据接
                ,where: {
                    action:'level_list',
                    assnum:assnum,
                    finish_time:finish_time,
                    level:2
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('tool(pmData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            var cycid = $(this).attr('data-id');
            switch (layEvent){
                case 'showRecord':
                    top.layer.open({
                        type: 2,
                        offset: 'r',
                        title: '预防性维护【'+rows.patrol_num+'】保养报告',
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        scrollbar: false,
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url]
                    });
                    return false;
                    break;
                case 'downRecord':
                    //下载保养报告
                    var params = {};
                    params.action = 'downpdf';
                    params.assnum = assnum;
                    params.cycid = cycid;
                    dpdf(url,params)
                    // postDownLoadFile({
                    //     url: url,
                    //     data: params,
                    //     method: 'POST'
                    // });
                    // return false;
                    break;
            }
        });
        table.on('toolbar(pmData)', function(obj){
            var event =  obj.event;
            switch(event){
                case 'batchPrintReport'://添加用户
                    var checkStatus = table.checkStatus('pmLists');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if (length == 0) {
                        top.layer.msg('请选择要打印的报告', {icon: 2});
                        return false;
                    }
                    var cycids = '';
                    for (var i = 0; i < length; i++) {
                        var tmpId = checkStatus.data[i]['cycid'];
                        cycids += tmpId + ',';
                    }
                    cycids = cycids.substring(0, cycids.length - 1);
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: admin_name+'/PatrolRecords/printReports',
                        data: {"assnums": assnum, "printStyle": "3", "cycids": cycids},
                        dataType: "html",
                        async: false,
                        beforeSend: beforeSend,
                        success: function (data) {
                            $('#Patrol_report').html(data);
                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2}, 1000);
                        },
                        complete: complete
                    });
                    var patrol_report = $('#Patrol_report');
                    patrol_report.show();
                    patrol_report.printArea();
                    patrol_report.hide();
                    return false;
                    break;
            }
        });
        form.on('submit(searchPm)', function (data) {
            var finish_time = data.field.finish_time;
            table.reload('pmLists', {
                url: url //数据接
                ,where: {
                    action:'level_list',
                    assnum:assnum,
                    finish_time:finish_time,
                    level:3
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
    });
    exports('controller/patrol/patrolRecords/showPatrolRecord', {});
});


