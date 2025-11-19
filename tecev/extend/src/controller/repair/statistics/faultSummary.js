layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'tipsType', 'element', 'tablePlug'], function () {
        var tablist=0;
        var form = layui.form, tipsType = layui.tipsType, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, element = layui.element, table = layui.table, tablePlug = layui.tablePlug;
        form.render();
        var thisbody=$('#LAY-Repair-RepairStatis-faultSummary');
        element.on('tab(faultSummaryTab)', function(data){
            if(tablist===0){
                //设备故障统计表格
                table.render({
                    elem: '#faultSummary'
                    ,size:'sm'
                    //,height: '600'
                    , limits: [10, 20, 50, 100]
                    , loading: true
                    , limit: 10
                    , url: admin_name+'/RepairStatis/faultSummary.html' //数据接口
                    , where: {
                        // sort: 'repid',
                        // ,order: 'asc'
                        tab: true
                    } //如果无需传递额外参数，可不加该参数
                    , initSort: {
                        field: 'repid' //排序字段，对应 cols 设定的各字段名
                        // ,type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                        //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                        //,curr: 5 //设定初始在第 5 页
                        //,theme: '#428bca' //当前页码背景色
                        groups: 10 //只显示 1 个连续页码
                        , prev: '上一页'
                        , next: '下一页'
                    },
                    toolbar: '#LAY-Repair-RepairStatis-exportFaultDetailed',
                    defaultToolbar: ['filter']
                    , cols: [[ //表头
                        // {type:'checkbox',fixed: 'left'},
                        {
                            field: 'repid',title: '序号', width: 80, fixed: 'left', align: 'center', type: 'space', templet: function (d) {
                            return d.LAY_INDEX;
                        }
                        }
                        , {field: 'department', fixed: 'left', title: '科室', width: 150, align: 'center'}
                        , {field: 'assnum', title: '设备编号', width: 130, align: 'center'}
                        , {field: 'assets', title: '设备名称', width: 130, align: 'center'}
                        , {field: 'repnum', title: '维修单号', width: 160, align: 'center'}
                        , {field: 'opendate', title: '启用日期', width: 110, align: 'center'}
                        , {field: 'applicant_time', title: '报修日期', width: 110, align: 'center'}
                        , {field: 'applicant', title: '报修人', width: 100, align: 'center'}
                        , {field: 'engineer_time', title: '维修日期', width: 110, align: 'center'}
                        , {field: 'breakdown', title: '故障原因', width:401,  align: 'center'}
                        , {field: 'engineer', title: '维修工程师', width: 98, align: 'center'}
                        , {field: 'status', title: '维修情况', width:78, align: 'center'}
                    ]]
                    , done: function (res, curr, count) {
                        var table_tr = $('#faultSummaryTable .layui-table:last tr');
                        if(res.code===200){
                            $.each(res.rows, function (key, value) {
                                $.each(res.repeat, function (key2, value2) {
                                    if(value2.department==value.department){
                                        if(value2.sum>0){
                                            $($(table_tr).find('td:last')[key]).attr('rowspan',value2.sum);
                                            res.repeat[key2].sum=0;
                                        }else{
                                            $(table_tr).find('td:last')[key].remove();
                                        }
                                    }
                                });
                            });
                        }
                        // $($('.layui-table:last tr td')[1]).remove();

                        // //得到当前页码
                        // console.log(curr);
                        //
                        // //得到数据总量
                        // console.log(count);
                    }
                });
                tablist=1;
            }
        });
        //时间元素渲染
        laydate.render(dateConfig('#faultSummaryStartDate'));
        laydate.render(dateConfig('#faultSummaryEndDate'));
        laydate.render(dateConfig('#tab_startDate'));
        laydate.render(dateConfig('#tab_endDate'));
        //监听搜索按钮
        form.on('submit(faultSummarySearch)', function (data) {
            var field = data.field;
            if (field.startDate && field.endDate) {
                if (field.endDate < field.startDate) {
                    layer.msg('统计日期设置不合理', {icon: 2});
                    return false;
                }
            }
            showChart(field);
            return false;
        });
        //设备故障统计 数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params.type=thisbody.find('select[name="type"]').val();
            params.startDate=thisbody.find('#faultSummaryStartDate').val();
            params.endDate=thisbody.find('#faultSummaryEndDate').val();
            if (params.startDate && params.endDate) {
                if (params.endDate < params.startDate) {
                    layer.msg('统计日期设置不合理', {icon: 2});
                    return false;
                }
            }
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

        //设备故障明细统计 数据导出
        form.on('submit(exportRecordData)', function () {
            var url = $(this).attr('data-url');
            var params = {};
            params.departid=thisbody.find('select[name="departid"]').val();
            params.tab_startDate=thisbody.find('#tab_startDate').val();
            params.tab_endDate=thisbody.find('#tab_endDate').val();
            params.sortAsc=thisbody.find('input[name="sortAsc"]:checked').val();
            if (params.tab_startDate && params.tab_endDate) {
                if (params.tab_startDate > params.tab_endDate) {
                    layer.msg('统计日期设置不合理', {icon: 2});
                    return false;
                }
            }
            postDownLoadFile({
                url: url,
                data: params,
                method: 'POST'
            });
            return false;
        });


        form.on('submit(Tab_eventquery)', function (data) {
            var params = data.field;
            if (params.tab_startDate && params.tab_endDate) {
                if (params.tab_startDate > params.tab_endDate) {
                    layer.msg('统计日期设置不合理', {icon: 2});
                    return false;
                }
            }
            table.reload('faultSummary', {
                url: admin_name+'/RepairStatis/faultSummary.html'
                , where: params
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
                //设定异步数据接口的额外参数
                //,height: 300
            });
            return false;
        });
    });
    exports('repair/statistics/faultSummary', {});
});





/**
 * post请求无法直接发送请求下载excel文档，是因为我们在后台改变了响应头的内容：
 * Content-Type: application/vnd.ms-excel
 * 致post请求无法识别这种消息头,导致无法直接下载。
 * 解决方法：
 * 改成使用form表单提交方式即可
 */
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
// 基于准备好的dom，初始化echarts实例
var myChart = echarts.init(document.getElementById('faultSummaryMain'));
$(function () {
    showChart();
});
function showChart(params) {
    myChart.clear();
    //显示一个简单的加载动画
    myChart.showLoading();
    // 异步加载数据
    $.post(admin_name+'/RepairStatis/faultSummary.html', params).done(function (data) {
        //隐藏加载动画
        myChart.hideLoading();

        if (data.reportTips) {
            showConditions(data.reportTips);
        }
        if (data.tableTh) {
            showTd(data.tableTh);
        }

        if (data.lists) {
            showTable(data.lists);
        } else {
            showTable([]);
        }

        if (data.lists) {
            if (data.max == true) {
                //图表显示详细信息
                var formatter = [
                    '{title|{b}}{abg|}',
                    '  {TabHead|故障原因}{valueHead|维修次数}{rateHead|占比}',
                    '{hr|}'
                ];
                $.each(data.lists[data.maxI]['find'], function (key, val) {
                    formatter.push('  {Tabnr|' + val.name + '}{value|' + val.totalNum + '}{rate|' + val.Ratio + '%}');
                });
                data.series.data[data.maxI]['label'] = {
                    normal: {
                        formatter: formatter.join('\n'),
                        backgroundColor: '#eee',
                        borderColor: '#777',
                        borderWidth: 1,
                        borderRadius: 4,
                        rich: {
                            title: {
                                color: '#eee',
                                align: 'center'
                            },
                            abg: {
                                backgroundColor: '#333',
                                width: '100%',
                                align: 'right',
                                height: 25,
                                borderRadius: [4, 4, 0, 0]
                            },
                            Tabnr: {
                                height: 25,
                                align: 'left',
                                color: '#000'
                            },
                            TabHead: {
                                color: '#333',
                                height: 24,
                                align: 'left'
                            },
                            hr: {
                                borderColor: '#777',
                                width: '100%',
                                borderWidth: 0.5,
                                height: 0
                            },
                            value: {
                                width: 20,
                                padding: [0, 20, 0, 30],
                                align: 'right'
                            },
                            valueHead: {
                                color: '#333',
                                padding: [0, 20, 0, 30],
                                align: 'right'
                            },
                            rate: {
                                width: 40,
                                align: 'right',
                                padding: [0, 10, 0, 0]
                            },
                            rateHead: {
                                color: '#333',
                                align: 'right',
                                padding: [0, 10, 0, 0]
                            }
                        }
                    }
                };
            }

            // 填入数据
            myChart.setOption({
                title: {
                    text: '设备故障统计饼图',
                    subtext: data.reportTips,
                    left:'10'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c}"
                },
                legend: {
                    show: true,//是否显示
                    type: 'scroll',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                    //orient: 'vertical',//图例列表的布局朝向vertical/horizontal
                    left: 'center',
                    top: '60',
                    bottom: 10,
                    width: '',//图例组件宽度。一般不用设置，默认宽度
                    data: data.legend,
                    selected:data.selected
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
                series: data.series,
                itemStyle: {
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            });
            // changeCanvas();
        }
    });
}
//改变表格数据
function showTable(res) {
    var html = '';
    var tNum = 0;
    var tRatio = 0;
    $('.faultSummaryConData').html('');
    $.each(res, function (e, val) {
        tNum += parseInt(val.totalNum);
        tRatio = (tRatio * 100 + parseFloat(val.Ratio) * 100) / 100
        html += '<tr>' +
            '<td>' + (e + 1) + '</td>\n' +
            '<td>' + val.title + '</td>\n' +
            '<td>' + val.totalNum + '</td>\n' +
            '<td>' + val.Ratio + '%</td>\n' +
            '</tr>';
    });
    html += '<tr>' +
        '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n' +
        '<td class="total-font-color">' + tNum + '</td>\n' +
        '<td class="total-font-color">' + (tRatio>0?100:0 )+ '%</td>\n' +
        '</tr>';
    $('.faultSummaryConData').append(html);
}
//修改显示的查询条件
function showConditions(res) {
    $('.faultSummaryReportConditions').html(res);
}
//改变表格的分类类型
function showTd(res) {
    $('.faultSummaryTableTh').html(res);
}



