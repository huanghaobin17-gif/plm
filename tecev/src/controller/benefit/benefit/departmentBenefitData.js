layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'element'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, element = layui.element,
            table = layui.table;

        var benefit_1 =echarts.init(document.getElementById('benefit_1'));
        var benefit_2 =echarts.init(document.getElementById('benefit_2'));
        var benefit_4 =echarts.init(document.getElementById('benefit_4'));
        var benefit_5 =echarts.init(document.getElementById('benefit_5'));
        var benefit_6 =echarts.init(document.getElementById('benefit_6'));
        var benefit_4_1 =echarts.init(document.getElementById('benefit_4_1'));
        var benefit_4_2 =echarts.init(document.getElementById('benefit_4_2'));
        var benefit_4_3 =echarts.init(document.getElementById('benefit_4_3'));
        var benefit_4_4 =echarts.init(document.getElementById('benefit_4_4'));
        var benefit_4_5 =echarts.init(document.getElementById('benefit_4_5'));


        form.render();
        laydate.render({
            elem: '#departBenefitDataYearDate',
            festival: true
            , type: 'year'
        });


        var params = {};
        params.departid = $('input[name="departid"]').val();
        params.year = $('input[name="year"]').val();
        showChart(params);
        showHuiben(params);
        //监听搜索按钮
        form.on('submit(departBenefitDataSearch)', function (data) {
            var field = data.field;
            showChart(field);
            table.reload('departBenefitLists', {
                url: departmentBenefitData
                , where: {
                    sort: 'buy_price'
                    , order: 'asc'
                    , type: 'getBenefitAssets'
                    , departid: field.departid
                    , year: field.year
                }
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        function showChart(params) {
            benefit_1.clear();
            benefit_2.clear();
            benefit_4.clear();
            benefit_6.clear();

            benefit_4_1.clear();
            benefit_4_2.clear();
            benefit_4_3.clear();
            benefit_4_4.clear();
            benefit_4_5.clear();

            benefit_1.showLoading();
            benefit_2.showLoading();
            benefit_4.showLoading();
            benefit_6.showLoading();

            benefit_4_1.showLoading();
            benefit_4_2.showLoading();
            benefit_4_3.showLoading();
            benefit_4_4.showLoading();
            benefit_4_5.showLoading();

            benefit_1.hideLoading();
            benefit_2.hideLoading();
            benefit_4.hideLoading();
            benefit_6.hideLoading();

            benefit_4_1.hideLoading();
            benefit_4_2.hideLoading();
            benefit_4_3.hideLoading();
            benefit_4_4.hideLoading();
            benefit_4_5.hideLoading();
            $.post(admin_name+'/Benefit/departmentBenefitData', params).done(function (res) {
                if(res.code){
                    var table_list=$('#table_list');
                    var notData=$('#notData');
                    table_list.hide();
                    notData.show();
                    $("#benefit_4_1_assets_name").html('-');
                    $("#benefit_4_2_assets_name").html('-');
                    $("#benefit_4_3_assets_name").html('-');
                    $("#benefit_4_4_assets_name").html('-');
                    $("#benefit_4_5_assets_name").html('-');
                    return;
                }
                //累计收益
                if(res.benefit_1_data.income.length > 0){
                    var benefit_1_data = res.benefit_1_data;
                    benefit_1.setOption({
                        grid:{
                            left:'7%',
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
                            data: benefit_1_data.xAxis_data
                        },
                        yAxis: {
                            type: 'value',
                        },
                        series: [{
                            data: benefit_1_data.income,
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
                }

                //收支结余
                if(res.benefit_2_data.income.length > 0){
                    var benefit_2_data = res.benefit_2_data;
                    benefit_2.setOption({
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
                                data: benefit_2_data.xAxis_data
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
                                data: benefit_2_data.income
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
                                data: benefit_2_data.profit
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
                                data: benefit_2_data.all_cost
                            }
                        ]
                    });
                }

                //效益设备收益统计
                if(res.benefit_3_data.income.length > 0){
                    var benefit_3_data = res.benefit_3_data;
                    $('#benefit_3').css('height',benefit_3_data.minHeight);
                    var benefit_3 =echarts.init(document.getElementById('benefit_3'));
                    benefit_3.clear();
                    benefit_3.showLoading();
                    benefit_3.hideLoading();

                    benefit_3.setOption({
                        color: ['#37A2DA'],
                        yAxis: {
                            type: 'category',
                            axisLine: {
                                lineStyle: {
                                    color: '#000'//坐标轴及字体颜色
                                }
                            },
                            data: benefit_3_data.yAxis_data
                        },
                        xAxis: {
                            type: 'value',
                            // max: function(value) {
                            //     return value.max + 20;
                            // },
                            axisLine: {
                                lineStyle: {
                                    color: '#000'
                                }
                            }
                        },
                        grid: {
                            left: '0%',
                            right: '2%',
                            bottom: '1%',
                            top: '1%',
                            containLabel: true
                        },
                        series: [
                            {
                                data: benefit_3_data.income,
                                type: 'bar',
                                barWidth: '80%',
                                barGap: '80',
                                itemStyle: {        //上方显示数值
                                    normal: {
                                        label: {
                                            show: true, //开启显示
                                            position: 'inside', //在上方显示
                                            textStyle: { //数值样式
                                                color: '#fff',
                                                fontSize: 14
                                            },
                                        }
                                    }
                                },
                                showBackground: true,
                                backgroundStyle: {
                                    color: 'rgba(178, 176, 183,1)'
                                }
                            }]
                    });
                }

                //支出TOP5设备
                if(res.benefit_4_data){
                    var benefit_4_data = res.benefit_4_data;
                    //支出TOP5设备统计
                    benefit_4.setOption({
                        color: ['#C23531'],
                        yAxis: {
                            type: 'category',
                            axisLine: {
                                lineStyle: {
                                    color: '#000'//坐标轴及字体颜色
                                }
                            },
                            data: benefit_4_data.yAxis_data
                        },
                        xAxis: {
                            type: 'value',
                            // max: function(value) {
                            //     return value.max + 20;
                            // },
                            axisLine: {
                                lineStyle: {
                                    color: '#000'
                                }
                            }
                        },
                        grid: {
                            left: '0%',
                            right: '3%',
                            bottom: '1%',
                            top: '1%',
                            containLabel: true
                        },
                        series: [
                            {
                                data: benefit_4_data.total_cost,
                                type: 'bar',
                                barWidth: '80%',
                                barGap: '80',
                                itemStyle: {        //上方显示数值
                                    normal: {
                                        label: {
                                            show: true, //开启显示
                                            position: 'inside', //在上方显示
                                            textStyle: { //数值样式
                                                color: '#fff',
                                                fontSize: 14
                                            },
                                        }
                                    }
                                },
                                showBackground: true,
                                backgroundStyle: {
                                    color: 'rgba(178, 176, 183,1)'
                                }
                            }]
                    });

                    //支出1
                    if(res.benefit_4_data.benefit_4_1_data){
                        var benefit_4_1_data = res.benefit_4_data.benefit_4_1_data;
                        $("#benefit_4_1_assets_name").html(benefit_4_1_data.assets);
                        benefit_4_1.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: '{a} <br/>{b} : {c} ({d}%)'
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 'auto',
                                left:'0%',
                                right:'0%',
                                data: benefit_4_1_data.legend
                            },
                            series: [
                                {
                                    name: '费用支出',
                                    type: 'pie',
                                    radius: '65%',
                                    center: ['50%', '60%'],
                                    data: benefit_4_1_data.series,
                                    emphasis: {
                                        itemStyle: {
                                            shadowBlur: 2,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    },
                                    label: {
                                        show: true,
                                        position: 'inside',
                                    }
                                }
                            ]
                        });
                    }else{
                        $("#benefit_4_1_assets_name").html('-');
                    }

                    //支出2
                    if(res.benefit_4_data.benefit_4_2_data){
                        var benefit_4_2_data = res.benefit_4_data.benefit_4_2_data;
                        $("#benefit_4_2_assets_name").html(benefit_4_2_data.assets);
                        benefit_4_2.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: '{a} <br/>{b} : {c} ({d}%)'
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 'auto',
                                left:'0%',
                                right:'0%',
                                data: benefit_4_2_data.legend
                            },
                            series: [
                                {
                                    name: '费用支出',
                                    type: 'pie',
                                    radius: '65%',
                                    center: ['50%', '60%'],
                                    data: benefit_4_2_data.series,
                                    emphasis: {
                                        itemStyle: {
                                            shadowBlur: 2,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    },
                                    label: {
                                        show: true,
                                        position: 'inside',
                                    }
                                }
                            ]
                        });
                    }else{
                        $("#benefit_4_2_assets_name").html('-');
                    }

                    //支出3
                    if(res.benefit_4_data.benefit_4_3_data){
                        var benefit_4_3_data = res.benefit_4_data.benefit_4_3_data;
                        $("#benefit_4_3_assets_name").html(benefit_4_3_data.assets);
                        benefit_4_3.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: '{a} <br/>{b} : {c} ({d}%)'
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 'auto',
                                left:'0%',
                                right:'0%',
                                data: benefit_4_3_data.legend
                            },
                            series: [
                                {
                                    name: '费用支出',
                                    type: 'pie',
                                    radius: '65%',
                                    center: ['50%', '60%'],
                                    data:benefit_4_3_data.series,
                                    emphasis: {
                                        itemStyle: {
                                            shadowBlur: 2,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    },
                                    label: {
                                        show: true,
                                        position: 'inside',
                                    }
                                }
                            ]
                        });
                    }else{
                        $("#benefit_4_3_assets_name").html('-');
                    }

                    //支出4
                    if(res.benefit_4_data.benefit_4_4_data){
                        var benefit_4_4_data = res.benefit_4_data.benefit_4_4_data;
                        $("#benefit_4_4_assets_name").html(benefit_4_4_data.assets);
                        benefit_4_4.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: '{a} <br/>{b} : {c} ({d}%)'
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 'auto',
                                left:'0%',
                                right:'0%',
                                data: benefit_4_4_data.legend
                            },
                            series: [
                                {
                                    name: '费用支出',
                                    type: 'pie',
                                    radius: '65%',
                                    center: ['50%', '60%'],
                                    data: benefit_4_4_data.series,
                                    emphasis: {
                                        itemStyle: {
                                            shadowBlur: 2,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    },
                                    label: {
                                        show: true,
                                        position: 'inside',
                                    }
                                }
                            ]
                        });
                    }else{
                        $("#benefit_4_4_assets_name").html('-');
                    }

                    //支出5
                    if(res.benefit_4_data.benefit_4_5_data){
                        var benefit_4_5_data = res.benefit_4_data.benefit_4_5_data;
                        $("#benefit_4_5_assets_name").html(benefit_4_5_data.assets);
                        benefit_4_5.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: '{a} <br/>{b} : {c} ({d}%)'
                            },
                            legend: {
                                orient: 'horizontal',
                                top: 'auto',
                                left:'0%',
                                right:'0%',
                                data: benefit_4_5_data.legend
                            },
                            series: [
                                {
                                    name: '费用支出',
                                    type: 'pie',
                                    radius: '65%',
                                    center: ['50%', '60%'],
                                    data: benefit_4_5_data.series,
                                    emphasis: {
                                        itemStyle: {
                                            shadowBlur: 2,
                                            shadowOffsetX: 0,
                                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                                        }
                                    },
                                    label: {
                                        show: true,
                                        position: 'inside',
                                    }
                                }
                            ]
                        });
                    }else{
                        $("#benefit_4_5_assets_name").html('-');
                    }
                }

                if(res.benefit_6_data){
                    var benefit_6_data = res.benefit_6_data;
                    benefit_6.setOption({
                        color: ['#3398DB'],
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        grid: {
                            left: '2%',
                            right: '5%',
                            bottom: '1%',
                            top: '3%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: benefit_6_data.xAxis_data
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [{
                            data: benefit_6_data.series_data,
                            label: {
                                show: true,
                                position: 'inside'
                            },
                            showBackground: true,
                            backgroundStyle: {
                                color: 'rgba(178, 176, 183,1)'
                            },
                            type: 'bar'
                        }]
                    });
                }

            });
        }

        table.render({
            elem: '#departBenefitLists'
            ,size: 'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '明细列表'
            , url: departmentBenefitData //数据接口
            , where: {
                sort: 'buy_price'
                , order: 'asc'
                , type: 'getBenefitAssets'
                , departid: params.departid
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'buy_price' //排序字段，对应 cols 设定的各字段名
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
                    field: 'assets',
                    title: '设备名称',
                    width: '18%',
                    align: 'center'
                },
                {
                    field: 'assnum',
                    title: '设备编号',
                    width: '15%',
                    align: 'center'
                },
                {
                    field: 'model',
                    title: '设备型号',
                    width: '15%',
                    align: 'center'
                },
                {
                    field: 'buy_price',
                    title: '设备价格',
                    width: '10%',
                    align: 'center'
                },
                {
                    field: 'income',
                    title: '累计收入(万元)',
                    width: '11%',
                    align: 'center'
                },
                {
                    field: 'all_cost',
                    title: '累计支出(万元)',
                    width: '11%',
                    align: 'center'
                },
                {
                    field: 'opendate',
                    title: '开机日期',
                    width: '10%',
                    align: 'center'
                },
                {
                    field: 'expected_life',
                    title: '预计使用年限',
                    width: '10%',
                    align: 'center'
                }
            ]]
        });

        function showHuibentable(table_data){
            var table_list=$('#huiben_list');
            var notData=$('#notHuibenData');
            var html = '';
            $.each(table_data,function (key,value) {
                html += '<tr>\n' +
                    '    <td class="text-center">'+value.assets+'</td>\n' +
                    '    <td class="text-center">'+value.assnum+'</td>\n' +
                    '    <td class="text-center">'+value.model+'</td>\n' +
                    '    <td class="text-center">'+value.buy_price+'</td>\n' +
                    '    <td class="text-center">'+value.income+'</td>\n' +
                    '    <td class="text-center">'+value.total_cost+'</td>\n' +
                    '    <td class="text-center">'+value.profit+'</td>\n' +
                    '    <td class="text-center">'+value.expected_life+'</td>\n' +
                    '    <td class="text-center">'+value.huiben_years+'</td>\n' +
                    '</tr>\n';
            });
            table_list.html(html);
            table_list.show();
            notData.hide();
        }

        //已回本设备统计
        function showHuiben(params){
            params.type = 'getHuibenAssets';
            benefit_5.clear();
            benefit_5.showLoading();
            benefit_5.hideLoading();
            $.post(admin_name+'/Benefit/departmentBenefitData', params).done(function (res) {
                var benefit_5_data = res;
                if(benefit_5_data){
                    benefit_5.setOption({
                        grid:{
                            left:'5%',
                            right:'5%',
                            top:'12%',
                            bottom:'16%',
                            axisLine:{
                                lineStyle:{
                                    color:'green'
                                }
                            },
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        toolbox: {
                            feature: {
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        legend: {
                            data: ['设备价格', '净利润', '回本年限']
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: benefit_5_data.xAxis_data,
                                axisPointer: {
                                    type: 'shadow'
                                },
                                axisLabel: {
                                    interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                    rotate:12//标签倾斜的角度
                                },
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                name: '设备价格/净利润(万元)',
                                min: 0,
                                // interval: 100,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            },
                            {
                                type: 'value',
                                name: '回本时间(年)',
                                min: 0,
                                interval: 2,
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        series: [
                            {
                                name: '设备价格',
                                type: 'bar',
                                data: benefit_5_data.series.buy_price
                            },
                            {
                                name: '净利润',
                                type: 'bar',
                                data: benefit_5_data.series.profit
                            },
                            {
                                name: '回本时间',
                                type: 'line',
                                yAxisIndex: 1,
                                data: benefit_5_data.series.huiben_years
                            }
                        ]
                    });
                    showHuibentable(benefit_5_data.table_data);
                }
            });
        }
    });
    exports('controller/benefit/benefit/departmentBenefitData', {});
});





