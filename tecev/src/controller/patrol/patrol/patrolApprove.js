layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'suggest', 'laydate', 'tablePlug'], function () {
        var table = layui.table, form = layui.form, suggest = layui.suggest, laydate = layui.laydate, tablePlug = layui.tablePlug;

        //初始化搜索建议插件
        suggest.search();
        form.render();
        layer.config(layerParmas());
        var gloabOptions = {};
        table.render({
            elem: '#patrolApprovelList'
            ,size: 'lg'
            , limits: [5, 10, 20, 50, 100]
            , loading: true
            ,title: '巡查计划审核列表'
            , url: patrolApprove //数据接口
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
            toolbar: '',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {field: 'patrid',title: '序号',width: 60,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                ,{field: 'patrol_name',hide: get_now_cookie(userid+cookie_url+'/patrol_name')=='false'?true:false,title: '计划名称',width: 280,align: 'center'}
                ,{field: 'patrol_date',hide: get_now_cookie(userid+cookie_url+'/patrol_date')=='false'?true:false,title: '计划执行日期',width: 220,align: 'center'}
                ,{field: 'patrol_level_name',hide: get_now_cookie(userid+cookie_url+'/patrol_level_name')=='false'?true:false,title: '级别',width: 140,align: 'center'}
                ,{field: 'assets_nums',hide: get_now_cookie(userid+cookie_url+'/assets_nums')=='false'?true:false,title: '计划设备台账',width: 120,align: 'center'}
                ,{field: 'app_user_status',hide: get_now_cookie(userid+cookie_url+'/app_user_status')=='false'?true:false,title: '审核流程&状态',width: 400,align: 'center'}
                ,{field: 'add_user',hide: get_now_cookie(userid+cookie_url+'/add_user')=='false'?true:false,title: '制定人',width: 100,align: 'center'}
                ,{field: 'add_time',hide: get_now_cookie(userid+cookie_url+'/add_time')=='false'?true:false,title: '制定时间',width: 160,sort: true,align: 'center'}
                ,{field: 'operation',title: '操作',fixed: 'right',minWidth: 100,align: 'center'}
            ]]
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
        table.on('tool(patrolApproveData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent) {
                case 'showPlans':
                    top.layer.open({
                        id: 'showPlans',
                        type: 2,
                        title: rows.patrol_name+'计划查看',
                        area: ['1120px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [admin_name+'/Patrol/showPlans?id=' + rows.patrid],
                        end: function () {
                            if (flag) {
                                table.reload('patrolApprovelList', {
                                    url: patrolApprove
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
        table.on('sort(patrolApproveData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('patrolApprovelList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        form.on('submit(patrolApproveSearch)', function (data) {
            var table = layui.table;
            gloabOptions = data.field;
            //刷新表格时，默认回到第一页
            //table.set(gloabOptions);
            table.reload('patrolApprovelList', {
                url: patrolApprove
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        /*
         /建议性搜索执行人
         */
        $("#patrolApproveExecutor").bsSuggest(
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
    exports('patrol/patrol/patrolApprove', {});
});



