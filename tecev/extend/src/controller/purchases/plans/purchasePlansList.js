
layui.define(function(exports){
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate, table = layui.table, suggest = layui.suggest, tablePlug = layui.tablePlug;

        //渲染多选下拉
        formSelects.render();
        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //录入时间元素渲染
        laydate.render({
            elem: '#year' //指定元素
            ,type: 'year'
        });


        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#purchasePlansList'
            //,height: '600'
            , limits: [5, 10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: purchasePlansList //数据接口
            , where: {
                sort: 'plans_id',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'plans_id' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Purchases-PurchasePlans-purchasePlansListToolbar',
            defaultToolbar: ['filter','exports']
            , cols: [[ //表头
                {
                    type: 'checkbox',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left'
                },
                {
                    field: 'plans_id',
                    title: '序号',
                    width: 70,
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'project_name',
                    hide: get_now_cookie(userid + cookie_url + '/project_name') == 'false' ? true : false,
                    title: '项目名称',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    width: 230,
                    align: 'center'
                },
                {field: 'department',hide: get_now_cookie(userid+cookie_url+'/department')=='false'?true:false, title: '上报科室', width: 150, align: 'center'},
                {field: 'apply_date',hide: get_now_cookie(userid+cookie_url+'/apply_date')=='false'?true:false, title: '上报日期', width: 110, align: 'center'},
                {field: 'plans_year',hide: get_now_cookie(userid+cookie_url+'/plans_year')=='false'?true:false, title: '计划年份', width: 90, align: 'center'},
                {field: 'plans_status_name',hide: get_now_cookie(userid+cookie_url+'/plans_status_name')=='false'?true:false, title: '计划状态', width: 100, align: 'center'},
                {field: 'plans_start',hide: get_now_cookie(userid+cookie_url+'/plans_start')=='false'?true:false, title: '开始日期', width: 110, align: 'center'},
                {field: 'plans_end',hide: get_now_cookie(userid+cookie_url+'/plans_end')=='false'?true:false, title: '结束日期', width: 110, align: 'center'},
                {field: 'assets_nums',hide: get_now_cookie(userid+cookie_url+'/assets_nums')=='false'?true:false, title: '上报设备数量', width: 120, align: 'center'},
                {field: 'assets_amount',hide: get_now_cookie(userid+cookie_url+'/assets_amount')=='false'?true:false, title: '设备总金额', width: 120, align: 'center'},
                {field: 'add_user',hide: get_now_cookie(userid+cookie_url+'/add_user')=='false'?true:false, title: '创建人', width: 100, align: 'center'},
                {
                    field: 'apply_status_icon',
                    style: 'background-color: #f9f9f9;',
                    hide: get_now_cookie(userid + cookie_url + '/apply_status_icon') == 'false' ? true : false,
                    title: '上报状态',
                    fixed: 'right',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'plans_operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    minWidth: 70,
                    align: 'center'
                }
            ]]
            ,done:function () {
                $(".layui-table").rowspan(2);
                // $(".layui-table").rowspan(3);
                // $(".layui-table").rowspan(4);
                // $(".layui-table").rowspan(5);
                // $(".layui-table").rowspan(6);
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
        //监听搜索
        form.on('submit(plansListSearch)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            //table.set(gloabOptions);
            table.reload('purchasePlansList', {
                url: purchasePlansList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //监听工具条
        table.on('tool(purchasePlansData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'showPlans':
                    top.layer.open({
                        type: 2,
                        title: '查看科室年度采购计划【' + rows.department + '】',
                        area: ['980px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url]
                    });
                    break;
                case 'departReport':
                    top.layer.open({
                        id: 'depart-Report',
                        type: 2,
                        title: '上报科室年度采购计划【' + rows.department + '】',
                        area: ['980px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [url + '?id=' + rows.plans_id],
                        end: function () {
                            if (flag) {
                                table.reload('purchasePlansList', {
                                    url: purchasePlansList
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
                case 'addPlans':
                    top.layer.open({
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['500px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('purchasePlansList', {
                                    url: purchasePlansList
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
        table.on('toolbar(purchasePlansData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'addPlans':
                    top.layer.open({
                        type: 2,
                        title: $(this).html(),
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['500px', '100%'],
                        closeBtn: 1,
                        content: [url],
                        end: function () {
                            if(flag){
                                table.reload('purchasePlansList', {
                                    url: purchasePlansList
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

        //项目名称搜索建议
        $("#plans_project").bsSuggest(
            returnPurPlansProjects()
        );
    });
    exports('purchases/plans/purchasePlansList', {});
});