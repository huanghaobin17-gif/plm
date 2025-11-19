layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'suggest', 'formSelects', 'laydate', 'element', 'tablePlug'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, formSelects = layui.formSelects, laydate = layui.laydate, element = layui.element, tablePlug = layui.tablePlug;
        //初始化搜索建议插件
        suggest.search();
        //更新渲染
        form.render();
        //弹窗参数设置
        layer.config(layerParmas());
        //定义一个全局空对象
        var gloabOptions = {};

        lay('.formatDate').each(function () {
            laydate.render({
                elem: this
                , trigger: 'click'
                , type: 'year'
                , format: 'yyyy年'
            });
        });
        //初始化多选
        formSelects.render('', selectParams(1));
        formSelects.btns('', selectParams(2), selectParams(3));
        //初始化时间
        lay('.completeDate').each(function(){
            laydate.render(dateConfig(this));
        });

        element.on('tab(allocationPlanTab)', function () {
            table.resize();
        });
        table.render({
            elem: '#getRecordSearchList'
            , limits: [20, 50, 100]
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , loading: true
            ,title: '设备保养记录查询列表'
            , url: getRecordSearchListUrl //数据接口
            , where: {
                sort: 'assid'
                , order: 'desc'
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
            toolbar: '#Patrol-PatroRecordSearch-getRecordSearchListToolbar',
            defaultToolbar: ['filter']
            , cols: [[
                {
                    type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'
                },
                {
                    field: 'assid',
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
                    field: 'assets',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备名称',
                    width: 180,
                    align: 'center'
                }
                , {
                    field: 'assnum',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '设备编号',
                    width: 160,
                    align: 'center'
                }
                , {field: 'assorignum',hide: get_now_cookie(userid+cookie_url+'/assorignum')=='false'?true:false, title: '设备原编码', width: 160, align: 'center'}
                , {field: 'model',hide: get_now_cookie(userid+cookie_url+'/model')=='false'?true:false, title: '规格/型号', width: 120, align: 'center'}
                , {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '使用科室', width: 160, align: 'center'}
                , {field: 'patrol_xc_cycle',hide: get_now_cookie(userid+cookie_url+'/patrol_xc_cycle')=='false'?true:false, title: '巡查周期(天)', width: 110, align: 'center'}
                , {field: 'patrol_pm_cycle',hide: get_now_cookie(userid+cookie_url+'/patrol_pm_cycle')=='false'?true:false, title: '保养周期(天)', width: 110, align: 'center'}
                , {field: 'cday',hide: get_now_cookie(userid+cookie_url+'/cday')=='false'?true:false, title: '保养即将到期天数', sort: true, width: 160, align: 'center'}
                , {field: 'patrol_nums',hide: get_now_cookie(userid+cookie_url+'/patrol_nums')=='false'?true:false, title: '保养次数', sort: true, width: 105, align: 'center'}
                , {field: 'maintain_nums',hide: get_now_cookie(userid+cookie_url+'/maintain_nums')=='false'?true:false, title: '巡查次数', sort: true, width: 105, align: 'center'}
                , {field: 'patrol_dates',hide: get_now_cookie(userid+cookie_url+'/patrol_dates')=='false'?true:false, title: '历史巡查日期', width: 130, align: 'center',templet: function(d){
                        if (d.patrol_dates == null) {
                            return '';
                        }
                        return '<div><span title="'+d.patrol_dates_all+'">'+d.patrol_dates+'</span></div>';}}
                , {field: 'maintain_dates',hide: get_now_cookie(userid+cookie_url+'/maintain_dates')=='false'?true:false, title: '历史保养日期', width: 130, align: 'center',templet: function(d){
                        if (d.maintain_dates == null) {
                            return '';
                        }
                        return '<div><span title="'+d.maintain_dates_all+'">'+d.maintain_dates+'</span></div>';}}
                , {field: 'pre_patrol_executor',hide: get_now_cookie(userid+cookie_url+'/pre_patrol_executor')=='false'?true:false, title: '上一次执行人', width: 130, align: 'center'}
                , {field: 'pre_patrol_result',hide: get_now_cookie(userid+cookie_url+'/pre_patrol_result')=='false'?true:false, title: '上一次巡查结果', width: 130, align: 'center'}
                , {field: 'pre_maintain_result',hide: get_now_cookie(userid+cookie_url+'/pre_maintain_result')=='false'?true:false, title: '上一次保养结果', width: 130, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 90,
                    align: 'center'
                }
            ]], done: function (res, curr) {
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

        //监听工具条
        table.on('tool(getRecordSearchListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showPatrolRecord':
                    var flag = 1;
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】巡查保养保养记录',
                        area: ['1150px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?assid=' + rows.assid + '&action=showPatrolRecord'],
                        end: function () {
                            if (flag) {
                                table.reload('getRecordSearchList', {
                                    url: getRecordSearchListUrl
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    break;
                case 'test':
                    //打印报告
                    top.layer.open({
                        type: 2,
                        offset: 'r',
                        title: '巡查报告',
                        shade: [0.8, '#393D49'],
                        shadeClose: true,
                        anim: 5,
                        scrollbar: false,
                        area: ['950px', '100%'],
                        closeBtn: 1,
                        content: [url + '?action=test&assnum='+rows.assnum]
                    });
                    break;
            }
        });

        //监听排序
        table.on('sort(getRecordSearchListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('getRecordSearchList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //搜索
        form.on('submit(getRecordSearchListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            table.reload('getRecordSearchList', {
                url: getRecordSearchListUrl
                , where: gloabOptions
                , height: 'full-100' //高度最大化减去差值
                , page: {
                    curr: 1 //重新从第 1 页开始
                }, done: function (res, curr) {
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
                }
            });
            return false;
        });

        var assnum = '';
        //打印方式
        form.on('submit(printStyle)', function (data) {
            var params = data.field;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/PatrolRecords/printReports',
                data: params,
                dataType: "html",
                async:false,
                beforeSend:beforeSend,
                success: function (data) {
                    $('#Patrol_report').html(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            var patrol_report=$('#Patrol_report');
            patrol_report.show();
            patrol_report.printArea();
            patrol_report.hide();
            return false;
        });

        //导出科室数据
        form.on('submit(departmentRecord)', function (data) {
            var params = data.field;
            params.type = 'departmentRecord';
            if(!params.departid){
                top.layer.msg('请选择科室', {icon: 2});
                return false;
            }
            postDownLoadFile({
                url: admin_name + '/PatrolRecords/exportReports',
                data: params,
                method: 'POST'
            });
            return false;
        });

        //表格头部工具栏
        table.on('toolbar(getRecordSearchListData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = true,
                checkStatus = table.checkStatus('getRecordSearchList'),
                length = checkStatus.data.length;//获取选中行数量，可作为是否有选中行的条件
            data = checkStatus.data;//获取选中行数量，可作为是否有选中行的条件
            switch(event){
                case 'batchPrintPatrolReport'://批量打印
                    if (length == 0) {
                        top.layer.msg('请先至少勾选一台设备', {icon: 2});
                        return false;
                    } else {
                        var assnums="";
                        var msg=false;
                        $.each(checkStatus.data,function(i,val){
                            if (val.maintain_nums==0) {
                                top.layer.msg('设备号'+val.assnum+'没有保养记录', {icon: 2});
                                msg=true;
                            }
                            assnums=assnums+val.assnum+',';
                        });
                        if (msg) {
                            return false;
                        }
                        $('input[name=assnums]').attr('value',assnums);
                        top.layer.open({
                            type: 1,
                            title: '请选择打印的方式',
                            maxmin: false,
                            area: ['300px'],
                            move: false,
                            content: $('#printStyle')
                        });
                    }
                    break;
                case 'batchExportPatrol'://批量导出
                    if(length == 0){
                        top.layer.msg('请先至少勾选一台设备',{icon:2});
                        return false;
                    }else {
                        var assid = '';
                        var params = {};
                        for (i = 0, len = data.length; i < len; i++) {
                            assid += data[i]['assid'] + ',';
                        }
                        params.assid = assid;
                        postDownLoadFile({
                            url: admin_name + '/PatrolRecords/exportReports',
                            data: params,
                            method: 'POST'
                        });

                    }

                    return false;
                    break;
                case 'exportDepartPatrol'://批量打印
                    top.layer.open({
                        type: 1,
                        title: '请选择科室打印',
                        maxmin: false,
                        area: ['450px'],
                        move: false,
                        content: $('#departmentRecord')
                    });
                    break;
            }
        });

        //设备名称搜索建议
        $("#getRecordSearchListAssets").bsSuggest(
            returnAssets()
        );

        //设备编号搜索建议
        $("#getRecordSearchListAssnum").bsSuggest(
            returnAssnum()
        );

        //设备原编号搜索建议
        $("#getRecordSearchListAssorignum").bsSuggest(
            returnAssets('assets','assorignum')
        );

    });
    exports('patrol/patrolRecords/getPatrolRecords', {});
});
