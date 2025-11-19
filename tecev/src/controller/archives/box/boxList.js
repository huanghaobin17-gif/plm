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
        formSelects.render('boxListDepartment', selectParams(1));
        formSelects.btns('boxListDepartment', selectParams(2), selectParams(3));
        //第一个实例
        table.render({
            elem: '#boxList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '档案盒管理'
            , url: boxList //数据接口
            , where: {
                sort: 'box_id'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'box_id' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Archives-Box-boxListToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left', style: 'background-color: #f9f9f9;'},
                {
                    field: 'box_id',
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
                    field: 'box_num',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '档案盒编号',
                    width: 160,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/category') == 'false' ? true : false
                }
                , {
                    field: 'file_nums',
                    title: '文件数量',
                    width: 100,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/emergency') == 'false' ? true : false
                }
                , {
                    field: 'toexpire_nums',
                    title: '即将过期数量('+expire_days+'天内)',
                    width: 180,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/files') == 'false' ? true : false
                }
                , {
                    field: 'assets_nums',
                    title: '涵盖设备数量',
                    width: 120,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/add_user') == 'false' ? true : false
                }
                , {
                    field: 'depart_nums',
                    title: '涵盖科室数量',
                    width: 120,
                    //event: 'depart_list',
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/add_user') == 'false' ? true : false
                }
                , {
                    field: 'date_span',
                    title: '文件时间段',
                    minWidth: 200,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/add_date') == 'false' ? true : false
                }
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 120,
                    align: 'center'
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });
        //监听操作栏按钮事件
        table.on('tool(boxData)', function(obj){
            var data = obj.data; //获得当前行数据
            var url = $(this).attr('data-url');
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            switch (layEvent) {
                case 'show_box':
                    top.layer.open({
                        type: 2,
                        title: '【'+data.box_num+'】 档案盒详情',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [boxList+'?action=show_box&box_id='+data.box_id]
                    });
                    break;
                case 'editBox':
                    top.layer.open({
                        id: 'editBox',
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['800px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('boxList', {
                                    url: boxList
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
        //监听头工具栏事件
        table.on('toolbar(boxData)', function(obj){
            var data = obj.data; //获得当前行数据
            var url = $(this).attr('data-url');
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var flag = 1;
            switch (layEvent) {
                case 'addBox':
                    top.layer.open({
                        id: 'addBox',
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['800px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('boxList', {
                                    url: boxList
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
        form.render();
        //监听提交
        form.on('submit(searchBox)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('boxList', {
                url: boxList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //档案编号
        $("#box_num").bsSuggest(
            returnBoxNum()
        );
        //设备名称搜索建议
        $("#boxListAssets").bsSuggest(
            returnAssets()
        );
        //设备编号搜索建议
        $("#boxListAssnum").bsSuggest(
            returnAssnum()
        ).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='boxListAssets']").val(data.assets);
        });
    });
    exports('archives/box/boxList', {});
});

