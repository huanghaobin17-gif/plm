layui.define(function(exports){
    layui.use(['carousel', 'echarts','form','table','laydate','suggest'], function() {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , suggest = layui.suggest
            , table = layui.table, tablePlug = layui.tablePlug;
        //全院监护仪科室分布Top5
        //台次占比
        monitor_department_nums();
        function monitor_department_nums() {
            var idname = echarts.init(document.getElementById('monitor_department_nums'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['普通内科','麻醉手术科','急诊科','儿科','康复科']
                },
                series : [
                    {
                        name: '台次',
                        type: 'pie',
                        radius : '55%',
                        center: ['55%', '60%'],
                        data:[
                            {value:24, name:'普通内科'},
                            {value:16, name:'麻醉手术科'},
                            {value:15, name:'急诊科'},
                            {value:12, name:'儿科'},
                            {value:6, name:'康复科'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        }

        //总金额占比
        monitor_department_prices();
        function monitor_department_prices() {
            var idname = echarts.init(document.getElementById('monitor_department_prices'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['普通内科','麻醉手术科','急诊科','儿科','康复科']
                },
                series : [
                    {
                        name: '总金额',
                        type: 'pie',
                        radius : '60%',
                        center: ['55%', '55%'],
                        data:[
                            {value:61.97, name:'普通内科'},
                            {value:41.31, name:'麻醉手术科'},
                            {value:38.73, name:'急诊科'},
                            {value:31.98, name:'儿科'},
                            {value:15.49, name:'康复科'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        }

        //2014~2018 全院监护仪增减分析仪
        monitor_add_scrap_year();
        function monitor_add_scrap_year() {
            var idname = echarts.init(document.getElementById('monitor_add_scrap_year'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
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
                        dataView: {show: false, readOnly: false},
                        magicType: {show: true, type: ['line', 'bar']},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'全院监护仪增减分析仪'}
                    }
                },
                legend: {
                    data:['报废','现有','新增','总数量','总金额']
                },
                grid: {
                    left: '2%',
                    right: '2%',
                    bottom: '2%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['2014年','2015年','2016年','2017年','2018年']
                },
                yAxis: [
                    {
                        type: 'value',
                        name: '台次',
                        min: -10,
                        max: 30,
                        interval: 5,
                        axisLabel: {
                            formatter: '{value} '
                        }
                    },
                    {
                        type: 'value',
                        name: '总金额',
                        min: 0,
                        max: 50,
                        interval: 5,
                        axisLabel: {
                            formatter: '{value} 万元'
                        }
                    }
                ],
                series: [
                    {
                        name:'报废',
                        type:'bar',
                        stack:'总量',
                        label: {
                            normal: {
                                show: true,
                                position: 'inside'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#61a0a8'
                            }
                        },
                        data:[-1,0,0,-3,0]
                    },
                    {
                        name: '新增',
                        type: 'bar',
                        stack:'总量',
                        label: {
                            normal: {
                                show: true,
                                position: 'inside'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#2f4554'
                            }
                        },
                        data:[3,6,8,11,5],

                    },
                    {
                        name:'现有',
                        type: 'bar',
                        stack: '总量',
                        label: {
                            normal: {
                                show: true,
                                position: 'inside'
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: '#c23531'
                            }
                        },
                        data:[4,2,3,5,13]
                    },
                    {
                        name:'总数量',
                        type: 'bar',
                        stack: '总量',
                        label: {
                            normal: {
                                show: true,
                                position: 'insideBottom',
                                textStyle: {
                                    color: '#333',
                                    fontWeight:'bold'
                                },
                            }
                        },
                        itemStyle: {
                            normal: {
                                color: 'none',
                                fontStyle:''
                            }
                        },
                        data:[6,8,11,13,18]
                    },
                    {
                        name:'总金额',
                        type:'line',
                        yAxisIndex: 1,
                        itemStyle: {
                            normal: {
                                color: '#91c7ae'
                            }
                        },
                        data:[15.49, 20.66,28.40,33.57,46.48]
                    }
                ]
            });
        }

        //监护仪品牌占有率
        monitor_brand_market();
        function monitor_brand_market() {
            var idname = echarts.init(document.getElementById('monitor_brand_market'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                },
                series : [
                    {
                        name: '品牌占有率',
                        type: 'pie',
                        radius : '55%',
                        center: ['50%', '60%'],
                        data:[
                            {value:65, name:'迈瑞'},
                            {value:112, name:'理邦'},
                            {value:36, name:'飞利浦'},
                            {value:26, name:'康达八方'},
                            {value:6, name:'三瑞'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        }

        //监护仪品牌金额占比
        monitor_brand_price();
        function monitor_brand_price() {
            var idname = echarts.init(document.getElementById('monitor_brand_price'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip : {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                },
                series : [
                    {
                        name: '品牌占有率',
                        type: 'pie',
                        radius : '55%',
                        center: ['50%', '60%'],
                        data:[
                            {value:117.12, name:'迈瑞'},
                            {value:179.57, name:'理邦'},
                            {value:257.38, name:'飞利浦'},
                            {value:66.64, name:'康达八方'},
                            {value:11.88, name:'三瑞'}
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }
                ]
            });
        }

        //监护仪品牌维修报表
        monitor_protection_2();
        function monitor_protection_2() {
            var idname = echarts.init(document.getElementById('monitor_protection_2'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    right: '26%',
                    left:'8%'
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'品牌监护仪保外第2年维修数据分析'}
                    }
                },
                legend: {
                    data:['台次','故障率','维修费率']
                },
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLabel: {
                            interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate:30//标签倾斜的角度
                        },
                        data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '故障率',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLine: {
                            lineStyle: {
                                color: '#d14a61'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '维修费率',
                        min: 0,
                        max: 10,
                        position: 'right',
                        offset: 80,
                        axisLine: {
                            lineStyle: {
                                color: '#5793f3'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '台次',
                        min: 0,
                        max: 100,
                        position: 'left',
                        axisLine: {
                            lineStyle: {
                                color: '#675bba'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} '
                        }
                    }
                ],
                series: [
                    {
                        name:'故障率',
                        type:'line',
                        data:[20, 25,25, 44.44, 25]
                    },
                    {
                        name:'维修费率',
                        type:'line',
                        yAxisIndex: 1,
                        data:[2.6, 3.21, 2.3, 2.94, 1.96]
                    },
                    {
                        name:'台次',
                        type:'bar',
                        yAxisIndex: 2,
                        data:[20, 32, 12,9, 4]
                    }
                ]
            });
        }

        monitor_protection_3();
        function monitor_protection_3() {
            var idname = echarts.init(document.getElementById('monitor_protection_3'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    right: '26%',
                    left:'8%'
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'品牌监护仪保外第3年维修数据分析'}
                    }
                },
                legend: {
                    data:['台次','故障率','维修费率']
                },
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLabel: {
                            interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate:30//标签倾斜的角度
                        },
                        data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '故障率',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLine: {
                            lineStyle: {
                                color: '#d14a61'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '维修费率',
                        min: 0,
                        max: 10,
                        position: 'right',
                        offset: 80,
                        axisLine: {
                            lineStyle: {
                                color: '#5793f3'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '台次',
                        min: 0,
                        max: 100,
                        position: 'left',
                        axisLine: {
                            lineStyle: {
                                color: '#675bba'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} '
                        }
                    }
                ],
                series: [
                    {
                        name:'故障率',
                        type:'line',
                        data:[45, 47.73,35, 66.67, 80]
                    },
                    {
                        name:'维修费率',
                        type:'line',
                        yAxisIndex: 1,
                        data:[2.8, 2.95, 2.68, 3.42, 3.67]
                    },
                    {
                        name:'台次',
                        type:'bar',
                        yAxisIndex: 2,
                        data:[40, 44, 20,15, 5]
                    }
                ]
            });
        }

        monitor_protection_4();
        function monitor_protection_4() {
            var idname = echarts.init(document.getElementById('monitor_protection_4'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    right: '26%',
                    left:'8%'
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'品牌监护仪保外第4年维修数据分析'}
                    }
                },
                legend: {
                    data:['台次','故障率','维修费率']
                },
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLabel: {
                            interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate:30//标签倾斜的角度
                        },
                        data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '故障率',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLine: {
                            lineStyle: {
                                color: '#d14a61'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '维修费率',
                        min: 0,
                        max: 10,
                        position: 'right',
                        offset: 80,
                        axisLine: {
                            lineStyle: {
                                color: '#5793f3'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '台次',
                        min: 0,
                        max: 100,
                        position: 'left',
                        axisLine: {
                            lineStyle: {
                                color: '#675bba'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} '
                        }
                    }
                ],
                series: [
                    {
                        name:'故障率',
                        type:'line',
                        data:[45.61, 46.46,38.65, 68.54, 78.88]
                    },
                    {
                        name:'维修费率',
                        type:'line',
                        yAxisIndex: 1,
                        data:[3.8, 3.56, 3.67, 3.84, 3.5]
                    },
                    {
                        name:'台次',
                        type:'bar',
                        yAxisIndex: 2,
                        data:[57, 92, 28,20, 6]
                    }
                ]
            });
        }

        monitor_protection_5();
        function monitor_protection_5() {
            var idname = echarts.init(document.getElementById('monitor_protection_5'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    right: '26%',
                    left:'8%'
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'品牌监护仪保外第5年维修数据分析'}
                    }
                },
                legend: {
                    data:['台次','故障率','维修费率']
                },
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLabel: {
                            interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate:30//标签倾斜的角度
                        },
                        data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '故障率',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLine: {
                            lineStyle: {
                                color: '#d14a61'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '维修费率',
                        min: 0,
                        max: 10,
                        position: 'right',
                        offset: 80,
                        axisLine: {
                            lineStyle: {
                                color: '#5793f3'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '台次',
                        min: 0,
                        max: 100,
                        position: 'left',
                        axisLine: {
                            lineStyle: {
                                color: '#675bba'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} '
                        }
                    }
                ],
                series: [
                    {
                        name:'故障率',
                        type:'line',
                        data:[63.33, 52.76,48.04, 69.13, 0]
                    },
                    {
                        name:'维修费率',
                        type:'line',
                        yAxisIndex: 1,
                        data:[3.82, 3.74, 3.87, 4.31, 0]
                    },
                    {
                        name:'台次',
                        type:'bar',
                        yAxisIndex: 2,
                        data:[60, 100, 34,26, 0]
                    }
                ]
            });
        }

        monitor_protection_year_repair();
        function monitor_protection_year_repair() {
            var idname = echarts.init(document.getElementById('monitor_protection_year_repair'));
            idname.clear();
            idname.hideLoading();
            idname.setOption({
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    right: '10%',
                    left:'5%'
                },
                toolbox: {
                    feature: {
                        dataView: {show: true, readOnly: false},
                        restore: {show: true},
                        saveAsImage: {show: true,name:'品牌监护仪保外年均维修数据分析'}
                    }
                },
                legend: {
                    data:['维修费率','故障率']
                },
                xAxis: [
                    {
                        type: 'category',
                        axisTick: {
                            alignWithLabel: true
                        },
                        axisLabel: {
                            interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate:30//标签倾斜的角度
                        },
                        data: ['迈瑞','理邦','飞利浦','康达八方','三瑞']
                    }
                ],
                yAxis: [
                    {
                        type: 'value',
                        name: '故障率',
                        min: 0,
                        max: 100,
                        position: 'right',
                        axisLine: {
                            lineStyle: {
                                color: '#d14a61'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    },
                    {
                        type: 'value',
                        name: '维修费率',
                        min: 0,
                        max: 5,
                        position: 'left',
                        axisLine: {
                            lineStyle: {
                                color: '#675bba'
                            }
                        },
                        axisLabel: {
                            formatter: '{value} %'
                        }
                    }
                ],
                series: [
                    {
                        name:'故障率',
                        type:'line',
                        data:[43.49, 42.99,36.67, 62.2, 45.97]
                    },
                    {
                        name:'维修费率',
                        type:'line',
                        yAxisIndex: 1,
                        data:[3.26, 3.37, 3.13, 3.63,2.28]
                    }
                ]
            });
        }
    });
    exports('strategy/stramonitor/monitorsur', {});
});
