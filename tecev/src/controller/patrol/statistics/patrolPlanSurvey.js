layui.define(function(exports){
    layui.use(['layer', 'form','laydate','element'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,laydate = layui.laydate,element = layui.element;
        element.render();
        //先更新页面部分需要提前渲染的控件
        form.render();
        //年选择器
        laydate.render({
            elem: '#year'
            ,value: current_year
            ,type: 'year'
        });
        var year_plans = echarts.init(document.getElementById('year_plans'));
        var params = {};
        params.year = $('input[name="year"]').val();
        showChart(params);
        //显示饼图
        function showChart(params) {
            year_plans.clear();
            //显示一个简单的加载动画
            year_plans.showLoading();
            // 异步加载数据
            $.post(admin_name+'/PatrolStatis/patrolPlanSurvey', params).done(function (data) {
                //隐藏加载动画
                year_plans.hideLoading();
                if (data.status == 1) {
                    $('.hidden-contant').hide();
                    var res = data.res;
                    $('#year_procress').attr('lay-percent',res.year_complete+' / '+res.year_all);
                    element.render();
                    var labelOption = {
                        show: true,
                        position: 'insideBottom',
                        rotate: 90,
                        align: 'left',
                        verticalAlign: 'middle',
                        distance: 15,
                        fontSize: 18,
                        formatter: '{c}  {name|{a}}',
                        rich: {
                            name: {
                                textBorderColor: '#fff',
                                fontSize: 16,
                            }
                        }
                    };
                    year_plans.setOption({
                        color: ['#003366', '#006699'],
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        legend: {
                            data: ['计划总数', '已完成']
                        },
                        grid: {
                            left: '2%',
                            right: '2%',
                            bottom: '3%',
                            containLabel: true
                        },
                        yAxis: [{type: 'value'}],
                        xAxis: [
                            {
                                type: 'category',
                                axisTick: {
                                    show: false
                                },
                                data: res.xAxis_data
                            }
                        ],
                        series: [
                            {
                                name: '计划总数',
                                type: 'bar',
                                barGap: 0,
                                label: labelOption,
                                data: res.all_plans_data
                            },
                            {
                                name: '已完成',
                                type: 'bar',
                                stack: '总量',
                                label: labelOption,
                                data: res.complete_plans_data
                            }
                        ]
                    });
                }else{
                    $('.hidden-contant').show();
                }

            });
        }
        //监听搜索按钮
        form.on('submit(patrolPlanSurveySearch)', function (data) {
            params = data.field;
            showChart(params);
            return false;
        });
    });
    exports('patrol/statistics/patrolPlanSurvey', {});
});

