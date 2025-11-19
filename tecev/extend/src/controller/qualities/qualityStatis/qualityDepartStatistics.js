layui.define(function(exports){
    layui.use(['carousel', 'formSelects', 'echarts', 'form', 'table', 'laydate', 'suggest', 'element', 'tipsType', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , formSelects = layui.formSelects
            , suggest = layui.suggest
            , table = layui.table
            , tablePlug = layui.tablePlug;
        suggest.search();
        laydate.render({
            elem: '#searchYear' //指定元素
            , type: 'year'
        });
        laydate.render({
            elem: '#searchTypeYear' //指定元素
            , type: 'year'
        });
        formSelects.render('searchTypeDepartid', selectParams(1));
        formSelects.btns('searchTypeDepartid', selectParams(2));


        formSelects.render('departid', selectParams(1));
        formSelects.btns('departid', selectParams(2));

        //管理科室 多选框初始配置


        form.render();
        var tdiv = $('.div-chart-show-pic');
        var ids = [];

        $.each(tdiv, function (index, item) {
            var tmpid = $(this).find('.div-chart-show-pic-chart').attr('id');
            if (tmpid) {
                ids.push(tmpid);
            }
        });

        $.each(ids, function (index, item) {
            var idname = echarts.init(document.getElementById(item));
            idname.clear();
            idname.showLoading();
        });

        var thisbody = $('#LAY-Qualities-QualityStatis-qualityDepartStatistics');

        var year = thisbody.find('input[name="year"]').val();


        var tablist = 0;
        var tablist2 = 0;


        var faiTypeTermMainParam = {};
        faiTypeTermMainParam.templates = thisbody.find('select[name="templates"]').val();
        faiTypeTermMainParam.year = thisbody.find('#searchTypeYear').val();
        faiTypeTermMainParam.type = 'faiTypeTerm';


        element.on('tab(qualityDepartTab)', function (data) {
            if (tablist === 0 && data.index === 1) {
                tablist = 1;
                //第一次切换选项卡至"模板明细不符合项统计" 获取数据
                showFaiTypeTermChart(faiTypeTermMainParam);
            }
        });

        //切换
        element.on('tab(qualityTab)', function (data) {
            if (tablist2 === 0 && data.index === 1) {
                tablist2 = 1;
                thisbody.find('#planResultLists').next().find('.layui-table').rowspan(1);
            }
        });

        //模板概况
        table.render({
            elem: '#qualitySurveyLists'
            , loading: true
            , title: '模板使用概况'
            , url: qualityDepartStatisticsUrl //数据接口
            , where: {
                type: 'qualitySurveyLists',
                year: year

            } //如果无需传递额外参数，可不加该参数
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {} //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , cols: [[ //表头
                {
                    field: 'qtemid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    unresize: true,
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'department',
                    fixed: 'left',
                    title: '科室名称',
                    width: 170,
                    align: 'center'
                },
                {
                    field: 'name',
                    align: 'center',
                    width: 180,
                    title: '模板'
                },

                {
                    field: 'useNums',
                    align: 'center',
                    width: 120,
                    title: '模板使用次数'
                },
                {
                    field: 'useRatio',
                    align: 'center',
                    width: 150,
                    title: '模板使用占比'
                },
                {
                    field: 'successNums',
                    align: 'center',
                    width: 100,
                    title: '合格次数'
                },
                {
                    field: 'failNums',
                    align: 'center',
                    width: 100,
                    title: '不合格次数'
                },
                {
                    field: 'successRatio',
                    title: '合格率',
                    align: 'center'
                }
            ]]
            , done: function (res, curr, count) {
                thisbody.find('#qualitySurveyLists').next().find('.layui-table').rowspan(1);
                thisbody.find('#qualitySurveyLists').next().find('.layui-table-total').find('td[data-field="successRatio"]').find('div').html(res.total_successRatio);
                //todo 多个图表
                //质控模板使用概况
                showQualitySurveyMain(res.tempSurveyData);
                showQualityQualifiedMain(res.tempQualifiedData);
            }
        });
        //质控计划结果概况
        table.render({
            elem: '#planResultLists'
            , loading: true
            , title: '质控计划结果概况'
            , url: qualityDepartStatisticsUrl //数据接口
            , where: {
                type: 'getPlanResult',
                year: year

            } //如果无需传递额外参数，可不加该参数
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {} //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , cols: [[ //表头
                {
                    field: 'qsid',
                    title: '序号',
                    width: 60,
                    fixed: 'left',
                    align: 'center',
                    unresize:false,
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },

                {
                    field: 'plan_name',
                    fixed: 'left',
                    unresize:false,
                    title: '质控计划名称',
                    width: 200,
                    align: 'center'
                },
                {
                    field: 'department',
                    fixed: 'left',
                    unresize:false,
                    title: '科室名称',
                    width: 170,
                    align: 'center'
                },

                {
                    field: 'assets',
                    align: 'center',
                    width: 190,
                    title: '设备名称'
                },
                {
                    field: 'assnum',
                    align: 'center',
                    width: 150,
                    title: '设备编号'
                },
                {
                    field: 'successNums',
                    align: 'center',
                    width: 100,
                    totalRow: true,
                    title: '合格次数'
                },
                {
                    field: 'failNums',
                    align: 'center',
                    width: 100,
                    totalRow: true,
                    title: '不合格次数'
                },
                {
                    width: 120,
                    field: 'successRatio',
                    title: '合格率',
                    align: 'center'
                }
                ,
                {
                    width: 120,
                    field: 'accord',
                    title: '符合项',
                    align: 'center'
                }
                ,
                {
                    minWidth: 450,
                    field: 'n_accord',
                    title: '不符合项',
                    align: 'left'
                }
            ]]
            , done: function (res, curr, count) {
                thisbody.find('#planResultLists').next().find('.layui-table').rowspan(1);
                // thisbody.find('#planResultLists').next().find('.layui-table').rowspan(2);
                showPlanResultMain(res.charData);
            }
        });
        //显示使用概况图表
        function showQualitySurveyMain(result) {
            var charMain = echarts.init(document.getElementById('qualitySurveyMain'));
            charMain.clear();
            charMain.hideLoading();
            if (!$.isEmptyObject(result)) {
                thisbody.find('#qualitySurveyMain').prev('.hidden-contant').hide();
                charMain.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data: result.legend
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    yAxis: {
                        type: 'value',
                        name: '使用次数'
                    },
                    xAxis: {
                        type: 'category',
                        data: result.xAxis,
                        axisLabel: {
                            interval: 0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate: 50//标签倾斜的角度
                        }
                    },
                    series: result.series

                })
            } else {
                thisbody.find('#qualitySurveyMain').prev('.hidden-contant').show();
            }
        }

        //显示质控合格结果合格概况图表
        function showQualityQualifiedMain(result) {
            var charMain = echarts.init(document.getElementById('qualityQualifiedMain'));
            charMain.clear();
            charMain.hideLoading();
            if (!$.isEmptyObject(result)) {
                thisbody.find('#qualityQualifiedMain').prev('.hidden-contant').hide();
                charMain.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data: result.legend
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    yAxis: {
                        type: 'value',
                        name: '次数'
                    },
                    xAxis: {
                        type: 'category',
                        data: result.xAxis,
                        axisLabel: {
                            interval: 0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate: 50//标签倾斜的角度
                        }
                    },
                    series: result.series

                })
            } else {
                thisbody.find('#qualityQualifiedMain').prev('.hidden-contant').show();
            }
        }

        //显示质控不合格项概况图表
        function showPlanResultMain(result) {
            var charMain = echarts.init(document.getElementById('qualityPlanResultMain'));
            charMain.clear();
            charMain.hideLoading();
            if (!$.isEmptyObject(result)) {
                thisbody.find('#qualityPlanResultMain').prev('.hidden-contant').hide();
                charMain.setOption({
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                            type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data: result.legend
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    yAxis: {
                        type: 'value',
                        name: '次数'
                    },
                    xAxis: {
                        type: 'category',
                        data: result.xAxis,
                        axisLabel: {
                            interval: 0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                            rotate: 50//标签倾斜的角度
                        }
                    },
                    series: result.series

                })
            } else {
                thisbody.find('#qualityPlanResultMain').prev('.hidden-contant').show();
            }
        }

        //监听搜索-质控概况
        form.on('submit(qualityDepartStatisticsSearch)', function (data) {
            var gloabOptions = data.field;
            gloabOptions.type = 'qualitySurveyLists';
            gloabOptions.departid = formSelects.value('departid', 'valStr');
            if (!gloabOptions.departid) {
                layer.msg('请选择需要统计的科室！', {icon: 2, time: 1000});
                return false;
            }
            var charMain = echarts.init(document.getElementById('qualitySurveyMain'));
            charMain.clear();
            charMain.showLoading();

            charMain = echarts.init(document.getElementById('qualityQualifiedMain'));
            charMain.clear();
            charMain.showLoading();
            table.reload('qualitySurveyLists', {
                url: qualityDepartStatisticsUrl
                , where: gloabOptions
            });

            charMain = echarts.init(document.getElementById('qualityPlanResultMain'));
            charMain.clear();
            charMain.showLoading();
            gloabOptions.type = 'getPlanResult';
            table.reload('planResultLists', {
                url: qualityDepartStatisticsUrl
                , where: gloabOptions
            });
            return false;
        });

        //监听搜索-质控明细项统计
        form.on('submit(faiTypeTermChartSearch)', function (data) {
            faiTypeTermMainParam.departid = formSelects.value('searchTypeDepartid', 'valStr');
            if (!faiTypeTermMainParam.departid) {
                layer.msg('请选择需要统计的科室！', {icon: 2, time: 1000});
                return false;
            }
            faiTypeTermMainParam.templates = data.field.templates;
            faiTypeTermMainParam.year = data.field.year;
            faiTypeTermMainParam.base64Data = '';
            showFaiTypeTermChart(faiTypeTermMainParam);
        });

        //导出质控概况
        form.on('submit(exportQualityDepart)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params.year = thisbody.find('#searchYear').val();
            params.departid = formSelects.value('departid', 'valStr');
            if (!params.departid) {
                layer.msg('请选择需要统计的科室！', {icon: 2, time: 1000});
                return false;
            }
            params.type = 'exportQualityDepart';
            var surveyMain = echarts.init(document.getElementById('qualitySurveyMain'));
            var fiedMain = echarts.init(document.getElementById('qualityQualifiedMain'));
            var planMain = echarts.init(document.getElementById('qualityPlanResultMain'));
            params.surveyMain = surveyMain.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            params.fiedMain = fiedMain.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            params.planMain = planMain.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            postDownLoadFile({
                url: url,
                data: params,
                method: 'POST'
            });
            return false;
        });

        //重置质控概况
        form.on('submit(resetQualityDepart)', function (data) {
            formSelects.value('departid', thisdepartids.split(","));
            thisbody.find('#searchYear').val(thisdate);
            form.render();
            return false;
        });

        //重置质控明细项统计
        form.on('submit(resetFaiTypeTerm)', function (data) {
            formSelects.value('searchTypeDepartid', thisdepartids.split(","));
            thisbody.find('#searchTypeYear').val(thisdate);
            thisbody.find('select[name="templates"]').val(thistemplates);
            form.render();
            return false;
        });

        //导出质控明细项统计
        form.on('submit(exportFaiTypeTerm)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params = faiTypeTermMainParam;
            params.type = 'faiTypeTerm';
            params.departid = formSelects.value('searchTypeDepartid', 'valStr');
            if (!params.departid) {
                layer.msg('请选择需要统计的科室！', {icon: 2, time: 1000});
                return false;
            }
            var myChart = echarts.init(document.getElementById('faiTypeTermMain'));
            params.base64Data = myChart.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            postDownLoadFile({
                url: url,
                data: params,
                method: 'POST'
            });
            return false;

        });

        //质控明细不符合项统计 更新
        function showFaiTypeTermChart(params) {
            var myChart = echarts.init(document.getElementById('faiTypeTermMain'));
            myChart.clear();
            //显示一个简单的加载动画
            myChart.showLoading();
            //异步加载数据
            $.post(qualityDepartStatisticsUrl, params).done(function (data) {
                //隐藏加载动画
                myChart.hideLoading();
                if (data.lists) {
                    showTable(data.lists);
                } else {
                    showTable([]);
                }
                if (data.lists && data.notData !== true) {
                    // 填入数据
                    myChart.setOption({
                        title: {
                            text: '质控明细项检测不符合项统计',
                            subtext: '',
                            x: 'center'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c}"
                        },
                        legend: {
                            show: true,//是否显示
                            type: 'scroll',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                            orient: 'horizontal',//图例列表的布局朝向vertical/horizontal
                            y: 'top',
                            right: '100',
                            left: '100',
                            top: '25',
                            bottom: 1,
                            data: data.legend,
                            selected: data.selected
                        },
                        grid: {
                            top: 80,//grid 组件离容器上侧的距离
                            left: '5%',//grid 组件离容器上侧的距离
                            bottom: 100,
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow',
                                    label: {
                                        show: true,
                                        formatter: function (params) {
                                            return params.value.replace('\n', '');
                                        }
                                    }
                                }
                            }
                        },
                        toolbox: {
                            show: true,
                            feature: {
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        series: [
                            {
                                name: '质控明细不符合项占比',
                                type: 'pie',
                                radius: '38%',
                                center: ['50%', '40%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        //formatter: '{a|{a}}{abg|}\n{hr|}\n  {b|{b}:} {c}  {per|{d}%}  ',
                                        formatter: function (params) {
                                            return '{a|不符合占比}{abg|}\n{hr|}\n  {b|' + params.data.name + ': ' + params.data.value + ' (' + params.data.precent + ')} ';
                                        },
                                        backgroundColor: '#eee',
                                        borderColor: '#aaa',
                                        borderWidth: 1,
                                        borderRadius: 4,

                                        rich: {
                                            a: {
                                                color: '#999',
                                                lineHeight: 22,
                                                align: 'center'
                                            },
                                            hr: {
                                                borderColor: '#aaa',
                                                width: '100%',
                                                borderWidth: 0.5,
                                                height: 0
                                            },
                                            b: {
                                                fontSize: 14,
                                                lineHeight: 30
                                            },
                                            per: {
                                                color: '#eee',
                                                backgroundColor: '#334455',
                                                padding: [4, 4],
                                                borderRadius: 2
                                            }
                                        }
                                    }
                                },
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                },
                                data: data['series']['data']
                            }
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    });
                } else {
                    myChart.setOption({
                        title: {
                            text: '质控明细项检测不符合项分析',
                            subtext: '',
                            x: 'center'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c}"
                        },
                        color: ['#dddddd'],
                        legend: {
                            show: true,//是否显示
                            type: 'scroll',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                            orient: 'horizontal',//图例列表的布局朝向vertical/horizontal
                            y: 'top',
                            right: '100',
                            left: '100',
                            top: '25',
                            bottom: 1,
                            data: ['不符合项']
                        },
                        grid: {
                            top: 80,//grid 组件离容器上侧的距离
                            left: '5%',//grid 组件离容器上侧的距离
                            bottom: 100,
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'shadow',
                                    label: {
                                        show: true,
                                        formatter: function (params) {
                                            return params.value.replace('\n', '');
                                        }
                                    }
                                }
                            }
                        },
                        toolbox: {
                            show: true,
                            feature: {
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        series: [
                            {
                                name: '质控明细不符合项占比',
                                type: 'pie',
                                radius: '38%',
                                center: ['50%', '40%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        formatter: function (params) {
                                            return '{a|不符合占比}{abg|}\n{hr|}\n  {b|' + params.data.name + ': ' + params.data.value + ' (' + params.data.precent + ')} ';
                                        },
                                        backgroundColor: '#eee',
                                        borderColor: '#ddd',
                                        borderWidth: 1,
                                        borderRadius: 4,

                                        rich: {
                                            a: {
                                                color: '#9C9C9C',
                                                lineHeight: 22,
                                                align: 'center'
                                            },
                                            hr: {
                                                borderColor: '#9C9C9C',
                                                width: '100%',
                                                borderWidth: 0.5,
                                                height: 0
                                            },
                                            b: {
                                                color: '#9C9C9C',
                                                fontSize: 14,
                                                lineHeight: 30
                                            },
                                            per: {
                                                color: '#9C9C9C',
                                                backgroundColor: '#334455',
                                                padding: [4, 4],
                                                borderRadius: 2
                                            }
                                        }
                                    }
                                },
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                                    }
                                },
                                data: [{value: 0, name: '不符合项', precent: '0%'}]
                            }
                        ],
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    });
                }
            });
        }

        var postDownLoadFile = function (options) {
            var config = $.extend(true, {method: 'POST'}, options);
            var $iframe = $('<iframe id="down-file-iframe" />');
            var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
            $form.attr('action', config.url);
            for (var key in config.data) {
                $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
            }
            $(document.body).append($iframe);
            $iframe.append($form);
            $form[0].submit();
            $iframe.remove();
        };

        //改变表格数据
        function showTable(res) {
            var html = '';
            var tsNum = 0;
            var tfNum = 0;
            var tRatio = 0;
            thisbody.find('.faiTypeTermConData').html('');
            $.each(res, function (e, val) {
                tsNum += parseInt(val.successNum);
                tfNum += parseInt(val.failNum);
                html += '<tr>' +
                    '<td>' + (e + 1) + '</td>\n' +
                    '<td>' + val.title + '</td>\n' +
                    '<td>' + val.successNum + '</td>\n' +
                    '<td>' + val.failNum + '</td>\n' +
                    '<td>' + val.Ratio + '%</td>\n' +
                    '</tr>';
            });
            // Math.round(num / total * 10000)
            html += '<tr>';
            html += '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n';
            html += '<td class="total-font-color">' + tsNum + '</td>\n';
            html += '<td class="total-font-color">' + tfNum + '</td>\n';
            if(tsNum>0){
                html += '<td class="total-font-color">' + Math.round((tsNum / (tsNum + tfNum)) * 10000) / 100.00 + "%" + '</td>\n';
            }else{
                html += '<td class="total-font-color">0.00%</td>\n';
            }
            html += '</tr>';
            thisbody.find('.faiTypeTermConData').append(html);
        }

        //修改显示的查询条件
        function showConditions(res) {
            thisbody.find('.faiTypeTermReportConditions').html(res);
        }
    });
    exports('qualities/qualityStatis/qualityDepartStatistics', {});
});
