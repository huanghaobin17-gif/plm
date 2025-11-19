layui.define(function(exports){
    layui.use(['layer', 'form', 'formSelects', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //初始化搜索建议插件
        suggest.search();
        //先更新页面部分需要提前渲染的控件
        form.render();
        //渲染多选下拉
        formSelects.render();
        layer.config(layerParmas());

        //定义一个全局空对象
        var gloabOptions = {},assidCount ={};

        var qualityDetailClonum = [
            {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'},
            {
                field: 'qsid',
                title: '序号',
                width: 50,
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
                title: '质控计划名称',
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
            {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 120, align: 'center'},
            {
                field: 'is_cycle',
                title: '周期执行',
                hide: get_now_cookie(userid+cookie_url+'/is_cycle')=='false'?true:false,
                width: 80,
                align: 'center',
                templet: function (d) {
                    return d.is_cycle == 0 ? '<span style="color:#FF5722;">否</span>' : (d.is_cycle == 1 ? '是' : '') ;
                }
            },
            {
                field: 'cycle',
                title: '周期(月)',
                hide: get_now_cookie(userid+cookie_url+'/cycle')=='false'?true:false,
                width: 80,
                align: 'center',
                templet: function (d) {
                    return d.is_cycle == 0 ? '<span style="color:#FF5722;">无</span>' : d.cycle ;
                }
            },
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
            {field: 'do_date',hide: get_now_cookie(userid+cookie_url+'/do_date')=='false'?true:false, title: '预计执行日期', sort: true, width: 125, align: 'center'},
            {field: 'username',hide: get_now_cookie(userid+cookie_url+'/username')=='false'?true:false, title: '检测人', width: 90, align: 'center'},
            {
                field: 'is_start',
                title: '计划状态',
                hide: get_now_cookie(userid+cookie_url+'/is_start')=='false'?true:false,
                sort: true,
                width: 90,
                align: 'center',
                templet: function (d) {
                    return d.is_start == 1 ? '<span style="color:#FF5722;">执行中</span>' : (d.is_start == 2 ? '<span style="color:#FFB800;">已暂停</span>' : (d.is_start == 3 ? '<span style="color:#2F4056;">已完成</span>' : '<span style="color:#1E9FFF;">已结束</span>'));
                }
            },
            {
                field: 'operation',
                style: 'background-color: #f9f9f9;',
                fixed: 'right',
                title: '操作',
                minWidth: 245,
                align: 'center'
            }
        ];

        table.render({
            elem: '#qualityDetailList'
            , size: 'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '质控明细录入列表'
            , url: qualityDetailList //数据接口
            , where: {
                sort: 'edittime'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            // , initSort: {
            //     field: 'edittime' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Qualities-Quality-qualityDetailListToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [ //表头
                qualityDetailClonum
            ]
            ,done:function (res) {
                 var layuiTable = $(".layui-table");
                 //layuiTable.rowspan(2);
                // layuiTable.rowspan(3);
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
        //监听排序
        table.on('sort(qualityDetailData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('qualityDetailList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //操作栏按钮
        table.on('tool(qualityDetailData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'execute':
                    //执行计划
                    if(rows.is_start == 0){
                        layer.msg('请先启用计划！',{icon : 2,time:1000});
                        return false;
                    }
                    var flag = 1;
                    top.layer.open({
                        id: 'executes',
                        type: 2,
                        title: '执行计划【'+rows.plan_name+'--'+rows.assets+'】',
                        area: ['1100px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?qsid='+rows.qsid],
                        end: function () {
                            if(flag){
                                table.reload('qualityDetailList', {
                                    url: qualityDetailList
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
                    break;
                case 'showPlan':
                    //显示计划详情
                    top.layer.open({
                        type: 2,
                        title: '【'+rows.plan_name+'】详情信息',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?qsid='+rows.qsid]
                    });
                    break;
                case 'scanReport':
                    //查看报告
                    if(rows.is_start <= 1){
                        layer.msg('请先执行计划！',{icon : 2,time:1000});
                        return false;
                    }
                    var picurl = $(this).attr('data-url');
                    if(!picurl){
                        layer.msg('该计划没上传报告！',{icon : 2,time:1000});
                        return false;
                    }
                    top.layer.open({
                        id: 'scanReports',
                        type: 2,
                        title: '【'+rows.plan_name+'】质控报告',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [admin_name+'/Quality/scanPic.html?qsid='+picurl]
                    });
                    break;
                case 'uploadReport':
                    if(rows.is_start <= 1){
                        layer.msg('请先执行计划！',{icon : 2,time:1000});
                        return false;
                    }
                    //上传报告
                    var flag = 1;
                    top.layer.open({
                        id: 'uploadReports',
                        type: 2,
                        title: '【'+rows.plan_name+'】上传质控报告',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [admin_name+'/Quality/setQualityDetail.html?type=uploadpic&qsid='+rows.qsid],
                        end: function () {
                            if(flag){
                                table.reload('qualityDetailList', {
                                    url: qualityDetailList
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
                    break;
                case 'showDetail':
                    //查看执行明细
                    var flag = 1;
                    top.layer.open({
                        type: 2,
                        title: '执行计划【'+rows.plan_name+'--'+rows.assets+'】',
                        area: ['83%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?qsid='+rows.qsid],
                        end: function () {
                            if(flag){
                                table.reload('qualityDetailList', {
                                    url: qualityDetailList
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
                    break;
                case 'printTemp':
                    top.layer.open({
                        id: 'printTemps',
                        type: 2,
                        title: '【'+rows.plan_name+'】详情信息',
                        area: ['900px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?qsid='+rows.qsid]
                    });
                    break;
                case 'editDetail':
                    top.layer.open({
                        id: 'editDetail',
                        type: 2,
                        title: '【'+rows.plan_name+'】修改明细',
                        area: ['83%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?qsid='+rows.qsid]
                    });
                    break;
            }
        });

        //监听搜索按钮
        form.on('submit(searchPlan)',function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            gloabOptions.departid = formSelects.value('qualityDetailListDepartment', 'valStr');
            table.reload('qualityDetailList', {
                url: qualityDetailList
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

        //监听一键打印模板按钮
        form.on('submit(batchPrint)',function (data) {
            var checkStatus = table.checkStatus('qualityDetailList');
            var data = checkStatus.data;
            if(data.length == 0){
                layer.msg('请选择要打印模板的设备计划！',{icon : 2,time:2000});
                return false;
            }
            var qsid = '';
            for(j = 0,len=data.length; j < len; j++) {
                qsid += data[j]['qsid']+',';
            }
            var params = {};
            params.type = 'batchPrint';
            params.qsid = qsid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: qualityDetailList,
                data: params,
                dataType: "html",
                async:false,
                beforeSend:beforeSend,
                success: function (data) {
                    $('#printTem').html(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            var printTemObj = $('#printTem');
            printTemObj.show();
            printTemObj.printArea();
            printTemObj.hide();
        });
        setTimeout(function(){
            //设备名称搜索建议
            $("#getAssetsPlanDetail").bsSuggest(
                returnAssets('assets_info','assets','','inAssid='+assidCount.assid)
            );
        },1000);
        //设备编号搜索建议
        $("#getAssetsPlanDetailAssnum").bsSuggest(
            returnAssnum()
        );
    });
    exports('qualities/quality/qualityDetailList', {});
});

