layui.define(function(exports){
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'formSelects', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            ,formSelects = layui.formSelects
            , table = layui.table, tablePlug = layui.tablePlug;
        laydate.render({
            elem: '#year_trend' //指定元素
            ,type: 'year'
        });
        form.render();
        //渲染多选下拉
        formSelects.render();
        formSelects.render('repairTrendDepartment', selectParams(1));
        formSelects.btns('repairTrendDepartment',selectParams(2));
        getChartData();form.on('select(change_count_type)',function (data) {
            getChartData();
        });
        form.on('select(change_show_type)',function (data) {
            getChartData();
        });

        function getChartData() {
            var engineer_eva = echarts.init(document.getElementById('repair_trend'));
            engineer_eva.clear();
            engineer_eva.showLoading();
            var params = {};
            params.action = 'chart';
            params.show_type = $('select[name="show_type"]').val();
            params.count_type = $('select[name="count_type"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: repairFeeTrend,
                data: params,
                dataType: "json",
                async: true,
                success: function (charData) {
                    if(!$.isEmptyObject(charData)){
                        var engineer_eva = echarts.init(document.getElementById('repair_trend'));
                        engineer_eva.hideLoading();
                        engineer_eva.setOption({
                            // color: ['#3398DB'],
                            title: {
                                text: charData['title']
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                    type : charData['tooltip']['axisPointer']['type']        // 默认为直线，可选为：'line' | 'shadow'
                                }
                            },
                            legend: {
                                data:charData['legend']
                            },
                            grid: {
                                left: '3%',
                                right: '4%',
                                bottom: '3%',
                                containLabel: true
                            },
                            toolbox: {
                                feature: {
                                    saveAsImage: {}
                                }
                            },
                            xAxis: {
                                type: 'category',
                                boundaryGap: false,
                                //axisLabel: {interval:1},//X坐标轴刻度间隔一个显示
                                data: charData['xAxis']['data']
                            },
                            yAxis: {
                                type: 'value',
                                name:charData['yAxis']['name']
                            },
                            series: charData['series']
                        });
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
        }


        table.render({
            elem: '#repairFeeTrendLists'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '维修费用趋势分析'
            ,url: repairFeeTrend //数据接口
            ,where: {
                action:'table'
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
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
                    title:'序号',
                    width:60,
                    fixed: 'left',
                    align:'center',
                    rowspan:"14",
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'department',
                    fixed: 'left',
                    title: '科室名称',
                    totalRowText: '合计',
                    rowspan:"14",
                    width: 160,
                    align:'center'
                },
                {
                    title: '年度汇总',
                    colspan: "3",
                    align:'center'
                },
                {
                    align:'center',
                    colspan: "3",
                    title: '1月'
                },
                {
                    align:'center',
                    colspan: "3",
                    title: '2月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '3月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '4月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '5月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '6月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '7月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '8月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '9月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '10月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '11月'
                },{
                    align:'center',
                    colspan: "3",
                    title: '12月'
                }
            ],
                [
                     {field: 'year_total_num',align:'center', title: '总次数',totalRow: true,totalNums:0, width: 90,rowspan:14}
                    ,{field: 'year_total_hours',align:'center', title: '总工时',totalRow: true, width: 100,rowspan:14}
                    ,{field: 'year_total_price',align:'center', title: '总费用（元）',totalRow: true, width: 140,rowspan:14}
                    ,{field: 'repair_num_1',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_1',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_1',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_2',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_2',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_2',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_3',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_3',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_3',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_4',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_4',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_4',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_5',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_5',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_5',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_6',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_6',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_6',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_7',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_7',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_7',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_8',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_8',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_8',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_9',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_9',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_9',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_10',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_10',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_10',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_11',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_11',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_11',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
                    ,{field: 'repair_num_12',align:'center', title: '次数',totalRow: true,totalNums:0, width: 70,rowspan:14}
                    ,{field: 'repair_hours_12',align:'center', title: '工时',totalRow: true, width: 80,rowspan:14}
                    ,{field: 'repair_price_12',align:'center', title: '费用（元）',totalRow: true, width: 110,rowspan:14}
            ] ]
        });
        //监听搜索
        form.on('submit(repairFeeTrendSearch)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            table.reload('repairFeeTrendLists', {
                url: repairFeeTrend
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
    });
    exports('statistics/statisRepair/repairFeeTrend', {});
});