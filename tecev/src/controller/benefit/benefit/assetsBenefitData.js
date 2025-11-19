layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'element'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, element = layui.element,
            table = layui.table;

        var income = echarts.init(document.getElementById('income'));
        var cost = echarts.init(document.getElementById('cost'));
        var income_cost = echarts.init(document.getElementById('income_cost'));

        //获取初始化饼图的条件
        laydate.render({
            elem: '#assetsBenefitDataYearDate',
            festival: true
            , type: 'year'

        });
        form.render();

        var params = {};
        params.assnum = $('input[name="assnum"]').val();
        params.year = $('input[name="year"]').val();
        showChart(params);
        //监听搜索按钮
        form.on('submit(assetsBenefitDataSearch)', function (data) {
            var field = data.field;
            showChart(field);
            table.reload('benefitLists', {
                url: getBenefitLists
                , where: {
                    sort: 'entryDate'
                    , order: 'asc'
                    , type: 'getDetail'
                    , assnum: field.assnum
                    , year: field.year
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //显示饼图
        function showChart(params) {
            income.clear();
            cost.clear();
            income_cost.clear();
            //显示一个简单的加载动画
            income.showLoading();
            cost.showLoading();
            income_cost.showLoading();
            // 异步加载数据
            $.post(admin_name+'/Benefit/assetsBenefitData', params).done(function (data) {
                //隐藏加载动画
                income.hideLoading();
                cost.hideLoading();
                income_cost.hideLoading();
                // 填入数据
                $('#total_income').html(0);
                $('#total_cost').html(0);
                $('#total_profit').html(0);
                $('#work_number').html(0);
                $('#total_rate_profit').html(0);
                $('#work_days').html(0);
                $('#stop_days').html(0);
                $('#start_rate').html(0);

                if (data.status == 1) {
                    $('.hidden-contant').hide();
                    var income_data = data.row.income_data;
                    var cost_data = data.row.cost_data;
                    var jieyu_data = data.row.jieyu_data;
                    var total_income = total_cost = total_profit = work_number = work_days = stop_days = 0;
                    $.each(income_data['series_data']['work_number'],function(i,v){
                        work_number += v;
                    });
                    $.each(income_data['series_data']['work_days'],function(i,v){
                        work_days += v;
                    });
                    $('#total_income').html(income_data.total_income);
                    $('#total_cost').html(income_data.total_cost);
                    $('#total_profit').html(income_data.total_profit);
                    $('#work_number').html(work_number);
                    $('#total_rate_profit').html(income_data.total_rate_profit);

                    $('#work_days').html(work_days);
                    stop_days = income_data['series_data']['total_days'] - work_days;
                    $('#stop_days').html(stop_days);
                    $('#start_rate').html((work_days/income_data['series_data']['total_days']*100).toFixed(2));

                    //累计收益情况
                    income.setOption({
                        grid:{
                            left:'5%',
                            right:'2%',
                            top:'4%',
                            bottom:'12%',
                            axisLine:{
                                lineStyle:{
                                    color:'green'
                                }
                            },
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        xAxis: {
                            type: 'category',
                            // axisLabel: {
                            //     interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            //     rotate:20//标签倾斜的角度
                            // },
                            data: income_data['xAxis_data']
                        },
                        yAxis: {
                            type: 'value',
                        },
                        series: [{
                            data: income_data['series_data']['income'],
                            type: 'line',
                            smooth: true,
                            areaStyle: {
                                color: "#E4F3DA" ,
                            },
                            lineStyle: {
                                color: "green",
                            }
                        }]
                    });

                    //支出分布
                    cost.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: '{a} <br/>{b} : {c} ({d}%)'
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left',
                            data: cost_data.legend
                        },
                        series: [
                            {
                                name: '访问来源',
                                type: 'pie',
                                radius: '55%',
                                center: ['65%', '55%'],
                                data: cost_data.series_data,
                                emphasis: {
                                    itemStyle: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                }
                            }
                        ]
                    });

                    //收支结余
                    income_cost.setOption({
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        legend: {
                            data: ['收入', '支出', '利润']
                        },
                        grid: {
                            left: '1%',
                            right: '1%',
                            bottom: '3%',
                            containLabel: true
                        },
                        yAxis: [
                            {
                                type: 'value'
                            }
                        ],
                        xAxis: [
                            {
                                type: 'category',
                                axisTick: {
                                    show: false
                                },
                                data: income_data['xAxis_data']
                            }
                        ],
                        series: [
                            {
                                name: '收入',
                                barWidth: '30%',
                                type: 'bar',
                                stack: '总量',
                                label: {
                                    show: true
                                },
                                barGap:'10%',
                                itemStyle: {
                                    color: '#01AAED'
                                },
                                data: jieyu_data.jieyu_income
                            },
                            {
                                name: '利润',
                                type: 'bar',
                                barWidth: '30%',
                                label: {
                                    show: true,
                                    position: 'inside'
                                },
                                itemStyle: {
                                    color: '#3A8C03'
                                },
                                data: jieyu_data.jieyu_profit
                            },
                            {
                                name: '支出',
                                type: 'bar',
                                barWidth: '30%',
                                stack: '总量',
                                label: {
                                    show: true,
                                    position: 'left'
                                },
                                itemStyle: {
                                    color: '#D74139'
                                },
                                data: jieyu_data.jieyu_cost
                            }
                        ]
                    });
                }else{
                    $('.hidden-contant').show();
                }

            });
        }

        table.render({
            elem: '#benefitLists'
            , limits: [12, 24, 48, 96]
            , loading: true
            , limit: 12
            , title: '明细列表'
            , url: getBenefitLists //数据接口
            , where: {
                sort: 'entryDate'
                , order: 'asc'
                , type: 'getDetail'
                , assnum: params.assnum
                , year: params.year
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'entryDate' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Benfit-detail-getListToolbar',
            defaultToolbar: ['filter', 'exports']
            , cols: [[ //表头
                {
                    field: 'entryDate',
                    title: '日期',
                    width: 85,
                    align: 'center'
                },
                {
                    field: 'income',
                    title: '收入',
                    width: 100,
                    align: 'center'
                },
                {
                    field: 'depreciation_cost',
                    title: '折旧费',
                    width: 100,
                    align: 'center'
                },
                {
                    field: 'material_cost',
                    title: '材料费',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'maintenance_cost',
                    title: '维保费',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'management_cost',
                    title: '管理费',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'comprehensive_cost',
                    title: '综合费',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'interest_cost',
                    title: '利息支出',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'all_cost',
                    title: '支出合计',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'profit',
                    title: '结余',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'operator',
                    title: '操作人员数量',
                    width: 120,
                    align: 'center'
                },
                {
                    field: 'work_number',
                    title: '诊疗次数',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'work_day',
                    title: '工作天数',
                    width: 90,
                    align: 'center'
                },
                {
                    field: 'use_rate',
                    title: '开机率',
                    width: 85,
                    align: 'center'
                },
                {
                    field: 'profit_rate',
                    title: '结余率',
                    width: 85,
                    align: 'center'
                }
            ]]
        });
    });
    exports('controller/benefit/benefit/assetsBenefitData', {});
});




