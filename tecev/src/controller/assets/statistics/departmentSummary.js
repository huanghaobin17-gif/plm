layui.define(function(exports){
    layui.use(['table', 'layer', 'form', 'laydate', 'tablePlug'], function () {
        var form = layui.form,
            table = layui.table,
            $ = layui.jquery,
            layer = layui.layer,
            laydate = layui.laydate
            , tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        laydate.render(dateConfig('#startDate'));
        laydate.render(dateConfig('#endDate'));
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
        var departid = $('input[name="departid"]').val();

        var tdiv = $('.div-chart-show-pic');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.div-chart-show-pic-chart').attr('id');
            if(tmpid){
                ids.push(tmpid);
            }
        });
        $.each(ids,function (index,item) {
            var idname = echarts.init(document.getElementById(item));
            idname.clear();
            idname.showLoading();
        });
        table.render({
            elem: '#departSummaryLists'
            , limits: [200]
            , loading: true
            , limit: 200
            ,title: '科室设备电子账单'
            , url: assetsSummary //数据接口
            , where: {
                sort: 'repairNum'
                , order: 'desc'
                , type: 'departmentSummary'
                , departid:departid
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'repairNum' //排序字段，对应 cols 设定的各字段名
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
            toolbar: '#LAY-Assets-Statistics-departSummaryToolbar',
            defaultToolbar: ['filter']
            , cols: [[ //表头
                {field: 'departid',title: '序号',unresize: true,totalRowText: '合计',width: 50,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                ,{field: 'assnum',fixed: 'left',title: '设备编号',width: 140,align: 'center'}
                ,{field: 'assets',title: '设备名称',width: 180,align: 'center'}
                ,{field: 'isGuarantee',title: '保修期内',width: 100,align: 'center'}
                ,{field: 'totalPrice',title: '设备金额(元)',totalRow: true,width: 140,align: 'center'}
                ,{field: 'repairNum',title: '报修次数',totalRow: true,width: 90,align: 'center'}
                ,{field: 'overRepairNum',title: '修复次数',totalRow: true,width: 90,align: 'center'}
                ,{field: 'partNum',title: '维修配件数',totalRow: true,width: 100,align: 'center'}
                ,{field: 'actualPrice',title: '维修总费用(元)',totalRow: true,width: 130,align: 'center'}
                ,{field: 'totalHours',title: '维修工时',totalRow: true,width: 130,align: 'center'}
                ,{field: 'patrolPlanNum',title: '保养计划次数',totalRow: true,width: 120,align: 'center'}
                ,{field: 'implementNum',title: '保养执行次数',totalRow: true,width: 120,align: 'center'}
                ,{field: 'operation',fixed: 'right',title: '操作',width: 270,align: 'center'}
            ]]
            , done: function (res, curr, count) {
                //showConditions(res.reportTips);
                //showTitle(res.reportTitle);
                var tdiv = $('.div-chart-show-pic');
                var ids = [];
                $.each(tdiv,function (index,item) {
                    var tmpid = $(this).find('.div-chart-show-pic-chart').attr('id');
                    if(tmpid){
                        ids.push(tmpid);
                    }
                });
                $.each(ids,function (index,item) {
                    var idname = echarts.init(document.getElementById(item));
                    idname.clear();
                    idname.hideLoading();
                    if(!$.isEmptyObject(res.rows)){
                        idname.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: "{a} <br/>{b}: {c} ({d}%)"
                            },
                            legend: {
                                data:res['legend'][item]
                            },
                            series: [
                                {
                                    name:'访问来源',
                                    type:'pie',
                                    selectedMode: 'single',
                                    radius: [0, '30%'],
                                    label: {
                                        normal: {
                                            position: 'inner'
                                        }
                                    },
                                    labelLine: {
                                        normal: {
                                            show: false
                                        }
                                    }
                                },
                                {
                                    name:'设备金额区间',
                                    type:'pie',
                                    radius: ['40%', '55%'],
                                    label: {
                                        normal: {
                                            formatter: '{a|{a}}{abg|}\n{hr|}\n  {b|{b}：}{c}  {per|{d}%}  ',
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
                                                    fontSize: 16,
                                                    lineHeight: 33
                                                },
                                                per: {
                                                    color: '#eee',
                                                    backgroundColor: '#334455',
                                                    padding: [2, 4],
                                                    borderRadius: 2
                                                }
                                            }
                                        }
                                    },
                                    data:res['series'][item]
                                }
                            ]
                        })
                    }else{
                        no_data_chart(idname);
                    }
                });
            }
        });
        //监听搜索按钮
        form.on('submit(eventquery)', function (data) {
            gloabOptions = data.field;
            gloabOptions.type ='departmentSummary';
            if (gloabOptions.priceMin && gloabOptions.priceMax) {
                if (parseFloat(gloabOptions.priceMax) < parseFloat(gloabOptions.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            table.reload('departSummaryLists', {
                url: assetsSummary
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        //数据导出
        form.on('submit(exportDepartData)', function (data) {
            var url = $(this).attr('data-url');
            var params={};
            params.startDate=$('input[name="startDate"]').val();
            params.endDate=$('input[name="endDate"]').val();
            params.priceMin=$('input[name="priceMin"]').val();
            params.priceMax=$('input[name="priceMax"]').val();
            params.departid=$('input[name="departid"]').val();
            if (params.priceMin && params.priceMax) {
                if (parseFloat(params.priceMax) < parseFloat(params.priceMin)) {
                    layer.msg("金额区间设置不正确！", {icon: 2}, 1000);
                    return false;
                }
            }
            var myChart = echarts.init(document.getElementById('assets_price'));
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

        //监听工具条
        table.on('tool(departSummaryData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'assets_detail':
                    top.layer.open({
                        type: 2,
                        title:'设备基本信息【'+rows.assets+'】',
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['1050px', '100%'],
                        closeBtn: 1,
                        content: [admin_name+'/Lookup/showAssets?assid='+$(this).attr('data-id')]
                    });
                    break;
                case 'showRepairRecord':
                    top.layer.open({
                        type: 2,
                        title:'设备维修记录【'+rows.assets+'】',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['1100px', '100%'],
                        closeBtn: 1,
                        content: [url+'?type=showRepairRecord&assid='+$(this).attr('data-id')]
                    });
                    break;
                case 'showRepairParts':
                    top.layer.open({
                        type: 2,
                        title:'设备维修记录【'+rows.assets+'】',
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url+'?type=showRepairParts&assid='+$(this).attr('data-id')]
                    });
                    break;
                case 'showPatrolPlan':
                    var startTime = $('input[name="startDate"]').val();
                    var endTime = $('input[name="endDate"]').val();
                    top.layer.open({
                        type: 2,
                        title:'巡查保养记录【'+rows.assets+'】',
                        scrollbar:false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url+'?type=showPatrolPlan&assnum='+$(this).attr('data-assnum')+'&startTime='+startTime+'&endTime='+endTime]
                    });
                    break;
            }

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
        $('.reportConditions').html(res);
    }
    function showTitle(res) {
        $('.reportTitle').html(res);
    }
    exports('controller/assets/statistics/departmentSummary', {});
});
