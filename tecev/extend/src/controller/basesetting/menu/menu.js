layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'form', 'treeGrid'], function () {
        layer.config(layerParmas());
        var treeGrid = layui.treeGrid;
        ptable = treeGrid.render({
            elem: '#getMenuLists'
            , url: getMenuLists
            , cellMinWidth: 100
            , idField: 'menuid'//必須字段
            , treeId: 'menuid'//树形id字段名称
            , treeUpId: 'parentid'//树形父id字段名称
            , treeShowName: 'title'//以树形式显示的字段
            , heightRemove: [".dHead", 10]//不计算的高度,表格设定的是固定高度，此项不生效
            , height: '100%'
            , isFilter: false
            , iconOpen: true//是否显示图标【默认显示】
            , isOpenDefault: false//节点默认是展开还是折叠【默认展开】
            , loading: true
            , method: 'POST'
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , cols: [[
                {field: 'menuid',title: '序号',width: 50,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                , {field: 'title', width: 250, title: '菜单名称', edit: 'text', align: 'left'}
                , {field: 'orderID', width: 60, title: '排序', edit: 'text', align: 'center'}
                , {field: 'menu_operation', minWidth: 140, title: '操作', align: 'left'}
            ]]
        });
        //监听工具条
        treeGrid.on('tool(getMenuData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var params = {};
            params['menuid'] = rows.menuid;
            params['action'] = 'updateMenu';
            switch (layEvent) {
                case 'start':
                    layer.confirm('确定启用该功能？', {icon: 3, title: $(this).html()}, function (index) {
                        params['status'] = 1;
                        $.ajax({
                            type: "POST",
                            url: getMenuLists,
                            data: params,
                            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        treeGrid.reload('getMenuLists', {
                                            url: getMenuLists
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
                case 'stop':
                    layer.confirm('确定停用该功能？', {icon: 3, title: $(this).html()}, function (index) {
                        params['status'] = 0;
                        $.ajax({
                            type: "POST",
                            url: getMenuLists,
                            data: params,
                            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        treeGrid.reload('getMenuLists', {
                                            url: getMenuLists
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
                case 'getDetail':
                    getDetail(rows.menuid);
                    break;
            }
        });
        getDetail(0);
        function getDetail(menuid) {
            ptable1 = treeGrid.render({
                elem: '#sonLists'
                , url: getMenuLists
                , where: {
                    action:'getDetail'
                    ,menuid:menuid
                } //如果无需传递额外参数，可不加该参数
                , cellMinWidth: 100
                , idField: 'menuid'//必須字段
                , treeId: 'menuid'//树形id字段名称
                , treeUpId: 'parentid'//树形父id字段名称
                , treeShowName: 'title'//以树形式显示的字段
                , heightRemove: [".dHead", 10]//不计算的高度,表格设定的是固定高度，此项不生效
                , height: '100%'
                , isFilter: false
                , iconOpen: true//是否显示图标【默认显示】
                , isOpenDefault: false//节点默认是展开还是折叠【默认展开】
                , loading: true
                , method: 'POST'
                , response: { //定义后端 json 格式，详细参见官方文档
                    statusName: 'code', //状态字段名称
                    statusCode: '200', //状态字段成功值
                    msgName: 'msg', //消息字段
                    countName: 'total', //总数字段
                    dataName: 'rows' //数据字段
                }
                , cols: [[
                    {
                        field: 'menuid',
                        title: '序号',
                        width: 50,
                        fixed: 'left',
                        align: 'center',
                        type: 'space',
                        templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    }
                    , {field: 'title', width: 250, title: '菜单名称', edit: 'text', align: 'left'}
                    , {field: 'orderID', width: 60, title: '排序', edit: 'text', align: 'center'}
                    , {field: 'son_operation', minWidth: 100, title: '操作', align: 'left'}
                ]]
            });
        }
        //监听单元格编辑
        treeGrid.on('edit(getMenuData)', function (obj) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            params['menuid'] = data.menuid;
            params[cloum] = value;
            params.action = 'updateMenu';
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: getMenuLists,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {

                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });
        //监听单元格编辑
        treeGrid.on('edit(sonData)', function (obj) {
            var value = $.trim(obj.value); //得到修改后的值
            var data = obj.data; //得到所在行所有键值
            var cloum = $.trim(obj.field); //得到字段
            var index = data.LAY_TABLE_INDEX + 1;
            var params = {};
            params['menuid'] = data.menuid;
            params[cloum] = value;
            params.action = 'updateMenu';
            //更新数据库
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: getMenuLists,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {

                    } else {
                        layer.msg(data.msg, {icon: 2, time: 2000});
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2, time: 2000});
                }
            });
        });
        //监听工具条
        treeGrid.on('tool(sonData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var params = {};
            params['menuid'] = rows.menuid;
            params['action'] = 'updateMenu';
            switch (layEvent) {
                case 'start':
                    layer.confirm('确定启用该功能？', {icon: 3, title: $(this).html()}, function (index) {
                        params['status'] = 1;
                        $.ajax({
                            type: "POST",
                            url: getMenuLists,
                            data: params,
                            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        treeGrid.reload('sonLists', {
                                            url: getMenuLists
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
                case 'stop':
                    layer.confirm('确定停用该功能？', {icon: 3, title: $(this).html()}, function (index) {
                        params['status'] = 0;
                        $.ajax({
                            type: "POST",
                            url: getMenuLists,
                            data: params,
                            //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                            beforeSend: function () {
                                layer.load(1);
                            },
                            //成功返回之后调用的函数
                            success: function (data) {
                                if (data.status == 1) {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        treeGrid.reload('sonLists', {
                                            url: getMenuLists
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
        $('.dHead').on('click',function () {
            var treedata=treeGrid.getDataTreeList('getMenuLists');
            treeGrid.treeOpenAll('getMenuLists',!treedata[0][treeGrid.config.cols.isOpen]);
        });
    });

    function openAll() {
        var treedata=treeGrid.getDataTreeList('getMenuLists');
        treeGrid.treeOpenAll(tableId,!treedata[0][treeGrid.config.cols.isOpen]);
    }
    exports('basesetting/menu/menu', {});
});