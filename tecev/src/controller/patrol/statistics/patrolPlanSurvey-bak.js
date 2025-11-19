layui.define(function(exports){
    layui.use(['layer', 'form','laydate','formSelects'], function() {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,laydate = layui.laydate,formSelects = layui.formSelects;

        //先更新页面部分需要提前渲染的控件
        form.render();
        //年选择器
        laydate.render({
            elem: '#year'
            ,value: current_year
            ,type: 'year'
        });
        //渲染多选下拉
        formSelects.render();
        //开始时间元素渲染
        laydate.render(dateConfig('#patrolPlanSurveyStartDate'));
        laydate.render(dateConfig('#patrolPlanSurveyEndDate'));

        formSelects.render('patrolPlanSurveyDepartment', selectParams(1));
        formSelects.btns('patrolPlanSurveyDepartment', selectParams(2), selectParams(3));
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
        form.on('submit(patrolPlanSurveySearch)', function (data) {
            params = data.field;
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            params.departids = formSelects.value('patrolPlanSurveyDepartment', 'valStr');
            showChart(params);
            return false;
        });
        //数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            var params = {};
            params.startDate = $('input[name="patrolPlanSurveyStartDate"]').val();
            params.endDate = $('input[name="patrolPlanSurveyEndDate"]').val();
            params.departids = formSelects.value('patrolPlanSurveyDepartment', 'valStr');
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
            params.departids = formSelects.value('patrolPlanSurveyDepartment', 'valStr');
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
    var myChart = echarts.init(document.getElementById('patrolPlanSurveyMain'));
    var colors = ['#5793f3', '#d14a61', '#675bba'];
    $(function(){
        showChart();
    });
    function showChart(params) {
        myChart.clear();
        //显示一个简单的加载动画
        myChart.showLoading();
        // 异步加载数据
        $.post(admin_name+'/PatrolStatis/patrolPlanSurvey.html',params).done(function (data) {
            //隐藏加载动画
            myChart.hideLoading();
            //console.log(data.series);
            if(data.lists){
                showTable(data.lists);
                showConditions(data.reportTips);
                showTitle(data.reportTitle);
                // 填入数据
                myChart.setOption({
                    title : {
                        text: '科室巡查计划概况统计',
                        subtext: data.reportTips
                    },
                    tooltip : {
                        trigger: 'axis',
                        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                        }
                    },
                    legend: {
                        data:data.legend
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    yAxis : [
                        {
                            type : 'category',
                            axisTick : {show: false},
                            data : data.departments
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
        var pNum = 0;
        var aNum = 0;
        $('.patrolPlanSurveyConData').html('');
        $.each(res,function(e,val){
            tNum += parseInt(val.totalNum);
            pNum += parseInt(val.planNum);
            aNum += parseFloat(val.abnormalNum);
            html += '<tr>';
            html += '<td>'+(e+1)+'</td>';
            if(val.totalNum > 0){
                html += '<td>'+val.departPlanDetail+'</td>';
            }else{
                html += '<td>'+val.department+'</td>';
            }
            html += '<td>'+val.totalNum+'</td>';
            if(val.planNum > 0){
                html += '<td style="color: #009688;">'+val.planNum+'</td>';
            }else{
                html += '<td>'+val.planNum+'</td>';
            }
            html += '<td>'+val.planRate+'</td>';
            if(val.abnormalNum > 0){
                html += '<td style="color: red;">'+val.abnormalNum+'</td>';
            }else{
                html += '<td>'+val.abnormalNum+'</td>';
            }
            html += '<td>'+val.abnormalRate+'</td>';
            html += '<td>'+val.operation+'</td>';
            html += '</tr>';
        });
        html += '<tr>' +
            '<td colspan="2" style="text-align: right;color:red;">合计：</td>\n' +
            '<td class="total-font-color">'+tNum+'</td>\n' +
            '<td class="total-font-color">'+pNum+'</td>\n' +
            '<td class="total-font-color"></td>\n' +
            '<td class="total-font-color">'+aNum+'</td>\n' +
            '<td class="total-font-color"></td>\n' +
            '</tr>';
        $('.patrolPlanSurveyConData').append(html);

        //科室巡查详情
        $('.departmentPatrol').on('click',function() {
            var url = $(this).attr('data-url');
            top.layer.open({
                type: 2,
                title: $(this).html(),
                shade: 0,
                anim:2,
                offset: 'r',//弹窗位置固定在右边
                scrollbar:false,
                area: ['95%', '100%'],
                closeBtn: 1,
                content: [url]
            });
            return false;
        });
    }
    function showConditions(res) {
        $('.patrolPlanSurveyReportConditions').html(res);
    }
    function showTitle(res) {
        $('.patrolPlanSurveyReportTitle').html(res);
    }
    exports('patrol/statistics/patrolPlanSurvey', {});
});

