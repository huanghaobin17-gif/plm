layui.define(function(exports){
    layui.use(['form', 'table', 'laydate', 'formSelects', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            ,formSelects = layui.formSelects
            , table = layui.table, tablePlug = layui.tablePlug;
        //渲染多选下拉
        formSelects.render();
        laydate.render({
            elem: '#year' //指定元素
            ,type: 'year'
        });
        form.render();
        table.render({
            elem: '#purFeeStatisLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '采购费用统计列表'
            ,url: purFeeStatis //数据接口
            ,where: {
                action:'getLists',
                year:year
            } //如果无需传递额外参数，可不加该参数
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

            ,defaultToolbar: ['exports']
            ,page: true //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    rowspan:"4",
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'department', fixed: 'left', title: '采购科室', width: 180, rowspan: "4", align: 'center'},
                {field: 'year', title: '统计年份', width: 100, rowspan: "4", align: 'center'},
                {field: 'nums', align: 'center', width: 120, rowspan: "4", title: '设备总数'}
                , {field: 'total_price', width: 140, rowspan: "4", title: '设备总金额', align: 'center'},
                {field: 'apply_times', title: '申请次数', width: 120, rowspan: "4", align: 'center'},
                {title: '计划类型', colspan: "2", align: 'center'},
                {title: '购置类型', colspan: "3", align: 'center'},
                {title: '是否进口', totalRow: true, colspan: "2", align: 'center'}
            ],
                [
                    {field: 'apply_type_1', title: '计划内',align:'center',totalRow: true, width: 100,rowspan:3}
                    ,{field: 'apply_type_2', title: '计划外',align:'center',totalRow: true, width: 100,rowspan:3}
                    ,{field: 'buy_type_1', title: '报废更新',align:'center',totalRow: true, width: 100,rowspan:3}
                    ,{field: 'buy_type_2', title: '添置',align:'center',totalRow: true, width: 100,rowspan:3}
                    ,{field: 'buy_type_3', title: '新增',align:'center',totalRow: true, minWidth: 100,rowspan:3}
                    ,{
                        field: 'is_import_1',align:'center', title: '是',totalRow: true, width: 90,rowspan:3}
                    ,{field: 'is_import_2',align:'center', title: '否',totalRow: true, width: 90,rowspan:3}
                ]
            ]
        });
        //监听搜索
        form.on('submit(purFeeStatisSearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            table.reload('purFeeStatisLists', {
                url: purFeeStatis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
    });
    exports('statistics/statisPurchases/purFeeStatis', {});
});