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
            elem: '#engineerEvaS'
        });
        laydate.render({
            elem: '#engineerEvaE'
        });
        form.render();

        var engineer_eva = echarts.init(document.getElementById('engineer_eva'));
        engineer_eva.clear();
        engineer_eva.showLoading();

        table.render({
            elem: '#engineerEvaLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '工程师评价对比'
            ,url: engineerEva //数据接口
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
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: true //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    rowspan:"5",
                    totalRowText: '合计',
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'engineer',
                    fixed: 'left',
                    title: '工程师名称',
                    width: '14%',
                    rowspan:"5",
                    align:'center'
                },
                {
                    field: 'repair_num',
                    align:'center',
                    width: '12%',
                    rowspan:"5",
                    totalRow: true,
                    totalNums:0,
                    title: '维修次数'
                },
                {
                    title: '技术水平',
                    totalRow: true,
                    totalNums:0,
                    colspan: "3",
                    align:'center'
                }
                ,{
                    title: '响应时效',
                    colspan:"3",
                    align:'center'
                },
                {
                    title: '服务态度',
                    colspan:"3",
                    align:'center'
                },
                {
                    title: '评分总计',
                    colspan:"4",
                    align:'center'
                }
            ],
                [
                    {field: 'technical_level_1',align:'center', title: '好',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'technical_level_2',align:'center', title: '一般',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'technical_level_3',align:'center', title: '差',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'response_efficiency_1', title: '好',align:'center',totalRow: true,totalNums:0, width: 90,rowspan:4}
                    ,{field: 'response_efficiency_2', title: '一般',align:'center',totalRow: true,totalNums:0, width: 90,rowspan:4}
                    ,{field: 'response_efficiency_3', title: '差',align:'center',totalRow: true,totalNums:0, width: 90,rowspan:4}
                    ,{field: 'service_attitude_1',align:'center', title: '好',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'service_attitude_2',align:'center', title: '一般',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'service_attitude_3',align:'center', title: '差',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'score_1',align:'center',totalRow: true,title: '技术水平', width: 120,rowspan:5}
                    ,{field: 'score_2',align:'center',totalRow: true, title: '效应时效', width: 120,rowspan:5}
                    ,{field: 'score_3',align:'center',totalRow: true, title: '服务态度', width: 120,rowspan:5}
                    ,{field: 'score_4',align:'center',totalRow: true, title: '综合得分', width: 120,rowspan:5}
                ]
            ]
            , done: function (res, curr, count) {
                var engineer_eva = echarts.init(document.getElementById('engineer_eva'));
                engineer_eva.clear();
                engineer_eva.showLoading();
                if(!$.isEmptyObject(res.charData)){
                    engineer_eva.hideLoading();
                    engineer_eva.setOption({
                        color: ['#3398DB'],
                        tooltip : {
                            trigger: 'axis',
                            // axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            //     type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            // }
                        },
                        xAxis : [
                            {
                                type : 'category',
                                data : res['charData']['legend_data'],
                                axisTick: {
                                    alignWithLabel: true
                                }
                            }
                        ],
                        yAxis : [
                            {
                                type : 'value'
                            }
                        ],
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '0%',
                            top: '15%',
                            containLabel: true
                        },
                        series : [
                            {
                                name:'综合得分',
                                type:'bar',
                                barWidth: '10%',
                                data:res['charData']['series_data']
                            }
                        ]
                    });
                }else{
                    engineer_eva.hideLoading();
                    $('.hidden-contant').show();
                }
            }
        });

        //监听排序
        table.on('sort(engineerEvaData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('engineerEvaLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,url: engineerEva
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //监听搜索
        form.on('submit(engineerEvaSearch)', function(data){
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
            table.reload('engineerEvaLists', {
                url: engineerEva
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

    });
    exports('statistics/statisRepair/engineerEva', {});
});