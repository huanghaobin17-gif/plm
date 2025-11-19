layui.define(function(exports){

    layui.use(['layer', 'form','laydate'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,laydate = layui.laydate;
        //先更新页面部分需要提前渲染的控件
        form.render();

        laydate.render(dateConfig('#assetsSurveyStartDate'));//开始时间元素渲染
        laydate.render(dateConfig('#assetsSurveyEndDate'));//结束时间元素渲染

        form.verify({
            priceMin: function (value) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            },
            priceMax: function (value) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            }
        });
        //监听搜索按钮
        form.on('submit(assetsSurveySearch)', function (data) {
            params = data.field;
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            showChart(params);
            return false;
        });
        //数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params.startDate = $('input[name="assetsSurveyStartDate"]').val();
            params.endDate = $('input[name="assetsSurveyEndDate"]').val();
            params.priceMin = $('input[name="assetsSurveyPriceMin"]').val();
            params.priceMax = $('input[name="assetsSurveyPriceMax"]').val();
            params.hospital_id = $('select[name="hospital_id"] option:selected').val();
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            params.base64Data = myChart.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            postDownLoadFile({
                url:url,
                data:params,
                method:'POST'
            });
            return false;
        });
    });
    /**
     * post请求无法直接发送请求下载excel文档，是因为我们在后台改变了响应头的内容：
     * Content-Type: application/vnd.ms-excel
     * 致post请求无法识别这种消息头,导致无法直接下载。
     * 解决方法：
     * 改成使用form表单提交方式即可
     */
    var postDownLoadFile = function (options) {
        var config = $.extend(true, { method: 'POST' }, options);
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
    var myChart = echarts.init(document.getElementById('assetsSurveyMain'));
    $(function(){
        var params = {};
        params.assetsSurveyStartDate = $('input[name="assetsSurveyStartDate"]').val();
        params.assetsSurveyEndDate = $('input[name="assetsSurveyEndDate"]').val();
        showChart(params);
    });
    function showChart(params) {
        //显示一个简单的加载动画
        myChart.showLoading();
        // 异步加载数据
        $.post(admin_name+'/AssetsStatis/assetsSurvey.html',params).done(function (data) {
            //隐藏加载动画
            myChart.hideLoading();
            if(data.lists){
                showTable(data.lists);
                showConditions(data.reportTips);
                showTitle(data.reportTitle);
                // 填入数据
                myChart.setOption({
                    title : {
                        text: '资产概况图表',
                        subtext: data.reportTips
                    },
                    tooltip : {
                        trigger: 'axis'
                    },
                    legend: {
                        show:true,//是否显示
                        type:'plain',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                        left:'25%',//图例组件离容器左侧的距离。
                        top:'5',//图例组件离容器上侧的距离。
                        width:'',//图例组件宽度。一般不用设置，默认宽度
                        x: 'right',
                        data:data.legend,
                        selected: data.selected
                    },
                    dataZoom: [
                        {
                            type: 'slider',
                            xAxisIndex: 0,//作用在X轴
                            start: 0,//左边在 0% 的位置。
                            end: 100// 右边在 100% 的位置。
                        },
                        {
                            type: 'slider',
                            yAxisIndex: 0,
                            start: 0,
                            end: 100
                        }
                    ],
                    grid: {
                        top: 80,//grid 组件离容器上侧的距离
                        left:'5%',//grid 组件离容器上侧的距离
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
                        show : true,
                        feature : {
                            dataView : {show: true, readOnly: false},
                            magicType : {show: true, type: ['line', 'bar']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    calculable : true,
                    xAxis : [
                        {
                            type : 'category',
                            nameRotate:9,//坐标轴名字旋转角度值。
                            data : data.departments,
                            axisLabel: {
                                interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                rotate:70//标签倾斜的角度
                            }
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value',
                            name: '数量（台）',//坐标轴名称
                            nameGap:15,//坐标轴名称与轴线之间的距离。
                            nameRotate:0//坐标轴名字旋转角度值。
                        },
                        {
                            type: 'value',
                            name: '总金额（元）'
                        }
                    ],
                    series : data.series
                });
            }
        });
    }
    function showTable(res) {
        var html = '';
        var tNum = 0;
        var tPrice = 0;
        var tWorking = 0;
        var tRepairing = 0;
        var tScraped = 0;
        var tnewAddNum = 0;
        var tnewAddPrice = 0;
        $('.assetsSurveyConData').html('');
        $.each(res,function(e,val){
            tNum += parseInt(val.totalNum);
            tPrice += parseFloat(val.totalPrice);
            tWorking += parseInt(val.working);
            tRepairing += parseInt(val.repairing);
            tScraped += parseInt(val.scraped);
            tnewAddNum += parseInt(val.newAddNum);
            tnewAddPrice += parseFloat(val.newAddPrice);
            html += '<tr>' +
                '<td>'+(e+1)+'</td>\n' +
                '<td>'+val.department+'</td>\n' +
                '<td>'+val.totalNum+'</td>\n' +
                '<td>'+val.totalPrice+'</td>\n' +
                '<td>'+val.working+'</td>\n' +
                '<td>'+val.repairing+'</td>\n' +
                '<td>'+val.scraped+'</td>\n' +
                '<td>'+val.newAddNum+'</td>\n' +
                '<td>'+val.newAddPrice+'</td>\n' +
                '</tr>';
        });
        html += '<tr>' +
            '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n' +
            '<td class="total-font-color">'+tNum+'</td>\n' +
            '<td class="total-font-color">'+tPrice+'</td>\n' +
            '<td class="total-font-color">'+tWorking+'</td>\n' +
            '<td class="total-font-color">'+tRepairing+'</td>\n' +
            '<td class="total-font-color">'+tScraped+'</td>\n' +
            '<td class="total-font-color">'+tnewAddNum+'</td>\n' +
            '<td class="total-font-color">'+tnewAddPrice+'</td>\n' +
            '</tr>';
        $('.assetsSurveyConData').append(html);
    }
    function showConditions(res) {
        $('.assetsSurveyReportConditions').html(res);
    }
    function showTitle(res) {
        $('.assetsSurveyReportTitle').html(res);
    }
    exports('assets/statistics/assetsSurvey', {});
});