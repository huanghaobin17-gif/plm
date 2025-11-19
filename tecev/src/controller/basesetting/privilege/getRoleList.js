layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['form', 'suggest', 'laydate', 'table', 'tablePlug'], function () {
        var form = layui.form, suggest = layui.suggest, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;
        laydate.render(dateConfig('#adddate'));
        layer.config(layerParmas());
        //初始化搜索建议插件
        suggest.search();

        form.render();

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        table.render({
            elem: '#roleLists'
            ,limits:[5,10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '角色列表'
            ,url: getRoleList //数据接口
            ,where: {
                sort: 'roleid'
                ,order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            ,initSort: {
                field: 'roleid' //排序字段，对应 cols 设定的各字段名
                ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: '#LAY-BaseSetting-Privilege-getRoleListToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {
                    field: 'roleid',
                    title: '序号',
                    width: 50,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'role',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '角色名称',
                    width: 200,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/role') == 'false' ? true : false
                }
                , {
                    field: 'status',
                    title: '状态',
                    width: 80,
                    sort: true,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/status') == 'false' ? true : false,
                    templet: function (d) {
                        return (d.status == 1) ? '<span style="color:#F581B1;">启用</span>' : '停用';
                    }
                }
                , {
                    field: 'users',
                    title: '用户列表',
                    width: 500,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/users') == 'false' ? true : false,
                    templet: function (d) {
                        return (d.users) ? d.users : '<span style="color:red;">暂未添加用户</span>';
                    }
                }
                , {
                    field: 'module_name',
                    title: '功能模块',
                    width: 650,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/module_name') == 'false' ? true : false,
                    templet: function (d) {
                        return (d.module_name) ? d.module_name : '<span style="color:red;">暂未分配权限</span>';
                    }
                }
                , {
                    field: 'remark',
                    title: '备注',
                    width: 200,
                    align: 'left',
                    hide: get_now_cookie(userid + cookie_url + '/remark') == 'false' ? true : false
                }
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    width: 230,
                    align: 'center'
                }
            ]]
            ,done: function(res, curr, count){
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听工具条
        table.on('tool(roleData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            if(layEvent === 'editUser'){ //编辑
                top.layer.open({
                    id: 'editUsers',
                    type: 2,
                    title: '成员维护【'+rows.role+'】',
                    area: ['75%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: [url+'?roleid='+rows.roleid],
                    end: function () {
                        if(flag){
                            table.reload('roleLists', {
                                url: getRoleList
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
            } else if(layEvent === 'editPri'){ //解绑
                top.layer.open({
                    id: 'editPris',
                    type: 2,
                    title: '权限维护【'+rows.role+'】',
                    area: ['100%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: url+'?roleid='+rows.roleid,
                    end: function () {
                        if(flag){
                            table.reload('roleLists', {
                                url: getRoleList
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
            } else if(layEvent === 'editRole'){ //解绑
                //do somehing
                top.layer.open({
                    type: 2,
                    title: '编辑角色【'+rows.role+'】',
                    area: ['480px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: url+'?roleid='+rows.roleid,
                    end: function () {
                        if(flag){
                            table.reload('roleLists', {
                                url: getRoleList
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
            }else if(layEvent === 'deleteRole'){ //删除
                //do something
                layer.confirm('角色删除后无法恢复，确定删除吗？', {icon: 3, title:$(this).html()+'【'+rows.role+'】'}, function(index){
                    var url = admin_name+'/Privilege/deleteRole';
                    var params = {};
                    params['roleid']  = rows.roleid;
                    $.ajax({
                        type:"POST",
                        url:url,
                        data:params,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend:function(){
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success:function(data){
                            layer.closeAll('loading');
                            if(data.status == 1){
                                layer.msg(data.msg,{icon : 1,time:2000},function(){
                                    table.reload('roleLists', {
                                        url: getRoleList
                                        ,where: gloabOptions
                                        ,page: {
                                            curr: 1 //重新从第 1 页开始
                                        }
                                    });
                                });
                            }else{
                                layer.msg(data.msg,{icon : 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function(){
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 2});
                        }
                    });
                    layer.close(index);
                });
            }else if(layEvent === 'editTarget'){ //解绑
                top.layer.open({
                    id: 'editPris',
                    type: 2,
                    title: '首页统计图表显示权限设置',
                    area: ['800px', '450px'],
                    offset: '50px',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar:false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: url+'?action=target_setting&roleid='+rows.roleid,
                    end: function () {
                        if(flag){
                            table.reload('roleLists', {
                                url: getRoleList
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
            }
        });

        //监听排序
        table.on('sort(roleData)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('roleLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });
        //监听提交
        form.on('submit(searchRole)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('roleLists', {
                url: getRoleList
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        $("#bsSuggestRole").bsSuggest(
            {
                url: admin_name+'/Privilege/getRoleList?type=getRole',
                effectiveFields: ["num", "rolename"],
                searchFields: [ "rolename"],
                effectiveFieldsAlias: {num: "序号",rolename: "角色名称"},
                ignorecase: false,
                showHeader: true,
                listStyle: {
                    "max-height": "375px", "max-width": "380px",
                    "overflow": "auto", "width": "480px", "text-align": "center"
                },
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: "rolename",
                keyField: "rolename",
                clearable: false
            }
        );

        table.on('toolbar(roleData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addRole'://添加角色
                    top.layer.open({
                        id: 'addRoles',
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        area: ['600px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if(flag){
                                table.reload('roleLists', {
                                    url: getRoleList
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
            }
        });
    });
    exports('basesetting/privilege/getRoleList', {});
});


