layui.define(function(exports){
    var gloabOptions = {};
    var gloabOptionsB = {};
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'suggest', 'element', 'formSelects', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , suggest = layui.suggest
            , element = layui.element
            , formSelects = layui.formSelects
            , table = layui.table, tablePlug = layui.tablePlug;
        suggest.search();
        //渲染所有多选下拉
        formSelects.render();
        //管理科室 多选框初始配置
        formSelects.render('qualityStaDepartments', selectParams(1));
        formSelects.btns('qualityStaDepartments', selectParams(2));

        //品牌 多选框初始配置
        formSelects.render('qualityStaBrand', selectParams(1));
        formSelects.btns('qualityStaBrand', selectParams(2));

        //管理科室 多选框初始配置
        formSelects.render('qualityStaDepartmentsB', selectParams(1));
        formSelects.btns('qualityStaDepartmentsB', selectParams(2));

        //品牌 多选框初始配置
        formSelects.render('qualityStaBrandB', selectParams(1));
        formSelects.btns('qualityStaBrandB', selectParams(2));



        laydate.render({
            elem: '#resultAnalysisSR'
        });
        laydate.render({
            elem: '#resultAnalysisER'
        });
        laydate.render({
            elem: '#resultAnalysisSB'
        });
        laydate.render({
            elem: '#resultAnalysisEB'
        });
        form.render();




        var tdiv = $('.quality_result_staticsic .div-chart-show-pic');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.div-chart-show-pic-chart').attr('id');
            if(tmpid){
                ids.push(tmpid);
            }
        });

        //科室质控设备数统计
        table.render({
            elem: '#department_result_lists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '用户列表'
            ,url: resultAnalysis //数据接口
            ,where: {
                action:'getLists'
                ,type:'department_result'
            } //如果无需传递额外参数，可不加该参数
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'departid',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    totalRowText: '合计',
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'department', fixed: 'left', title: '科室名称', width: 180, align: 'center'},
                {field: 'assets_nums', align: 'center', width: 100, totalRow: true,totalNums:0, title: '质控设备数'},
                {field: 'assets_rate', align: 'center', width: 120, totalRow: true, title: '设备数占比'},
                {field: 'tem_1', align: 'center', width: 120, totalRow: true,totalNums:0, title: '监护仪(模板)'},
                {field: 'tem_2', align: 'center', width: 125, totalRow: true,totalNums:0, title: '输液装置(模板)'},
                {field: 'tem_3', align: 'center', width: 120, totalRow: true,totalNums:0, title: '除颤仪(模板)'},
                {field: 'tem_4', align: 'center', width: 120, totalRow: true,totalNums:0, title: '呼吸机(模板)'},
                {field: 'res_1', align: 'center', width: 100, totalRow: true,totalNums:0, title: '合格数'},
                {field: 'res_2', align: 'center', width: 100, totalRow: true,totalNums:0, title: '不合格数'},
                {field: 'res_rate', align: 'center', width: 100, title: '合格率'},
                {field: 'depart_res_rate', align: 'center', width: 120, totalRow: true, title: '合格数占比'}
            ]]
            , done: function (res, curr, count) {
                var department_result = echarts.init(document.getElementById('department_result'));
                department_result.clear();
                department_result.hideLoading();
                var max = res.max;
                if(!$.isEmptyObject(res.charData)){
                    department_result.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                        },
                        legend: {
                            type: 'scroll',
                            orient: 'vertical',
                            top:'2%',
                            x: 'right',
                            data:res['charData']['legend_data']
                        },
                        series: [
                            {
                                name:'科室质控设备统计',
                                type:'pie',
                                radius: ['60%', '80%'],
                                center:['35%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'center',
                                        fontSize:'16',
                                        formatter : function(param){
                                            if(param.data.is_show){
                                                return param.name+'  '+param.value+' \n占比 '+param.data.precent;
                                            }else{
                                                return '';
                                            }
                                        }
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '20',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:res['charData']['series_data']
                            }
                        ]
                    })
                }else{
                    no_data_chart(department_result);
                }
            }
        });


        //质控模板数统计
        table.render({
            elem: '#template_nums_lists'
            ,loading:true
            ,limit: 100
            ,title: '模板使用次数统计列表'
            ,url: resultAnalysis //数据接口
            ,where: {
                action:'getLists',
                type:'template_nums'
            } //如果无需传递额外参数，可不加该参数
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,totalRow: true

            ,defaultToolbar: ['exports']
            ,page: false //开启分页
            ,cols: [[ //表头
                {
                    field:'qtemid',
                    title:'序号',
                    width:80,
                    fixed: 'left',
                    align:'center',
                    unresize: true,
                    totalRowText: '合计',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'tem_name',
                    fixed: 'left',
                    title: '质控模板名称',
                    width: 200,
                    align:'center'
                },
                {
                    field: 'total_nums',
                    align:'center',
                    width: 160,
                    totalRow: true,
                    totalNums:0,
                    title: '使用次数'
                },
                {
                    field: 'pass_nums',
                    align:'center',
                    width: 160,
                    totalRow: true,
                    totalNums:0,
                    title: '结果合格次数'
                },
                {
                    field: 'notpass_nums',
                    align:'center',
                    width: 160,
                    totalRow: true,
                    totalNums:0,
                    title: '结果不合格次数'
                }
                ,{
                    field: 'pass_rate',
                    title: '合格次数占比',
                    align:'center'
                }
            ]]
            , done: function (res, curr, count) {
                var departFee = echarts.init(document.getElementById('template_nums'));
                departFee.clear();
                departFee.hideLoading();
                if(!$.isEmptyObject(res.charData)){
                    departFee.setOption({
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b}: {c} ({d}%)"
                        },
                        legend: {
                            type: 'scroll',
                            orient: 'vertical',
                            top:'2%',
                            x: 'right',
                            data:res['charData']['legend_data']
                        },
                        series: [
                            {
                                name:'模板使用次数分析',
                                type:'pie',
                                radius: ['60%', '80%'],
                                center:['35%', '50%'],
                                avoidLabelOverlap: false,
                                label: {
                                    normal: {
                                        show: true,
                                        position: 'center',
                                        fontSize:'16',
                                        formatter : function(param){
                                            if(param.data.is_show){
                                                return param.name+'  '+param.value+' \n占比 '+param.data.precent;
                                            }else{
                                                return '';
                                            }
                                        }
                                    },
                                    emphasis: {
                                        show: true,
                                        textStyle: {
                                            fontSize: '20',
                                            fontWeight: 'bold'
                                        }
                                    }
                                },
                                labelLine: {
                                    normal: {
                                        show: false
                                    }
                                },
                                data:res['charData']['series_data']
                            }
                        ]
                    })
                }else{
                    no_data_chart(departFee);
                }
            }
        });


        //监听质控结果统计搜索
        form.on('submit(repairAnalysisSearchR)', function(data){
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            if(gloabOptions.startDate && gloabOptions.endDate){
                if(gloabOptions.startDate > gloabOptions.endDate){
                    layer.msg("请输入合理的日期",{icon : 2,time:1000});
                    return false;
                }
            }
            table.reload('department_result_lists', {
                url: resultAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            table.reload('template_nums_lists', {
                url: resultAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //监听质控异常统计搜索
        form.on('submit(repairAnalysisSearchB)', function(data){
            gloabOptionsB = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            if(gloabOptionsB.startDateB && gloabOptionsB.endDateB){
                if(gloabOptionsB.startDateB > gloabOptionsB.endDateB){
                    layer.msg("请输入合理的日期",{icon : 2,time:1000});
                    return false;
                }
            }
            var qtemid = $('select[name="templates"]').val();
            get_abnormal_assets(qtemid);
            get_abnormal_detail(qtemid);
            return false;
        });

        form.render();
        form.on('select(change_templates)',function (data) {
            get_abnormal_assets(data.value);
            get_abnormal_detail(data.value);
        });
        element.on('tab(qualityDepartTab)', function(data){
            if(data.index == 1){
                get_abnormal_assets(qtemid);
                get_abnormal_detail(qtemid);
            }
        });
        //异常设备台次统计
        function get_abnormal_assets(qtemid) {
            table.render({
                elem: '#abnormal_assets_lists'
                //,height: '600'
                ,limits:[10,20,50,100]
                ,loading:true
                ,limit: 10
                ,url: resultAnalysis //数据接口
                ,where: {
                    action:'getLists'
                    ,type:'abnormal_assets'
                    ,qtemid:qtemid
                    ,startDate:$('input[name="startDateB"]').val()
                    ,endDate:$('input[name="endDateB"]').val()
                    ,departids:$('input[name="departidsB"]').val()
                    ,brands:$('input[name="brandsB"]').val()
                    ,suppname:$('input[name="suppnameB"]').val()
                    ,assets:$('input[name="assetsB"]').val()
                    ,category:$('input[name="categoryB"]').val()
                } //如果无需传递额外参数，可不加该参数
                ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
                ,request: {
                    pageName: 'page' //页码的参数名称，默认：page
                    ,limitName: 'limit' //每页数据量的参数名，默认：limit
                } //如果无需自定义请求参数，可不加该参数
                , response: { //定义后端 json 格式，详细参见官方文档
                    statusName: 'code', //状态字段名称
                    statusCode: '200', //状态字段成功值
                    msgName: 'msg', //消息字段
                    countName: 'total', //总数字段
                    dataName: 'rows' //数据字段
                }
    
                ,defaultToolbar: ['exports']
                ,page: false //开启分页
                ,cols: [[ //表头
                    {
                        field:'qsid',
                        title:'序号',
                        width:60,
                        fixed: 'left',
                        align:'center',
                        unresize: true,
                        templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    },
                    {
                        field: 'department',
                        fixed: 'left',
                        title: '科室名称',
                        width: 160,
                        align:'center'
                    },
                    {
                        field: 'assets',
                        align:'center',
                        width: 160,
                        title: '设备名称'
                    },
                    {
                        field: 'assnum',
                        align:'center',
                        width: 160,
                        title: '设备编码'
                    },
                    {
                        field: 'category',
                        align:'center',
                        width: 160,
                        title: '设备分类'
                    },
                    {
                        field: 'model',
                        align:'center',
                        width: 140,
                        title: '规格/型号'
                    },
                    {
                        field: 'brand',
                        align:'center',
                        width: 140,
                        title: '品牌'
                    },
                    {
                        field: 'supplier',
                        align:'center',
                        width: 180,
                        title: '供应商'
                    },
                    {
                        field: 'plan_name',
                        align:'center',
                        width: 200,
                        title: '质控计划名称'
                    },
                    {
                        field: 'is_cycle',
                        align:'center',
                        width: 100,
                        title: '周期执行'
                    },
                    {
                        field: 'cycle',
                        align:'center',
                        width: 100,
                        title: '周期(月)'
                    },
                    {
                        field: 'period',
                        align:'center',
                        width: 100,
                        title: '期次'
                    },
                    {
                        field: 'start_date',
                        align:'center',
                        width: 110,
                        title: '启动日期'
                    },
                    {
                        field: 'add_date',
                        align:'center',
                        width: 110,
                        title: '完成日期'
                    },
                    {
                        field: 'result',
                        align:'center',
                        width: 100,
                        fixed: 'right',
                        title: '检测结果',
                        templet: function (d) {
                            return d.result == '不合格' ? '<span style="color: red;">不合格</span>' : '<span style="color: green;">合格</span>';
                        }
                    }
                ]]
                , done: function (res, curr, count) {
                    var layuiTable = $(".layui-table");
                    layuiTable.rowspan(1);
                    var abnormal_assets = echarts.init(document.getElementById('abnormal_assets'));
                    abnormal_assets.clear();
                    abnormal_assets.hideLoading();
                    if(!$.isEmptyObject(res.charData)){
                        abnormal_assets.setOption({
                            tooltip: {
                                trigger: 'item',
                                formatter: "{a} <br/>{b}: {c} ({d}%)"
                            },
                            legend: {
                                type: 'scroll',
                                orient: 'vertical',
                                top:'2%',
                                x: 'right',
                                data:res['charData']['legend_data']
                            },
                            series: [
                                {
                                    name:'异常设备台次',
                                    type:'pie',
                                    radius: ['60%', '80%'],
                                    center:['35%', '50%'],
                                    avoidLabelOverlap: false,
                                    label: {
                                        normal: {
                                            show: true,
                                            position: 'center',
                                            fontSize:'16',
                                            formatter : function(param){
                                                if(param.data.is_show){
                                                    return param.name+'  '+param.value+' \n占比 '+param.data.precent;
                                                }else{
                                                    return '';
                                                }
                                            }
                                        },
                                        emphasis: {
                                            show: true,
                                            textStyle: {
                                                fontSize: '20',
                                                fontWeight: 'bold'
                                            }
                                        }
                                    },
                                    labelLine: {
                                        normal: {
                                            show: false
                                        }
                                    },
                                    data:res['charData']['series_data']
                                }
                            ]
                        })
                    }else{
                        no_data_chart(abnormal_assets);
                    }
                }
            });
        }
        //异常项明细统计
        function get_abnormal_detail(qtemid) {
            var params = {};
            params.qtemid = qtemid;
            params.action   = 'get_abnormal_detail';
            params.startDate = $('input[name="startDateB"]').val();
            params.endDate   = $('input[name="endDateB"]').val();
            params.departids = $('input[name="departidsB"]').val();
            params.brands = $('input[name="brandsB"]').val();
            params.suppname = $('input[name="suppnameB"]').val();
            params.assets = $('input[name="assetsB"]').val();
            params.category = $('input[name="categoryB"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: resultAnalysis,
                data: params,
                dataType: "json",
                async: true,
                beforeSend:beforeSend,
                success: function (res) {
                    if (res.status == 1) {
                        //显示总结果图表数据template_result_chart
                        var template_result_chart = echarts.init(document.getElementById('template_result_chart'));
                        template_result_chart.clear();
                        template_result_chart.hideLoading();
                        if(!$.isEmptyObject(res.resultChartData)){
                            template_result_chart.setOption({
                                tooltip: {
                                    trigger: 'item',
                                    formatter: "{a} <br/>{b}: {c} ({d}%)"
                                },
                                legend: {
                                    type: 'scroll',
                                    orient: 'vertical',
                                    top:'2%',
                                    x: 'right',
                                    data:res['resultChartData']['legend_data']
                                },
                                series: [
                                    {
                                        name:'检测结果',
                                        type:'pie',
                                        radius: ['60%', '80%'],
                                        center:['45%', '50%'],
                                        avoidLabelOverlap: false,
                                        label: {
                                            normal: {
                                                show: true,
                                                position: 'center',
                                                fontSize:'16',
                                                formatter : function(param){
                                                    if(param.data.is_show){
                                                        return param.name+'  '+param.value+' \n占比 '+param.data.precent;
                                                    }else{
                                                        return '';
                                                    }
                                                }
                                            },
                                            emphasis: {
                                                show: true,
                                                textStyle: {
                                                    fontSize: '20',
                                                    fontWeight: 'bold'
                                                }
                                            }
                                        },
                                        labelLine: {
                                            normal: {
                                                show: false
                                            }
                                        },
                                        data:res['resultChartData']['series_data']
                                    }
                                ]
                            });
                        }else{
                            no_data_chart(template_result_chart);
                        }
                        $('.show_detail_list').hide();
                        if(!$.isEmptyObject(res.detailData)){
                            $('.no_detail_data').hide();
                            $('.show_detail_'+res.qtemid).show();
                            //显示表格数据
                            $.each(res.detailData.table_data,function (index,item) {
                                if(index == 'other'){
                                    var html = '';
                                    var i = 1;
                                    $.each(item,function (key,val) {
                                        if(res.qtemid == 1 || res.qtemid == 3){
                                            html += '<tr>\n';
                                            html += '<td>'+key+'</td>\n' +
                                                '<td>'+val.pass+'</td>\n' +
                                                '<td>'+val.not_pass+'</td>\n' +
                                                '<td>'+val.not_pass_rate+'</td>\n';
                                            html += '</tr>\n';
                                            i++;
                                        }
                                        if(res.qtemid == 2 || res.qtemid == 4){
                                            html += '<tr>\n';
                                            html += '<td>'+key+'</td>\n' +
                                                '<td>'+val.pass+'</td>\n' +
                                                '<td>'+val.not_pass+'</td>\n' +
                                                '<td>'+val.not_use+'</td>\n' +
                                                '<td>'+val.not_pass_rate+'</td>\n';
                                            html += '</tr>\n';
                                            i++;
                                        }
                                    });
                                    $('.'+index+'_'+res.qtemid).html('');
                                    $('.'+index+'_'+res.qtemid).html(html);
                                }else{
                                    var html = '';
                                    var i = 1;
                                    $.each(item,function (key,val) {
                                        html += '<tr>\n';
                                        html += '<td>'+i+'</td>\n' +
                                            '<td>'+key+'</td>\n' +
                                            '<td>'+val.pass+'</td>\n' +
                                            '<td>'+val.not_pass+'</td>\n' +
                                            // '<td>'+val.not_use+'</td>\n' +
                                            '<td>'+val.not_pass_rate+'</td>\n';
                                        html += '</tr>\n';
                                        i++;
                                    });
                                    $('.'+index+'_'+res.qtemid).html('');
                                    $('.'+index+'_'+res.qtemid).html(html);
                                }
                            });
                            //显示表格对应的图表
                            $.each(res.detailData.chart_data,function (index,item) {
                                if(!$.isEmptyObject(item)){
                                    index += '_'+res.qtemid;
                                    var tw = $('#LAY-Statistics-QualityStatis-resultAnalysis').width();
                                    tw = (tw-50)*0.55;
                                    $('#'+'sta_abnormal_'+index).css('width',tw+'px');
                                    var abnormal_chart = echarts.init(document.getElementById('sta_abnormal_'+index));
                                    abnormal_chart.clear();
                                    abnormal_chart.hideLoading();
                                    abnormal_chart.setOption({
                                        tooltip : {
                                            trigger: 'item',
                                            //formatter: "{a} <br/>{b} : {c} ({d}%)"
                                            formatter:function (params) {
                                                return "异常项数量 <br/>"+params.data.name+" : "+params.data.value+" ("+params.data.precent+")"
                                            }
                                        },
                                        series : [
                                            {
                                                name: '异常项数量',
                                                type: 'pie',
                                                radius : '70%',
                                                center: ['50%', '55%'],
                                                label: {
                                                    normal: {
                                                        //formatter: '{a|{a}}{abg|}\n{hr|}\n  {b|{b}:} {c}  {per|{d}%}  ',
                                                        formatter:function (params) {
                                                            return '{a|异常项数量}{abg|}\n{hr|}\n  {b|'+params.data.name+': '+params.data.value+' ('+params.data.precent+')} ';
                                                        },
                                                        backgroundColor: '#eee',
                                                        borderColor: '#aaa',
                                                        borderWidth: 1,
                                                        borderRadius:4,

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
                                                data:item,
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
                                }else{
                                    index += '_'+res.qtemid;
                                    var tw = $('#LAY-Statistics-QualityStatis-resultAnalysis').width();
                                    tw = (tw-50)*0.55;
                                    $('#'+'sta_abnormal_'+index).css('width',tw+'px');
                                    var abnormal_chart = echarts.init(document.getElementById('sta_abnormal_'+index));
                                    abnormal_chart.clear();
                                    abnormal_chart.hideLoading();
                                    abnormal_chart.setOption({
                                        color:"#E6E6E6",
                                        title : {
                                            subtext: '暂无异常项数据',
                                            x:'center'
                                        },
                                        series : [
                                            {
                                                name: '异常项数量',
                                                type: 'pie',
                                                radius : '70%',
                                                center: ['50%', '55%'],
                                                data:[{value:0, name:''}]
                                            }
                                        ]
                                    });
                                }
                            });
                        }else{
                            $('.no_detail_data').show();
                        }
                    }else{
                        layer.msg(res.msg,{icon : 2},1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
        }

        function no_data_chart(idname) {
            idname.setOption({
                color:"#E6E6E6",
                title : {
                    subtext: '暂无相关数据',
                    x:'center'
                },
                series: [
                    {
                        type:'pie',
                        radius: ['65%', '80%'],
                        center:['50%', '55%'],
                        avoidLabelOverlap: false,
                        label: {
                            normal: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                show: true,
                                textStyle: {
                                    fontSize: '30',
                                    fontWeight: 'bold'
                                }
                            }
                        },
                        labelLine: {
                            normal: {
                                show: false
                            }
                        },
                        data:[
                            {value:0}

                        ]
                    }
                ]
            });
        }

        //设备名称搜索建议
        $("#getAssetsStatistics").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#getCateStatistics").bsSuggest(
            returnCategory('')
        );
        //供应商名称搜索建议
        $("#getSuppliersStatistics").bsSuggest(
            getOfflineSuppliersName('')
        );

        //设备名称搜索建议
        $("#getAssetsStatistics_error").bsSuggest(
            returnAssets()
        );

        //分类搜索建议
        $("#getCateStatistics_error").bsSuggest(
            returnCategory('')
        );

        //供应商名称搜索建议
        $("#getSuppliersStatistics_error").bsSuggest(
            getOfflineSuppliersName('')
        );
    });
    exports('statistics/statisQuality/resultAnalysis', {});
});