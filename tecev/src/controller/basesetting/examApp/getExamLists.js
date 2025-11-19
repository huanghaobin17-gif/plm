layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'laydate', 'form', 'tablePlug'], function () {
        var table = layui.table, laydate = layui.laydate, form = layui.form, tablePlug = layui.tablePlug;
        form.render();
        laydate.render({
            elem: '#exam_time'
            ,type: 'month'
        });
        layer.config(layerParmas());
        laydate.render(dateConfig('#adddate'));


        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        table.render({
            elem: '#examAppLists'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            ,title: '审查列表'
            , url: getExamLists //数据接口
            , where: {
                sort: 'id'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'id' //排序字段，对应 cols 设定的各字段名
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
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            cols: [[ //表头
                {field: 'id',title: '序号',width: 60,style: 'background-color: #f9f9f9;',fixed: 'left',
                    align: 'center',type: 'space',templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }, {field: 'type',title: '申请类型',width: 90,style: 'background-color: #f9f9f9;',fixed: 'left',align: 'center'}
                , {field: 'desc',title: '申请备注',fixed: 'left',style: 'background-color: #f9f9f9;',width: 460,align: 'center'}
                , {field: 'department',title: '设备当前所属科室',width: 160,align: 'center'}
                , {field: 'model',title: '规格型号',width: 160,align: 'center'}
                , {field: 'applicant_user',title: '操作人',width: 100,align: 'center'}
                , {field: 'applicant_time',title: '申请时间',sort: true,width: 160,align: 'center'}
                , {field: 'approval_status',title: '批准状态',width: 100,align: 'center'}
                , {field: 'approval_user',title: '批准人',width: 120,align: 'center'}
                , {field: 'approval_time',title: '批准时间',width: 160,sort: true,align: 'center'}
                , {field: 'back_status',title: '回退状态',width: 100,align: 'center'}
                , {field: 'back_user',title: '回退人',width: 120,align: 'center'}
                , {field: 'back_time',title: '回退时间',width: 160,sort: true,align: 'center'}
                , {field: 'operation',style: 'background-color: #f9f9f9;',title: '操作',fixed: 'right',minWidth: 100,align: 'center'}
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });

        //监听搜索按钮
        form.on('submit(search_exam_app)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            table.reload('examAppLists', {
                url: getExamLists
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //监听工具条
        table.on('tool(examAppData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var type = rows.operation_type;
            var msg = '';
            if(type == 'edit'){
                msg = '修改';
            }else{
                msg = '删除';
            }
            switch (layEvent){
                case 'pass':
                    layer.confirm('确认同意该【'+msg+'】申请吗？', {icon: 3, title: $(this).html()}, function (index) {
                        var params = {};
                        params['id'] = rows.id;
                        params['event'] = layEvent;
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
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('examAppLists', {
                                            url: getExamLists
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
                    break;
                case 'not_pass':
                    layer.confirm('确认驳回该【'+msg+'】申请吗？', {icon: 3, title: $(this).html()}, function (index) {
                        var params = {};
                        params['id'] = rows.id;
                        params['event'] = layEvent;
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
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('examAppLists', {
                                            url: getExamLists
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
                    break;
            }
        });

        //监听排序
        table.on('sort(examAppData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('examAppLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
    });
    exports('basesetting/examApp/getExamLists', {});
});