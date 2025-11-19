layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'element'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, element = layui.element,
            table = layui.table;

        var line =echarts.init(document.getElementById('lineMain'));
        form.render();
        laydate.render({
            elem: '#departmentBenefitDataYearDate',
            festival: true,
            value: new Date()
            , type: 'year'

        });

        var paramsLine={};
        paramsLine.departid=$('input[name="departid"]').val();
        paramsLine.type='getLine';
        paramsLine.yearDate=$('input[name="yearDate"]').val();
        //加载线图
        showLine(paramsLine);
        //显示一个简单的加载动画


        //监听线图搜索按钮
        form.on('submit(departmentBenefitDataLineSearch)',function () {
            var paramsLine={};
            paramsLine.departid=$('input[name="departid"]').val();
            paramsLine.type='getLine';
            paramsLine.yearDate=$('input[name="yearDate"]').val();
            showLine(paramsLine);

        });


        //显示线图
        function showLine(params) {
            line.clear();
            line.showLoading();
            $.post(admin_name+'/Benefit/departmentBenefitData', params).done(function (data) {
                //隐藏加载动画
                line.hideLoading();
                if (data.status == 1) {
                    var result = data.row;

                    line.setOption({
                        title: {
                            text: ''
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['收入', '支出', '利润']
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
                            data: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
                        },
                        yAxis: {
                            name:'单位(元)',
                            type: 'value'
                        },
                        series: [
                            {
                                name: '收入',
                                type: 'line',
                                smooth: true,
                                data: result.line.income
                            },
                            {
                                name: '支出',
                                type: 'line',
                                smooth: true,
                                data: result.line.cost
                            },
                            {
                                name: '利润',
                                type: 'line',
                                smooth: true,
                                data: result.line.profit
                            }
                        ]

                    });
                    changeTable(result.data);
                }
            });
        }


//改变表格数据
        function changeTable(result) {
            var table_list=$('#table_list');
            var notData=$('#notData');
            if(result.length>0){
                var html='';
                $.each(result,function (key,value) {
                    html+='<tr>';
                    html+='<td>'+value.entryDate+'</td>';
                    html+='<td>'+value.income+'</td>';
                    html+='<td>'+value.depreciation_cost+'</td>';
                    html+='<td>'+value.material_cost+'</td>';
                    html+='<td>'+value.maintenance_cost+'</td>';
                    html+='<td>'+value.management_cost+'</td>';
                    html+='<td>'+value.operator+'</td>';
                    html+='<td>'+value.comprehensive_cost+'</td>';
                    html+='<td>'+value.interest_cost+'</td>';
                    html+='<td>'+value.all_cost+'</td>';
                    html+='<td>'+value.work_number+'</td>';
                    html+='<td>'+value.repair_time+'</td>';
                    html+='<td>'+value.workDay_rate+'</td>';
                    // html+='<td>'+value.positive_rate+'</td>';
                    html+='<td>'+value.profit+'</td>';
                    html+='<td>'+value.profitr_rate+'</td>';
                    html+='</tr>';
                });
                table_list.html(html);
                table_list.show();
                notData.hide();
            }else{
                table_list.hide();
                notData.html('<td colspan="16">暂无数据</td>');
                notData.show();
            }
        }


    });
    exports('controller/benefit/benefit/departmentBenefitData', {});
});





