layui.define(function (exports) {
    //判断搜索建议的位置
    position = '';
    if (Math.floor($("#LAY-Assets-Print-verify .layui-form-item").width() / $("#LAY-Assets-Print-verify .layui-inline").width()) == 3) {
        position = '';
    } else {
        position = 1;
    }
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'laydate', 'form', 'formSelects', 'tablePlug'], function () {
        var table = layui.table, suggest = layui.suggest, laydate = layui.laydate, form = layui.form,
            formSelects = layui.formSelects, tablePlug = layui.tablePlug;
        form.render();

        //渲染所有多选下拉
        formSelects.render('verifyDepartment', selectParams(1));
        formSelects.btns('verifyDepartment', selectParams(2), selectParams(3));
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
        //初始化搜索建议插件
        suggest.search();
        table.render({
            elem: '#verifyLists'
            , limits: [20, 50, 100, 200, 500]
            , loading: true
            , limit: 20
            , title: '核实列表'
            , height: 'full-100' //高度最大化减去差值
            , url: verify //数据接口
            , where: {
                sort: 'assid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
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
            , toolbar: '#LAY-Assets-Print-verifyToolbar'
            , defaultToolbar: ['filter']
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    fixed: 'left'
                }
                , {
                    field: 'assid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'assnum',
                    title: '设备编号',
                    hide: get_now_cookie(userid + cookie_url + '/assnum') == 'false' ? true : false,
                    width: 180,
                    fixed: 'left',
                    align: 'center'
                }
                , {
                    field: 'assets',
                    fixed: 'left',
                    hide: get_now_cookie(userid + cookie_url + '/assets') == 'false' ? true : false,
                    title: '设备名称',
                    width: 180,
                    align: 'center'
                }
                , {
                    field: 'department',
                    hide: get_now_cookie(userid + cookie_url + '/department') == 'false' ? true : false,
                    title: '所属科室',
                    width: 180,
                    align: 'center'
                }
                , {
                    field: 'model',
                    hide: get_now_cookie(userid + cookie_url + '/model') == 'false' ? true : false,
                    title: '设备型号',
                    width: 160,
                    align: 'center'
                }
                , {
                    field: 'assorignum',
                    hide: get_now_cookie(userid + cookie_url + '/assorignum') == 'false' ? true : false,
                    title: '设备原编号',
                    width: 180,
                    align: 'center'
                }
                , {
                    field: 'serialnum',
                    hide: get_now_cookie(userid + cookie_url + '/serialnum') == 'false' ? true : false,
                    title: '设备序列号',
                    width: 160,
                    align: 'center'
                }
                , {
                    field: 'code_status',
                    hide: get_now_cookie(userid + cookie_url + '/code_status') == 'false' ? true : false,
                    title: '打印状态',
                    width: 100,
                    align: 'center',
                    templet: function (d) {
                        return d.code_status == '未打印' ? '<span style="color:red;">' + d.code_status + '</span>' : '<span style="color: #009688;">' + d.code_status + '</span>';
                    }
                }
                , {
                    field: 'print_status',
                    title: '标签状态',
                    hide: get_now_cookie(userid + cookie_url + '/print_status') == 'false' ? true : false,
                    width: 140,
                    sort: true,
                    fixed: 'right',
                    align: 'center',
                    templet: function (d) {
                        return d.print_status == '初始状态' ? '<span >' + d.print_status + '</span>' : '<span style="color:#009688;">' + d.print_status + '</span>';
                    }
                }
                , {
                    field: 'operation',
                    title: '操作',
                    minWidth: 70,
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    align: 'left'
                }

            ]]
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

        //搜索按钮
        form.on('submit(verifySearch)', function (data) {
            gloabOptions = data.field;
            table.reload('verifyLists', {
                url: verify
                , height: 'full-100' //高度最大化减去差值
                , where: gloabOptions
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
        table.on('tool(verifyData)', function (obj) {
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event;
            if (layEvent == 'showFile') {
                var assid = data.assid;
                top.layer.open({
                    id: 'showFiles',
                    type: 2,
                    title: '相关文件查看',
                    scrollbar: false,
                    area: ['50%', '100%'],
                    closeBtn: 1,
                    offset: 'r',//弹窗位置固定在右边
                    content: [admin_name+'/Print/verify?action=showFile&assid=' + assid]
                });
            }
        });
        table.on('toolbar(verifyData)', function (obj) {
            var checkStatus = table.checkStatus(obj.config.id);
            var url = $(this).attr('data-url');
            var layEvent = obj.event;
            var data = checkStatus.data;
            if (data.length == 0) {
                layer.msg('请选择要导出的设备！', {icon: 2, time: 1000});
                return false;
            }
            var assid = '';
            var params = {};
            for (j = 0, len = data.length; j < len; j++) {
                assid += data[j]['assid'] + ',';
            }
            params.assid = assid;
            params.action = 'exportAssets';
            var fields = '';
            var field_arr = new Array();
            $.each($('th'), function (index, e) {
                if (e.className.indexOf('layui-hide') == '-1' && e.getAttribute("data-field") != 'operation' && e.getAttribute("data-field") != 'assid' && $.inArray(e.getAttribute("data-field"), field_arr) == -1) {
                    fields += e.getAttribute("data-field") + ',';
                    field_arr.push(e.getAttribute("data-field"));
                }
            });
            params.fields = fields;
            switch (layEvent) {
                case "exportAssets":
                    postDownLoadFile({
                        url: url,
                        data: params,
                        method: 'POST'
                    });
                    break;
            }
            return false;
        });
        //监听排序
        table.on('sort(verifyData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('verifyLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //设备名称搜索建议
        $("#verifyName").bsSuggest(
            returnAssets()
        );

        //科室搜索建议
        $("#verifyDepartment").bsSuggest(
            returnDepartment()
        );
        //设备编号搜索建议
        $("#verifyAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='getAssetsListAssets']").val(data.assets);
            $("input[name='getAssetsListAssorignum']").val(data.assorignum);
        });
    });
    exports('assets/print/verify', {});
});




