layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'table', 'tablePlug'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, table = layui.table, tablePlug = layui.tablePlug;
        laydate.render(dateConfig('#onUseAssetsSurveyStartDate'));//开始时间元素渲染
        laydate.render(dateConfig('#onUseAssetsSurveyEndDate'));//结束时间元素渲染
        //先更新页面部分需要提前渲染的控件
        form.render();
        console.log($('input[name="onUseAssetsSurveyStartDate"]').val());
        table.render({
            elem: '#onUseAssetsSurveyConData'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: admin_name+'/AssetsStatis/onUseAssetsSurvey' //数据接口
            , where: {
                onUseAssetsSurveyStartDate:$('input[name="onUseAssetsSurveyStartDate"]').val(),
                onUseAssetsSurveyEndDate:$('input[name="onUseAssetsSurveyEndDate"]').val()
            } //如果无需传递额外参数，可不加该参数
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'lists' //数据字段
            },
            toolbar: '#LAY-Assets-AssetsStatis-onUseAssetsSurveyToolbar',
            defaultToolbar: ['filter']
            ,page: false //开启分页
            , cols: [[ //表头
                {
                    field: 'apply_id', title: '序号', width: 80, fixed: 'left', align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'department', title: '科室名称', width: 200, align: 'center'},
                {field: 'totalNum', title: '设备总数量', width: 160, align: 'center'},
                {field: 'onUse', title: '正常在用数量', width: 160, align: 'center'},
                {field: 'other', title: '其他数量', width: 160, align: 'center'},
                {field: 'totalPrice', title: '设备总金额（元）', minWidth: 220, align: 'center'}
            ]]
            ,done: function(res, curr, count){
                var html = '';
                var tNum = uNum = oNum = 0;
                var tPrice = 0;
                $.each(res.lists,function(e,val){
                    tNum += parseInt(val.totalNum);
                    uNum += parseInt(val.onUse);
                    oNum += parseInt(val.other);
                    tPrice += parseFloat(val.totalPrice);
                });
                html += '<tr>' +
            '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n' +
            '<td class="total-font-color">'+tNum+'</td>\n' +
            '<td class="total-font-color">'+uNum+'</td>\n' +
            '<td class="total-font-color">'+oNum+'</td>\n' +
            '<td class="total-font-color">'+tPrice.toFixed(2)+'</td>\n' +
            '</tr>';
            $('.layui-table-main .layui-table').append(html);
            }
        });
        form.verify({
            priceMin: function (value,item) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            },
            priceMax: function (value,item) {
                if (value){
                    if (!/^\d+(\.\d+)?$/.test(value + "")) {
                        return "请输入大于等于0的金额";
                    }
                }
            }
        });
        //监听搜索按钮
        form.on('submit(onUseAssetsSurveySearch)', function (data) {
            params = data.field;
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            params.type = $("select[name='type']").val();
            showChart(params.type,params);
            table.reload('onUseAssetsSurveyConData', {
                url: admin_name+'/AssetsStatis/onUseAssetsSurvey'
                ,where: params
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        form.on('select(type)', function(data){
            var params = {};
            params.startDate = $("input[name='startDate']").val();
            params.endDate = $("input[name='endDate']").val();
            params.priceMin = $("input[name='priceMin']").val();
            params.priceMax = $("input[name='priceMax']").val();
            params.hospital_id = $("select[name='hospital_id'] option:selected").val();
            params.type = data.value;
            showChart(params.type,params);
            return false;
        });
        //数据导出 
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params.startDate = $("input[name='startDate']").val();
            params.endDate = $("input[name='endDate']").val();
            params.priceMin = $("input[name='priceMin']").val();
            params.priceMax = $("input[name='priceMax']").val();
            params.hospital_id = $("select[name='hospital_id'] option:selected").val();
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
    var myChart = echarts.init(document.getElementById('onUseAssetsSurveyMain'));
    $(function(){
        var params = {};
        params.onUseAssetsSurveyStartDate = $('input[name="onUseAssetsSurveyStartDate"]').val();
        params.onUseAssetsSurveyEndDate = $('input[name="onUseAssetsSurveyEndDate"]').val();
        showChart('pie',params);
    });
    function showChart(type,params) {
        myChart.clear();
        //显示一个简单的加载动画
        myChart.showLoading();
        if(type == 'pie'){
            // 异步加载数据
            $.post(admin_name+'/AssetsStatis/onUseAssetsSurvey.html',params).done(function (data) {
                //隐藏加载动画
                myChart.hideLoading();
                if(data.lists){
                    showConditions(data.reportTips);
                    showTitle(data.reportTitle);
                    // 填入数据
                    myChart.setOption({
                        title : {
                            text: '各科室在用资产统计',
                            subtext: data.reportTips
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c}"
                        },
                        legend: {
                            show:true,//是否显示
                            type:'scroll',//'plain'：普通图例。缺省就是普通图例。'scroll'：可滚动翻页的图例。当图例数量较多时可以使用
                            orient: 'vertical',//图例列表的布局朝向vertical/horizontal
                            right: '0%',
                            top: '80px',
                            bottom: 10,
                            width:'',//图例组件宽度。一般不用设置，默认宽度
                            data:data.legend
                        },
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
                                //dataView : {show: true, readOnly: false},
                                //magicType : {show: true, type: ['pie', 'bar']},
                                //restore : {show: true},
                                saveAsImage : {show: true}
                            }
                        },
                        calculable : true,
                        series : data.series,
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
        if(type == 'bar'){
            // 异步加载数据
            $.post(admin_name+'/AssetsStatis/onUseAssetsSurvey.html',params).done(function (data) {
                //隐藏加载动画
                myChart.hideLoading();
                if(data.lists){
                    // 填入数据
                    myChart.setOption({
                        title : {
                            text: '各科室在用资产统计',
                            subtext: data.reportTips
                        },
                        tooltip : {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c}"
                        },
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
                                //dataView : {show: true, readOnly: false},
                                //magicType : {show: true, type: ['pie', 'bar']},
                                //restore : {show: true},
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
                                name: '数量（台）',//坐标轴名称
                                nameGap:15,//坐标轴名称与轴线之间的距离。
                                nameRotate:0//坐标轴名字旋转角度值。
                            }
                        ],
                        series : data.series,
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
    }
    function showConditions(res) {
        $('.onUseAssetsSurveyReportConditions').html(res);
    }
    function showTitle(res) {
        $('.onUseAssetsSurveyReportTitle').html(res);
    }
    exports('assets/statistics/onUseAssetsSurvey', {});
});