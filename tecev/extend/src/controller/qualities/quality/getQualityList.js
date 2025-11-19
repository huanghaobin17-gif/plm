layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'formSelects', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染多选下拉
        formSelects.render();
        suggest.search();
        layer.config(layerParmas());

        //定义一个全局空对象
        var gloabOptions = {},assidCount ={};

        var qualityList = [
            {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
            {
                field: 'qsid',
                title: '序号',
                width: 60,
                fixed: 'left',
                style: 'background-color: #f9f9f9;',
                align: 'center',
                type: 'space',
                templet: function (d) {
                    return d.LAY_INDEX;
                }
            },
            {
                field: 'plan_name',
                hide: get_now_cookie(userid + cookie_url + '/plan_name') == 'false' ? true : false,
                style: 'background-color: #f9f9f9;',
                fixed: 'left',
                title: '计划名称',
                minWidth: 200,
                align: 'center'
            },
            {
                field: 'assnum',
                hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                title: '设备编号',
                width: 140,
                align: 'center'
            },
            {field: 'assets',hide: get_now_cookie(userid+cookie_url+'/assets')=='false'?true:false, title: '设备名称', width: 140, align: 'center'},
            {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格 / 型号', width: 105, align: 'center'},
            {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 140, align: 'center'},
            {field: 'is_cycle_name',hide: get_now_cookie(userid+cookie_url+'/is_cycle_name')=='false'?true:false, title: '周期执行', width: 80, align: 'center'},
            {field: 'cycle_name',hide: get_now_cookie(userid+cookie_url+'/cycle_name')=='false'?true:false, title: '周期(月)', width: 80, align: 'center'},
            {
                field: 'period',
                title: '期次',
                hide: get_now_cookie(userid+cookie_url+'/period')=='false'?true:false,
                width: 80,
                align: 'center',
                templet: function (d) {
                    return d.period > 0 ? '第 '+d.period+' 期' : '';
                }
            },
            {field: 'do_date',hide: get_now_cookie(userid+cookie_url+'/do_date')=='false'?true:false, title: '预计执行日期', sort: true, width: 120, align: 'center'},
            {field: 'test_date',hide: get_now_cookie(userid+cookie_url+'/test_date')=='false'?true:false, title: '上次质控日期', sort: true, width: 120, align: 'center'},
            {field: 'test_user',hide: get_now_cookie(userid+cookie_url+'/test_user')=='false'?true:false, title: '上次检测人', width: 90, align: 'center'},
            {field: 'test_result',hide: get_now_cookie(userid+cookie_url+'/test_result')=='false'?true:false, title: '上次检测结果', width: 110, align: 'center'},
            {
                field: 'username',
                hide: get_now_cookie(userid + cookie_url + '/username') == 'false' ? true : false,
                title: '本次检测人',
                width: 90,
                align: 'center'
            },
            {
                field: 'is_start',
                hide: get_now_cookie(userid+cookie_url+'/is_start')=='false'?true:false,
                title: '执行状态',
                sort:true,
                width: 95,
                align: 'center',
                templet: function (d) {
                    return d.is_start == 0 ? '<span style="color:#FFB800;">未启用</span>' : (d.is_start == 1 ? '<span style="color:#FF5722;">执行中</span>' : (d.is_start == 2 ? '已暂停' : (d.is_start == 3 ? '已完成' : '<span style="color: #01AAED;">已结束</span>'))) ;
                }
            },
            {
                field: 'operation',
                style: 'background-color: #f9f9f9;',
                fixed: 'right',
                title: '操作',
                minWidth: 180,
                align: 'center'
            }
        ];

        table.render({
            elem: '#qualityPlanList'
            ,size:'sm'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '质控计划制定列表'
            , url: getQualityList //数据接口
            // , initSort: {
            //     field: 'plans' //排序字段，对应 cols 设定的各字段名
            //     , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            // }
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
            },
            toolbar: '#LAY-Qualities-Quality-getQualityListToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [ //表头
                qualityList
            ]
            ,done:function (res) {
                var layuiTable = $(".layui-table");
                layuiTable.rowspan(2);
                layuiTable.rowspan(3);
                assidCount.assid = res.assidCount
            }
        });

        form.on('checkbox', function(data){
            var type=$(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
            var key=data.elem.name;
            var status=data.elem.checked;
            document.cookie=userid+cookie_url+'/'+key+'='+status+"; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        }
           //
        });
        //操作栏按钮
        table.on('tool(qualityPlanData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'start'){
                //启用计划
                var flag = 1;
                top.layer.open({
                    id: 'starts',
                    type: 2,
                    title: '启用【'+rows.plan_name+'--'+rows.assets+'】',
                    area: ['1100px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?qsid='+rows.qsid],
                    end: function () {
                        if(flag){
                            table.reload('qualityPlanList', {
                                url: getQualityList
                                ,where: gloabOptions
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    },
                    cancel:function(){
                        //如果是直接关闭窗口的，则不刷新表格
                        flag = 0;
                    }
                });
            }else if (layEvent === 'layup'){
                //暂停
                layer.confirm('确定暂停改计划吗？', {icon: 3, title: '暂停计划'}, function (index) {
                    var params = {};
                    params['type'] = 'stop';
                    params['qsid'] = rows.qsid;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: url,
                        data: params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend: function () {
                            layer.load(2);
                        },
                        //成功返回之后调用的函数
                        success: function (data) {
                            if (data.status == 1) {
                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                    table.reload('qualityPlanList', {
                                        url: getQualityList
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
                        },
                        complete: function () {
                            layer.closeAll('loading');
                        }
                    });
                    layer.close(index);
                });
            }else if (layEvent === 'edit'){
                //修改计划
                if(rows.is_start == 0){
                    layer.msg('请先启用计划！',{icon : 2,time:1000});
                    return false;
                }
                var flag = 1;
                top.layer.open({
                    id: 'edits',
                    type: 2,
                    title: '修改计划【'+rows.plan_name+'--'+rows.assets+'】',
                    area: ['1100px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?qsid='+rows.qsid],
                    end: function () {
                        if(flag){
                            table.reload('qualityPlanList', {
                                url: getQualityList
                                ,where: gloabOptions
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    },
                    cancel:function(){
                        //如果是直接关闭窗口的，则不刷新表格
                        flag = 0;
                    }
                });
            }else if (layEvent === 'execute'){
                //执行计划
                if(rows.is_start == 0){
                    layer.msg('请先启用计划！',{icon : 2,time:1000});
                    return false;
                }
                var flag = 1;
                top.layer.open({
                    id: 'executes',
                    type: 2,
                    title: '启用【'+rows.plan_name+'--'+rows.assets+'】',
                    area: ['1100px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?qsid='+rows.qsid],
                    end: function () {
                        if(flag){
                            table.reload('qualityPlanList', {
                                url: getQualityList
                                ,where: gloabOptions
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    },
                    cancel:function(){
                        //如果是直接关闭窗口的，则不刷新表格
                        flag = 0;
                    }
                });
            }else if (layEvent === 'showPlan'){
                //显示计划详情
                top.layer.open({
                    type: 2,
                    title: '【'+rows.plan_name+'】详情信息',
                    area: ['1100px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?qsid='+rows.qsid]
                });
            }else if(layEvent === 'showDetail'){
                //查看执行明细
                top.layer.open({
                    type: 2,
                    title: '执行明细【'+rows.plan_name+'--'+rows.assets+'】',
                    area: ['1100px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?qsid='+rows.qsid]
                });
            }
        });

        //监听搜索按钮
        form.on('submit(searchPlan)',function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            gloabOptions.departid = formSelects.value('getQualityListDepartment', 'valStr');
            table.reload('qualityPlanList', {
                url: getQualityList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //监听重置按钮
        form.on('submit(resetPlan)',function (data) {
            $("li").removeClass("selected");
            $("p").find("span").html('');
            // $("p").find("span").attr('class','placeholder');
            gloabOptions.departid = '';
        });

        //监听一键启动按钮
        form.on('submit(batchStart)',function (data) {
            var url = $(this).attr('data-url');
            var checkStatus = table.checkStatus('qualityPlanList');
            var resultData = checkStatus.data;
            if(resultData.length === 0){
                layer.msg('请选择要启动的设备！',{icon : 2,time:2000});
                return false;
            }
            var allnostart = true;
            var notIs_cycle = true;

            for(j = 0,len=resultData.length; j < len; j++) {
                if(parseInt(resultData[j]['is_start']) !== 0){
                    allnostart = false;
                }
                if(parseInt(resultData[j]['is_cycle'])!== 0 && resultData.length>2){
                    notIs_cycle= false;
                }
            }
            if(!allnostart){
                layer.msg('请选择未启用的设备！',{icon : 2,time:2000});
                return false;
            }
            if(!notIs_cycle){
                layer.msg('已设置周期执行的计划不支持多个计划一键启用！',{icon : 2,time:2000});
                return false;
            }

            var qsid = '';
            for(j = 0,len=resultData.length; j < len; j++) {
                qsid += resultData[j]['qsid']+',';
            }
            var flag = 1;
            top.layer.open({
                id: 'batchStarts',
                type: 2,
                title: '批量启用计划',
                area: ['1100px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [url+'?qsid='+qsid],
                end: function () {
                    if(flag){
                        table.reload('qualityPlanList', {
                            url: getQualityList
                            ,where: gloabOptions
                            ,page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
        });

        //监听一键打印模板按钮
        form.on('submit(batchPrintStart)',function (data) {
            var checkStatus = table.checkStatus('qualityPlanList');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要打印模板的设备计划！',{icon : 2,time:2000});
                return false;
            }
            var qsid = '';
            for(j = 0,len=data.length; j < len; j++) {
                if(data[j]['is_start'] == 0){
                    layer.msg('请选择已启用的质控计划！',{icon : 2,time:2000});
                    return false;
                }
                qsid += data[j]['qsid']+',';
            }
            var params = {};
            params.type = 'batchPrint';
            params.qsid = qsid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: getQualityList,
                data: params,
                dataType: "html",
                async:false,
                beforeSend:beforeSend,
                success: function (data) {
                    $('#printStartTem').html(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            var printStartTemObj = $('#printStartTem');
            printStartTemObj.show();
            printStartTemObj.printArea();
            printStartTemObj.hide();
        });

        setTimeout(function(){
            //设备名称搜索建议
            $("#getAssetsPlan").bsSuggest(
                returnAssets('assets_info','assets','','inAssid='+assidCount.assid)
            );
        },1000);
        //保存检测依据
        form.on('submit(saveBasis)', function (data) {
            params = data.field;
            //检测依据
            var basis = data.field['basis'].split("\n");
            $.each(basis,function(k,v){
                basis[k]=$.trim(v);
            });
            params.basis = basis.join(',');
            params.saveOthers = 'basis';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/Quality/addPresetQI',
                data: params,
                dataType: "json",
                async: true,
                beforeSend:beforeSend,
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg('保存成功',{icon : 1},1000);
                        setTimeout(function(){
                            layui.index.render();
                        },2000)
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            return false;
        });

    });
    exports('qualities/quality/getQualityList', {});
});
