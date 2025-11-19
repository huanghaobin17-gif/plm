layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'form', 'tablePlug'], function () {

        layer.config(layerParmas());

        var table = layui.table, suggest = layui.suggest, form = layui.form, tablePlug = layui.tablePlug;

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
        //第一个实例
        table.render({
            elem: '#assetsDicLists'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '设备字典'
            , url: assetsDic //数据接口
            , where: {
                sort: 'dic_assid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'dic_assid' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-BaseSetting-Dictionary-assetsDicToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    field: 'dic_assid',
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
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备名称',
                    width: 180,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false
                }
                , {
                    field: 'category',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '设备分类',
                    width: 220,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/category') == 'false' ? true : false
                }
                , {
                    field: 'assets_category',
                    title: '设备类型',
                    minWidth: 400,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assets_category') == 'false' ? true : false
                }
                , {
                    field: 'unit',
                    title: '单位',
                    width: 80,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/unit') == 'false' ? true : false
                }
                , {
                    field: 'dic_category',
                    title: '字典类别',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/dic_category') == 'false' ? true : false
                }
                , {
                    field: 'remark',
                    title: '备注',
                    width: 300,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/remark') == 'false' ? true : false
                }
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 100,
                    align: 'center'
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听工具条
        table.on('tool(assetsDicData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'edit') { //编辑
                //do somehing
                var flag = 1;
                top.layer.open({
                    id: 'editDictorys',
                    type: 2,
                    title: '修改设备字典【' + rows.assets + '】',
                    area: ['60%', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: [url + '?id=' + rows.dic_assid],
                    end: function () {
                        if (flag) {
                            table.reload('assetsDicLists', {
                                url: assetsDic
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
            } else if (layEvent === 'delete') { //删除
                //do something
                layer.confirm('删除设备字典后无法恢复，确定删除吗？', {
                    icon: 3,
                    title: $(this).html() + '【' + rows.assets + '】'
                }, function (index) {
                    var params = {};
                    params['id'] = rows.dic_assid;
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
                                    table.reload('assetsDicLists', {
                                        url: assetsDic
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
        table.on('sort(assetsDicData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('assetsDicLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        form.render();
        //监听提交
        form.on('submit(searchAssetsDic)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('assetsDicLists', {
                url: assetsDic
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        getAssetsDic();
        /*
         /选择字典类型
         */
        $("#dic_category").bsSuggest(
            returnDicCategory()
        );

        table.on('toolbar(assetsDicData)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch(event){
                case 'addAssetsDic'://发布公告
                    top.layer.open({
                        id: 'addDictorys',
                        type: 2,
                        title: $(this).html(),
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        area: ['700px', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            if (flag) {
                                table.reload('assetsDicLists', {
                                    url: assetsDic
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
                case 'batchAddAssetsDic'://发布公告
                    top.layer.open({
                        id: 'batchAddAssetsDic',
                        type: 2,
                        title: '批量新增设备字典',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['1180px', '100%'],
                        closeBtn: 1,
                        content: url,
                        end: function () {
                            table.reload('assetsDicLists', {
                                url: assetsDic
                                , where: gloabOptions
                                , page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    });
                    break;
            }
        });
    });
    function getAssetsDic() {
        /*
         /选择设备名称
         */
        $("#dicAssetsName").bsSuggest(
            returnDicAssets()
        );
    }
    exports('basesetting/dictionary/assetsDic', {});
});

