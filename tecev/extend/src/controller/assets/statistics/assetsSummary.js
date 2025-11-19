layui.define(function(exports){

    layui.use(['table', 'layer', 'form', 'laydate', 'formSelects', 'tablePlug'], function () {
        var form = layui.form, table = layui.table, $ = layui.jquery, layer = layui.layer, laydate = layui.laydate, formSelects = layui.formSelects, tablePlug = layui.tablePlug;


        layer.config(layerParmas());
        laydate.render(dateConfig('#assetsSummaryStartDate'));//开始时间元素渲染
        laydate.render(dateConfig('#assetsSummaryEndDate'));//结束时间元素渲染

        //渲染所有多选下拉
        formSelects.render('assetsSummaryDepartment', selectParams(1));
        formSelects.btns('assetsSummaryDepartment', selectParams(2), selectParams(3));
        //先更新页面部分需要提前渲染的控件
        form.render();
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
        var department_nums_result = echarts.init(document.getElementById('department_nums_result'));
        department_nums_result.clear();
        department_nums_result.showLoading();
        table.render({
            elem: '#assetsSummaryLists'
            , limits: [200]
            , loading: true
            , limit: 200
            ,title: '各科室设备汇总'
            , url: assetsSummary //数据接口
            , where: {
                sort: 'totalNum'
                , order: 'desc',
                assetsSummaryStartDate:$('input[name="assetsSummaryStartDate"]').val(),
                assetsSummaryEndDate:$('input[name="assetsSummaryEndDate"]').val()
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'totalNum' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
            ,page: false, //开启分页
            totalRow: true,
            toolbar: '#LAY-Assets-AssetsStatis-assetsSummaryToolbar',
            defaultToolbar: ['filter']
            , cols: [[ //表头
                {field: 'departid',title: '序号',unresize: true,totalRowText: '合计',width: 50,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                ,{field: 'department',fixed: 'left',title: '科室名称',width: 140,align: 'center'}
                ,{field: 'totalNum',title: '设备总数',totalRow: true,totalNums:0,width: 90,align: 'center'}
                ,{field: 'totalPrice',title: '总金额(元)',totalRow: true,width: 140,align: 'center'}
                ,{field: 'guaranteeNum',title: '维保期内设备数',totalRow: true,totalNums:0,width: 130,align: 'center'}
                ,{field: 'repairNum',title: '报修次数',totalRow: true,totalNums:0,width: 90,align: 'center'}
                ,{field: 'overRepairNum',title: '修复次数',totalRow: true,totalNums:0,width: 90,align: 'center'}
                ,{field: 'partNum',title: '维修配件数',totalRow: true,totalNums:0,width: 100,align: 'center'}
                ,{field: 'actualPrice',title: '维修总费用(元)',totalRow: true,width: 130,align: 'center'}
                ,{field: 'patrolPlanNum',title: '保养计划次数',totalRow: true,totalNums:0,width: 120,align: 'center'}
                ,{field: 'implementNum',title: '保养执行次数',totalRow: true,totalNums:0,width: 120,align: 'center'}
                ,{field: 'operation',fixed: 'right',title: '操作',width: 120,align: 'center'}

            ]]
            , done: function (res, curr, count) {
                showConditions(res.reportTips);
                showTitle(res.reportTitle);
                var depart_nums_result = echarts.init(document.getElementById('department_nums_result'));
                depart_nums_result.clear();
                depart_nums_result.hideLoading();
                if(!$.isEmptyObject(res.rows)){
                    depart_nums_result.setOption({
                        tooltip : {
                            trigger: 'axis',
                            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                            }
                        },
                        legend: {
                            data: res['legend']['num']
                        },
                        grid: {
                            left: '2%',
                            right: '2%',
                            bottom: '1%',
                            containLabel: true
                        },
                        yAxis:  {
                            type: 'value'
                        },
                        xAxis: {
                            type: 'category',
                            data: res['yAxis']['data'],
                            axisLabel: {
                                interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                rotate:70//标签倾斜的角度
                            }
                        },
                        series: res['series']['num']
                    })
                }else{
                    no_data_chart(depart_nums_result);
                }
            }
        });
        function no_data_chart(idname) {
            idname.setOption({
                color:"#E6E6E6",
                title : {
                    subtext: '暂无相关数据',
                    x:'center'
                }
            });
        }
        //监听搜索按钮
        form.on('submit(assetsSummarySearch)', function (data) {
            gloabOptions = data.field;
            if (gloabOptions.priceMin && gloabOptions.priceMax) {
                if (parseFloat(gloabOptions.priceMax) < parseFloat(gloabOptions.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            gloabOptions.departids = formSelects.value('assetsSummaryDepartment', 'valStr');
            table.reload('assetsSummaryLists', {
                url: assetsSummary
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //监听工具条
        table.on('tool(assetsSummaryData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'depart_detail') {
                top.layer.open({
                    type: 2,
                    title: '科室设备详情【' + rows.department + '】',
                    area: ['1200px', '100%'],
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    scrollbar: false,
                    isOutAnim: true,
                    closeBtn: 1,
                    content: [url]
                });
            }
        });

        //数据导出
        form.on('submit(exportData)', function (data) {
            var url = $(this).attr('data-url');
            params = {};
            params.startDate = $('input[name="assetsSummaryStartDate"]').val();
            params.endDate = $('input[name="assetsSummaryEndDate"]').val();
            params.priceMin = $('input[name="assetsSummaryPriceMin"]').val();
            params.priceMax = $('input[name="assetsSummaryPriceMax"]').val();
            params.departids = formSelects.value('assetsSummaryDepartment', 'valStr');
            params.hospital_id = $('select[name="hospital_id"] option:selected').val();
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            var myChart = echarts.init(document.getElementById('department_nums_result'));
            params.base64Data = myChart.getDataURL({
                pixelRatio: 1.2,//像素精度
                backgroundColor: '#fff'
            });
            params.departids = formSelects.value('assetsSummaryDepartment', 'valStr');
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
    function showConditions(res) {
        $('.assetsSummaryReportConditions').html(res);
    }
    function showTitle(res) {
        $('.assetsSummaryReportTitle').html(res);
    }
    exports('assets/statistics/assetsSummary', {});
});


