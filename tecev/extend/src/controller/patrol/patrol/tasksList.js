layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'tablePlug'], function () {
        var laydate = layui.laydate, table = layui.table, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();

        form.render();
        layer.config(layerParmas());

        laydate.render(dateConfig('#tasksListStartDate'));
        laydate.render(dateConfig('#tasksListEndDate'));

        //第一个实例
        table.render({
            elem: '#tasksList'
            //,height: '600'
            , limits: [20, 50, 100]
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , loading: true
            ,title: '任务实施列表'
            , url: tasksListUrl //数据接口
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
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'patrid',
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
                ,{field: 'patrol_name',fixed: 'left',hide: get_now_cookie(userid+cookie_url+'/patrol_name')=='false'?true:false,title: '计划名称',width: 320,align: 'center'}
                ,{field: 'patrol_level_name',hide: get_now_cookie(userid+cookie_url+'/patrol_level_name')=='false'?true:false,title: '计划级别',width: 140,align: 'center'}
                ,{field: 'patrol_date',hide: get_now_cookie(userid+cookie_url+'/patrol_date')=='false'?true:false,title: '计划执行日期',width: 200,align: 'center'}
                ,{field: 'cycle_name',hide: get_now_cookie(userid+cookie_url+'/cycle_name')=='false'?true:false,title: '是否周期计划',width: 120,align: 'center'}
                ,{field: 'total_cycle',hide: get_now_cookie(userid+cookie_url+'/total_cycle')=='false'?true:false,title: '总周期数',width: 100,align: 'center'}
                ,{field: 'current_cycle',hide: get_now_cookie(userid+cookie_url+'/current_cycle')=='false'?true:false,title: '当前周期',width: 100,align: 'center'}
                ,{field: 'patrol_status_name',hide: get_now_cookie(userid+cookie_url+'/patrol_status_name')=='false'?true:false,title: '计划状态',width: 100,align: 'center'}
                ,{field: 'remark',hide: get_now_cookie(userid+cookie_url+'/remark')=='false'?true:false,title: '备注',width: 250,align: 'center'}
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 100,
                    align: 'center'
                }]]
            , done: function (res, curr) {
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
        table.on('tool(tasksListData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var thisUrl = $(this).attr('data-url');
            switch (layEvent) {
                case 'doTask':
                    var flag = 1;
                    top.layer.open({
                        id: 'doTasks',
                        type: 2,
                        title: '巡查任务实施设备清单【' + rows.patrolname + '】',
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [thisUrl + '?cyclenum=' + rows.cyclenum],
                        end: function () {
                            if (flag) {
                                table.reload('tasksList', {
                                    url: tasksListUrl
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
                case 'showPlans':
                var name="";
                  switch (rows.patrol_level) {
                            case PATROL_LEVEL_DC:
                                name = PATROL_LEVEL_NAME_DC;
                                break;
                            case PATROL_LEVEL_RC:
                                name = PATROL_LEVEL_NAME_RC;
                                break;
                            case PATROL_LEVEL_PM:
                                name = PATROL_LEVEL_NAME_PM;
                                break;
                            default :
                                name = '异常参数';
                        }
                    top.layer.open({
                        id: 'showPlans',
                        type: 2,
                        title: name+'：'+rows.patrol_name,
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name+'/Patrol/showPlans?id=' + rows.patrid],
                        end: function () {
                            if (flag) {
                                table.reload('patrolList', {
                                    url: patrolListUrl
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
                case 'examine':
                    var name="";
                  switch (rows.patrol_level) {
                            case PATROL_LEVEL_DC:
                                name = PATROL_LEVEL_NAME_DC;
                                break;
                            case PATROL_LEVEL_RC:
                                name = PATROL_LEVEL_NAME_RC;
                                break;
                            case PATROL_LEVEL_PM:
                                name = PATROL_LEVEL_NAME_PM;
                                break;
                            default :
                                name = '异常参数';
                        }
                    top.layer.open({
                        id: 'examine',
                        type: 2,
                        title: name+'：'+rows.patrolname,
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name+'/Patrol/examine?id=' + rows.patrid],
                        end: function () {
                            if (flag) {
                                table.reload('patrolList', {
                                    url: patrolListUrl
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

            }
        });
        //监听排序
        table.on('sort(tasksListData)', function (obj) {
            table.reload('tasksList', {
                //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                initSort: obj
                , where: {
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res, curr, count) {
                    if(res.rows) {
                        var LAY_table = $('#LAY-Patrol-Patrol-tasksList');
                        var td_patrolnum = LAY_table.find('.layui-table-fixed-l').find('tr').find("td[data-field='patrolnum']");
                        var td_patrolname = LAY_table.find('.layui-table-main').find('tr').find("td[data-field='patrolname']");
                        rowspanTD(res.rows, res.repeat_patrolnum, td_patrolnum, 'patrolnum');
                        rowspanTD(res.rows, res.repeat_patrolname, td_patrolname, 'patrolname');
                    }
                }
            });
        });

        form.on('submit(tasksListSearch)', function (data) {
            var table = layui.table;
            var gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('启用时间设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('tasksList', {
                url: tasksListUrl
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

        //建议性搜索计划名称
        $("#tasksListPatrolname").bsSuggest(
            returnProject()
        );
        //建议性搜索执行人
        $("#tasksListExecutor").bsSuggest(
            returnExecutor()
        );
    });
    exports('patrol/patrol/tasksList', {});
});






