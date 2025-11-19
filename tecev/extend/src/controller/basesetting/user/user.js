layui.define(function (exports) {
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'laydate', 'form', 'upload', 'tablePlug'], function () {
        var table = layui.table, suggest = layui.suggest, laydate = layui.laydate, form = layui.form, upload = layui.upload, tablePlug = layui.tablePlug;
        form.render();
        //监听提交
        form.on('submit(searchUser)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            table.reload('userLists', {
                url: getUserList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
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
        //初始化搜索建议插件
        suggest.search();
        table.render({
            elem: '#userLists'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '用户列表'
            , url: getUserList //数据接口
            , where: {
                sort: 'userid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'userid' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-BaseSetting-User-getUserListToolbar',
            defaultToolbar: ['filter', 'exports']
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;', fixed: 'left'}
                , {
                    field: 'userid',
                    title: '序号',
                    width: 65,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'username',
                    hide: get_now_cookie(userid + cookie_url + '/username') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '用户名',
                    width: 120,
                    align: 'center'
                }
                , {
                    field: 'gender', hide: get_now_cookie(userid + cookie_url + '/gender') == 'false' ? true : false, title: '性别', width: 75, sort: true, align: 'center', templet: function (d) {
                        return (d.gender == 1) ? '男' : '女';
                    }
                }
                , {field: 'telephone', hide: get_now_cookie(userid + cookie_url + '/telephone') == 'false' ? true : false, title: '联系电话', width: 120, align: 'center'}
                , {field: 'jobdepartment', hide: get_now_cookie(userid + cookie_url + '/jobdepartment') == 'false' ? true : false, title: '工作科室', width: 140, align: 'center'}
                , {field: 'department', hide: get_now_cookie(userid + cookie_url + '/department') == 'false' ? true : false, title: '管理科室', width: 260, align: 'center'}
                , {
                    field: 'roles', hide: get_now_cookie(userid + cookie_url + '/roles') == 'false' ? true : false, title: '所属角色', width: 200, align: 'center', templet: function (d) {
                        return (d.roles) ? d.roles : '<span style="color:red;">暂未分配角色</span>';
                    }
                }
                , {
                    field: 'status', hide: get_now_cookie(userid + cookie_url + '/status') == 'false' ? true : false, title: '状态', width: 75, sort: true, align: 'center', templet: function (d) {
                        return (d.status == 1) ? '<span style="color: #F581B1;">启用</span>' : '停用';
                    }
                }
                , {
                    field: 'isbinding', hide: get_now_cookie(userid + cookie_url + '/isbinding') == 'false' ? true : false, title: '绑定微信', width: 90, align: 'center', templet: function (d) {
                        return (d.isbinding == '是') ? '<span style="color: #F581B1;">是</span>' : '否';
                    }
                }
                , {
                    field: 'ismanager', hide: get_now_cookie(userid + cookie_url + '/ismanager') == 'false' ? true : false, title: '科室审批负责人', width: 130, align: 'center', templet: function (d) {
                        return (d.ismanager == '是') ? '<span style="color: #F581B1;">是</span>' : '否';
                    }
                }
                , {field: 'remark', hide: get_now_cookie(userid + cookie_url + '/remark') == 'false' ? true : false, title: '备注', width: 160, align: 'left'}
                , {field: 'logintime', hide: get_now_cookie(userid + cookie_url + '/logintime') == 'false' ? true : false, title: '最后登录日期', width: 135, sort: true, align: 'center'}
                , {
                    field: 'users_operation',
                    title: '操作',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    minWidth: 260,
                    align: 'center'
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        var uploadInst = upload.render({
            elem: '#batchAddUser', //绑定元素
            url: admin_name + '/User/batchAddUser',
            title: '批量添加用户',
            method: 'POST',
            contentType: 'application/json; charset=utf-8',
            ext: 'xls|xlsx|xlsm',
            type: 'file',
            unwrap: false,
            auto: true,
            data: {"type": "upload"},
            multiple: true,
            before: function (input) {
                //返回的参数item，即为当前的input DOM对象
                layer.load(2);
            },
            done: function (res) {
                //上传完毕回调
                layer.closeAll('loading');
                if (res.status == 1) {
                    layer.msg(res.msg, {icon: 1, time: 2000}, function () {
                        //刷新表格数据
                        table.reload('userLists', {
                            url: getUserList
                            , where: gloabOptions
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                } else if (res.status == 2) {
                    layer.msg(res.msg, {icon: 3, time: 5000}, function () {
                        //刷新表格数据
                        table.reload('userLists', {
                            url: getUserList
                            , where: gloabOptions
                            , page: {
                                curr: 1 //重新从第 1 页开始
                            }
                        });
                    });
                } else {
                    layer.msg(res.msg, {icon: 2, time: 2500});
                }
            }
            , error: function () {
                //请求异常回调
                layer.closeAll('loading');
                layer.msg('网络异常，请稍后再重试！', {icon: 2}, 1000);
            }
        });


        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
            //
        });
        //监听工具条
        table.on('tool(userData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'showPrivi') { //编辑
                top.layer.open({
                    type: 2,
                    title: '查看用户权限【' + rows.username + '】',
                    area: ['940px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: [url]
                });
            } else if (layEvent === 'edit') { //编辑
                var flag = 1;
                top.layer.open({
                    id: 'editusers',
                    type: 2,
                    title: '修改用户【' + rows.username + '】',
                    area: ['60%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: [url + '?userid=' + rows.userid],
                    end: function () {
                        if (flag) {
                            table.reload('userLists', {
                                url: getUserList
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
            } else if (layEvent === 'unbundling') { //解绑
                layer.confirm('解除绑定后会导致该用户无法收取微信消息，确定解绑？', {icon: 3, title: $(this).html()}, function (index) {
                    var params = {};
                    params['userid'] = rows.userid;
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
                                    table.reload('userLists', {
                                        url: getUserList
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
            } else if (layEvent === 'clearLoginTimes') {
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {username: rows.username},
                    //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                    beforeSend: function () {
                        layer.load(1);
                    },
                    //成功返回之后调用的函数
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                table.reload('userLists', {
                                    url: getUserList
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
            } else if (layEvent === 'delete') { //删除
                //do something
                layer.confirm('用户删除后无法恢复，确定删除吗？', {
                    icon: 3,
                    title: $(this).html() + '【' + rows.username + '】'
                }, function (index) {
                    var params = {};
                    params['userid'] = rows.userid;
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
                            layer.closeAll('loading');
                            if (data.status == 1) {
                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                    table.reload('userLists', {
                                        url: getUserList
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
            }
        });

        //监听排序
        table.on('sort(userData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('userLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //下载文件
        $(".downloadConfirmReport").on('click', function () {
            var params = {};
            params.path = '/Public/dwfile/adduser.xlsx';
            params.filename = '批量添加用户模板.xlsx';
            postDownLoadFile({
                url: admin_name + '/Tool/downFile',
                data: params,
                method: 'POST'
            });
            return false;
        });

        /*
         /选择用户
         */
        $("#bsSuggestUser").bsSuggest(
            returnUser()
        );

        table.on('toolbar(userData)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'adduser'://添加用户
                    top.layer.open({
                        id: 'addusers',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['780px', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if (flag) {
                                table.reload('userLists', {
                                    url: getUserList
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
                case 'batchDeleteUser'://批量删除用户
                    var checkStatus = table.checkStatus('userLists');
                    //获取选中行数量，可作为是否有选中行的条件
                    var length = checkStatus.data.length;
                    if (length == 0) {
                        top.layer.msg('请选择要删除的用户', {icon: 2});
                        return false;
                    }
                    var id = '';
                    for (var i = 0; i < length; i++) {
                        var tmpId = checkStatus.data[i]['userid'];
                        id += tmpId + ',';
                    }
                    id = id.substring(0, id.length - 1);
                    layer.confirm('用户删除后无法恢复！您确定要删除吗？', {icon: 3, title: $(this).html()}, function (index) {
                        var params = {};
                        params['userid'] = id;
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
                                        table.reload('userLists', {
                                            url: getUserList
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
    });
    exports('basesetting/user/user', {});
});




