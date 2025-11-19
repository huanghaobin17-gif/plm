layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'tablePlug'], function () {
        var laydate = layui.laydate, table = layui.table, form = layui.form, suggest = layui.suggest, tablePlug = layui.tablePlug;
        //初始化搜索建议插件
        suggest.search();

        form.render();
        layer.config(layerParmas());

        laydate.render(dateConfig('#examineListStartDate'));
        laydate.render(dateConfig('#examineListEndDate'));

        //第一个实例
        table.render({
            elem: '#examineList'
            //,height: '600'
            , limits: [20, 50, 100]
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , loading: true
            ,title: '科室验收列表'
            , url: examineListUrl //数据接口
            , where: {
                sort: 'check_status'
                , order: 'asc'
            }
            // , initSort: {
            //     field: 'patrolnum' //排序字段，对应 cols 设定的各字段名
            //     , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: 'true',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'cycid',
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
                    field: 'patrol_num',
                    hide: get_now_cookie(userid + cookie_url + '/patrol_num') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '计划编号',
                    width: 150,
                    align: 'center',
                    class: 'patrolnum'
                }
                , {field: 'patrol_name',hide: get_now_cookie(userid+cookie_url+'/patrol_name')=='false'?true:false, title: '计划名称', width: 320, align: 'center'}
                , {field: 'patrol_level',hide: get_now_cookie(userid+cookie_url+'/patrol_level')=='false'?true:false, title: '计划级别', width: 140, align: 'center', templet: function (d) {
                        var name='';
                        switch (d.patrol_level) {
                            case PATROL_LEVEL_RC:
                                name = PATROL_LEVEL_NAME_RC;
                                break;
                            case PATROL_LEVEL_DC:
                                name = PATROL_LEVEL_NAME_DC;
                                break;
                            case PATROL_LEVEL_PM:
                                name = PATROL_LEVEL_NAME_PM;
                                break;
                            default :
                                name = '异常参数';
                        }
                        return name;
                    }
                }
                , {field: 'cycle_name',hide: get_now_cookie(userid+cookie_url+'/cycle_name')=='false'?true:false, title: '周期计划', width: 100, align: 'center'}
                , {field: 'period',hide: get_now_cookie(userid+cookie_url+'/period')=='false'?true:false, title: '期次', width: 90, align: 'center'}
                , {field: 'complete_time',hide: get_now_cookie(userid+cookie_url+'/complete_time')=='false'?true:false, title: '完成日期', width: 110, align: 'center'}
                , {field: 'execute_user',hide: get_now_cookie(userid+cookie_url+'/execute_user')=='false'?true:false, title: '执行人', width: 120, align: 'center'}
                , {field: 'numstatus',hide: get_now_cookie(userid+cookie_url+'/numstatus')=='false'?true:false, title: '执行 / 实际 / 计划设备台次',  width: 200, align: 'center'}
                , {field: 'abnormal',hide: get_now_cookie(userid+cookie_url+'/abnormal')=='false'?true:false, title: '异常项总数 / 明细项', width: 160, align: 'center'}
                , {
                    field: 'operation',
                    title: '状态/操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 120,
                    align: 'center'
                }]]
            , done: function (res, curr) {
                // var pages = this.page.pages;
                // var thisId = '#' + this.id;
                // if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                //     $(thisId).next().css('height', 'auto');
                //     $(thisId).next().find('.layui-table-main').css('height', 'auto');
                // } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                //     $(thisId).next().css('height', 'auto');
                //     $(thisId).next().find('.layui-table-main').css('height', 'auto');
                // } else {
                //     table.resize(this.id); //重置表格尺寸
                // }
                // var table_tr = $('#faultSummaryTable .layui-table:last tr');
                // if(res.code===200){
                //     $.each(res.rows, function (key, value) {
                //         $.each(res.repeat, function (key2, value2) {
                //             if(value2.department==value.department){
                //                 if(value2.sum>0){
                //                     $($(table_tr).find('td:last')[key]).attr('rowspan',value2.sum);
                //                     res.repeat[key2].sum=0;
                //                 }else{
                //                     $(table_tr).find('td:last')[key].remove();
                //                 }
                //             }
                //         });
                //     });
                // }
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
        table.on('tool(examineListData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var flag=1;
            switch (layEvent) {
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
                        title: rows.patrol_name+' - 第'+rows.period+'期',
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name+'/Patrol/examine?id=' + rows.cycid],
                        end: function () {
                            if (flag) {
                                table.reload('examineList', {
                                    url: examineListUrl
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
        table.on('sort(examineListData)', function (obj) {
            table.reload('examineList', {
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
                        var td_patrolnum = LAY_table.find('.layui-table-fixed-l').find('tr').find("td[data-field='patrolnum']");
                        var td_patrolname = LAY_table.find('.layui-table-main').find('tr').find("td[data-field='patrolname']");
                        rowspanTD(res.rows, res.repeat_patrolnum, td_patrolnum, 'patrolnum');
                        rowspanTD(res.rows, res.repeat_patrolname, td_patrolname, 'patrolname');
                    }
                }
            });
        });

        form.on('submit(examineListSearch)', function (data) {
            var table = layui.table;
            var gloabOptions = data.field;
            if (gloabOptions.startDate && gloabOptions.endDate) {
                if (gloabOptions.endDate < gloabOptions.startDate) {
                    layer.msg('周期时段设置不合理', {icon: 2});
                    return false;
                }
            }
            //刷新表格时，默认回到第一页
            table.reload('examineList', {
                url: examineListUrl
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
        $("#examineListPatrolname").bsSuggest(
            returnProject(1)
        );
        //建议性搜索科室
        $("#examineListDepartment").bsSuggest(
            returnDepartment()
        );
        //建议性搜索执行人
        $("#examineListExecutor").bsSuggest(
            returnExecutor()
        );
    });
    exports('patrol/patrol/examineList', {});
});



