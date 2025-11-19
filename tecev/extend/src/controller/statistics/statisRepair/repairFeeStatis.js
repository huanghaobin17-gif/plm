layui.define(function(exports){
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'suggest', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , suggest = layui.suggest
            , table = layui.table, tablePlug = layui.tablePlug;
        suggest.search();
        laydate.render({
            elem: '#repairFeeS'
        });
        laydate.render({
            elem: '#repairFeeE'
        });
        form.render();
        table.render({
            elem: '#repairFeeStatisLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '维修费用统计列表'
            ,url: repairFeeStatis //数据接口
            ,where: {
                action:'getLists'
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
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'repnum', fixed: 'left', title: '维修编号', width: 180, align: 'center'},
                {field: 'assnum', align: 'center', width: 160, title: '设备编号'}
                , {field: 'assets', width: 180, title: '设备名称', align: 'center'},
                {field: 'category', width: 160, title: '设备分类', align: 'center'},
                {field: 'department', title: '所属科室', width: 160, align: 'center'},
                {field: 'applicant_department', title: '报修科室', width: 160, align: 'center'},
                {field: 'repair_type_name', title: '维修类型', width: 90, align: 'center'},
                {field: 'fault_type_name', title: '故障类型', width: 250, align: 'center'},
                {field: 'engineer', width: 110, title: '维修工程师', align: 'center'},
                {field: 'overdate', width: 130, sort: true, title: '维修完成日期', align: 'center'},
                {field: 'over_status_name', width: 100, title: '修复状态', align: 'center'},
                {field: 'part_num', width: 100, sort: true, title: '配件数量', align: 'center'},
                {field: 'part_total_price', width: 120, sort: true, title: '配件总价', align: 'center'},
                {field: 'actual_price', width: 120, sort: true, title: '实际维修费用', align: 'center'}
            ]]
        });
        //监听搜索
        form.on('submit(repairFeeStatisSearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            if(gloabOptions.startDate && gloabOptions.endDate){
                if(gloabOptions.startDate > gloabOptions.endDate){
                    layer.msg("请输入合理的日期",{icon : 2,time:1000});
                    return false;
                }
            }
            table.reload('repairFeeStatisLists', {
                url: repairFeeStatis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        form.on('submit(reset)',function () {
            $('input[name="catid"]').val('');
        });
        // 选择分类
        $("#repairFeeStatisCat").bsSuggest(
            returnCategory('',1)
        ).on('onSetSelectValue', function (e, keyword, data) {
            $('input[name="catid"]').val(data.catid);
        }).on('onUnsetSelectValue', function () {
            //不正确
        });
    });
    exports('statistics/statisRepair/repairFeeStatis', {});
});