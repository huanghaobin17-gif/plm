layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'laydate', 'tablePlug'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();
        form.render();
        layer.config(layerParmas());
        var gloabOptions = {};
        //录入时间元素渲染
        laydate.render({
            elem: '#date_start' //指定元素
        });
        laydate.render({
            elem: '#date_end' //指定元素
        });
        //第一个实例
        table.render({
            elem: '#patrolList'
            , limits: [20, 50, 100]
            , limit: 20
            , height: 'full-100' //高度最大化减去差值
            , loading: true
            ,title: '巡查保养查询列表'
            , url: patrolListUrl //数据接口
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
                theme: '#428bca', //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Patrol-Patrol-patrolListToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'patrid',
                    title: '序号',
                    style: 'background-color: #f9f9f9;',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                ,{field: 'patrol_name',fixed: 'left',hide: get_now_cookie(userid+cookie_url+'/patrol_name')=='false'?true:false,title: '计划名称',width: 320,align: 'center'}
                ,{field: 'patrol_level_name',hide: get_now_cookie(userid+cookie_url+'/patrol_level_name')=='false'?true:false,title: '计划级别',width: 140,align: 'center'}
                ,{field: 'patrol_date',hide: get_now_cookie(userid+cookie_url+'/patrol_date')=='false'?true:false,title: '计划执行日期',width: 200,align: 'center'}
                ,{field: 'cycle_name',hide: get_now_cookie(userid+cookie_url+'/cycle_name')=='false'?true:false,title: '周期计划',width: 100,align: 'center'}
                ,{field: 'total_cycle',hide: get_now_cookie(userid+cookie_url+'/total_cycle')=='false'?true:false,title: '总周期数',width: 100,align: 'center'}
                ,{field: 'current_cycle',hide: get_now_cookie(userid+cookie_url+'/current_cycle')=='false'?true:false,title: '当前周期',width: 100,align: 'center'}
                ,{field: 'patrol_status_name',hide: get_now_cookie(userid+cookie_url+'/patrol_status_name')=='false'?true:false,title: '计划状态',width: 100,align: 'center'}
                ,{field: 'remark',hide: get_now_cookie(userid+cookie_url+'/remark')=='false'?true:false,title: '备注',width: 250,align: 'center'}
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    minWidth: 110,
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
        table.on('tool(patrolListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'release':
                    top.layer.open({
                        id: 'release',
                        type: 2,
                        title: '计划发布：'+rows.patrol_name,
                        area: ['1080px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?id=' + rows.patrid],
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
                case 'edit':
                    var url=admin_name+'/Patrol/allocationPlan';
                    top.layer.open({
                        id: 'edit',
                        type: 2,
                        title: $(this).html(),
                        area: ['100%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url + '?patrid=' + rows.patrid],
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
                case 'showPlans':
                    top.layer.open({
                        id: 'showPlans',
                        type: 2,
                        title: '计划查看：'+rows.patrol_name,
                        area: ['1180px', '100%'],
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
                    top.layer.open({
                        id: 'showPlans',
                        type: 2,
                        title: rows.patrol_level_name+'：'+rows.patrolname,
                        area: ['1120px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url+'?id=' + rows.patrid],
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
                case 'delete':
                    var url=admin_name+'/Patrol/deletePatrol';
                    var params = {};
                    params.patrid = rows.patrid;
                    layer.confirm('本次申请巡查计划不通过，确认删除该计划吗？', {icon: 3, title: '删除'}, function () {
                        $.ajax({
                            timeout: 5000,
                            dataType: "json",
                            type:"POST",
                            url:url,
                            data:params,
                            async:false,
                            beforeSend:function(){
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success:function(data){
                                if(data.status == 1){
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('patrolList', {
                                            url: patrolListUrl
                                            ,page: {
                                                curr: 1 //重新从第 1 页开始
                                            }
                                        });
                                    });
                                }else{
                                    layer.msg(data.msg,{icon : 2,time:3000});
                                }
                            },
                            //调用出错执行的函数
                            error: function(){
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 2});
                            },
                            complete:function () {
                                layer.closeAll('loading');
                            }
                        });
                    });
                    break;
            }
        });

        //监听排序
        table.on('sort(patrolListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('patrolList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        form.on('submit(patrolListSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('patrolList', {
                url: patrolListUrl
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

        table.on('toolbar(patrolListData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'patrolListAddPatrol'://添加主设备
                    //判断是否开启审核且有增加审核人
                    var data = $.ajax({
                            timeout: 5000,
                            dataType: "json",
                            type:"POST",
                            url:url,
                            data:{"action":"is_Approval"},
                            async:false,
                            //成功返回之后调用的函数
                            success:function(data){
                                if (data.status==-1) {
                                   top.layer.msg(data.msg);
                                   return false;
                                }
                            },
                            //调用出错执行的函数
                            error: function(){
                                //请求出错处理
                                layer.msg('服务器繁忙', {icon: 2});
                            }
                        });
                    if (data.responseJSON.status==-1) {
                    top.layer.msg(data.responseJSON.msg);
                    }else{
                    top.layer.open({
                        id: 'patrolListAddPatrols',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['100%', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            table.reload('patrolList', {
                                url: patrolListUrl
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        },
                        cancel: function () {
                            flag = 0;
                        }
                    });
                    }
                    break;
            }
        });

        /*
         /建议性搜索计划名称
         */
        $("#patrolListPatrolname").bsSuggest(
            returnProject(1)
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='adduser']").val(data.adduser);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='adduser']").val('');
        });
        /*
         /建议性搜索计划制定人
         */
        $("#patrolListAdduser").bsSuggest(
            returnProjectMan()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='patrolname']").val(data.patrolname);
        }).on('onUnsetSelectValue', function () {
            //不正确
            $("input[name='patrolname']").val('');
        });
        /*
         /建议性搜索执行人
         */
        $("#patrolListExecutor").bsSuggest(
            returnExecutor()
        );
    });


    $("#patrolListPatrolname").change(function () {
        if ($("#patrolListPatrolname").val() == '') {
            $("input[name='adduser']").val('');
            $("input[name='patrolname']").val('');
        }
    });
    $("#patrolListAdduser").change(function () {
        if ($("#patrolListAdduser").val() == '') {
            $("input[name='adduser']").val('');
            $("input[name='patrolname']").val('');
        }
    });
    exports('patrol/patrol/patrolList', {});
});



