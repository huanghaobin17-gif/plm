layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'form', 'tablePlug','formSelects'], function () {

        layer.config(layerParmas());

        var table = layui.table, formSelects = layui.formSelects,suggest = layui.suggest, form = layui.form, tablePlug = layui.tablePlug;

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        //初始化搜索建议插件
        suggest.search();
        //渲染所有多选下拉
        formSelects.render('emergencyCategory', selectParams(1));
        formSelects.btns('emergencyCategory', selectParams(2), selectParams(3));

        formSelects.render('emergencyUser', selectParams(1));
        formSelects.btns('emergencyUser', selectParams(2), selectParams(3));
        //第一个实例
        table.render({
            elem: '#emergencyPlanList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 5
            , title: '应急预案'
            , url: emergencyPlanList //数据接口
            , where: {
                sort: 'arempid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'arempid' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Archives-Emergency-addEmergencyToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'arempid',
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
                    field: 'category',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '预案分类',
                    width: 140,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/category') == 'false' ? true : false
                }
                , {
                    field: 'emergency',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '预案名称',
                    width: 300,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/emergency') == 'false' ? true : false
                }
                , {
                    field: 'files',
                    title: '相关文件',
                    minWidth: 500,
                    align: 'left',
                    hide: get_now_cookie(userid + cookie_url + '/files') == 'false' ? true : false
                }
                , {
                    field: 'add_date',
                    title: '添加日期',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/add_date') == 'false' ? true : false
                }
                , {
                    field: 'add_user',
                    title: '添加者',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/add_user') == 'false' ? true : false
                }
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 140,
                    align: 'center'
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听工具条
        table.on('tool(emergencyPlanData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'edit':
                    var flag = 1;
                    top.layer.open({
                        id: 'editEmer',
                        type: 2,
                        title: '修改应急预案【' + rows.emergency + '】',
                        area: ['980px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url + '?arempid=' + rows.arempid],
                        end: function () {
                            if (flag) {
                                table.reload('emergencyPlanList', {
                                    url: emergencyPlanList
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'delete':
                    layer.confirm('确定删除该应急预案？', {
                        icon: 3,
                        title: $(this).html() + '【' + rows.emergency + '】'
                    }, function (index) {
                        var params = {};
                        params['arempid'] = rows.arempid;
                        $.ajax({
                            type: "POST",
                            url: url,
                            data: params,
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                layer.closeAll('loading');
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        table.reload('emergencyPlanList', {
                                            url: emergencyPlanList
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
                            }
                        });
                        layer.close(index);
                    });
                    break;
                case 'showEmer':
                    top.layer.open({
                        id: 'showEmer',
                        type: 2,
                        title: '查看应急预案【' + rows.emergency + '】',
                        area: ['980px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
            }
        });


        form.render();
        //监听提交
        form.on('submit(searchEmergency)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('emergencyPlanList', {
                url: emergencyPlanList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        table.on('toolbar(emergencyPlanData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addEmergency'://发布公告
                    top.layer.open({
                        id: 'addEmergencys',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['980px', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if (flag) {
                                table.reload('emergencyPlanList', {
                                    url: emergencyPlanList
                                    , where: gloabOptions
                                    , page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel: function () {
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });

        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).data('path');
            var name = $(this).data('name');
            var url = admin_name+'/Tool/showFile';
            top.layer.open({
                id: 'showFiles',
                type: 2,
                title: name + '相关文件查看',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url + '?path=' + path + '&filename=' + name]
            });
            return false;
        });
    });
    exports('archives/emergency/emergencyPlanList', {});
});

