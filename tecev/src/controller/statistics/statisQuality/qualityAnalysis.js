layui.define(function(exports){
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , table = layui.table, tablePlug = layui.tablePlug;

        laydate.render({
            elem: '#qualityAnalysisS'
        });
        laydate.render({
            elem: '#qualityAnalysisE'
        });
        form.render();
        var params = {};
        params.action = 'getChartData';
        params.pic_type = 'pie';
        params.startDate = $('input[name="startDate"]').val();
        params.endDate = $('input[name="endDate"]').val();
        params.pic_type = $('select[name="pic_type"]').val();
        table.render({
            elem: '#qualityAnalysisLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '质控计划报表分析列表'
            ,url: qualityAnalysis //数据接口
            ,where: {
                action:'getLists'
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
            ,page: false
            ,totalRow: true

            ,defaultToolbar: ['exports']
            //,page: true //开启分页
            ,cols: [[ //表头
                {
                    field:'id',
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
                {field: 'type', fixed: 'left', title: '是否结束', width: 90, align: 'center'},
                {field: 'plans_num', fixed: 'left', title: '计划数量', totalRow: true,totalNums:0, width: 90, align: 'center'},
                {field: 'assets_num', totalRow: true,totalNums:0, align: 'center', title: '设备数量'}
                , {field: 'pass_num', totalRow: true,totalNums:0, title: '合格数量', align: 'center'},
                {field: 'not_pass_num', totalRow: true,totalNums:0, title: '不合格数量', align: 'center'}
            ]]
        });
        //监听搜索
        form.on('submit(staticQualitySearch)', function(data){
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
            table.reload('qualityAnalysisLists', {
                url: qualityAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            params.action = 'getChartData';
            params.pic_type = $('select[name="pic_type"]').val();
            params.startDate = gloabOptions.startDate;
            params.endDate = gloabOptions.endDate;
            get_chart_data(params);
            return false;
        });
        form.on('select(change_pic_quality)',function (data) {
            params.action = 'getChartData';
            params.pic_type = data.value;
            params.startDate = $('input[name="startDate"]').val();
            params.endDate = $('input[name="endDate"]').val();
            get_chart_data(params);
        });
        var tdiv = $('#statics-contant-quality').find('div');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.static-chart-width-quality').attr('id');
            if(tmpid){
                ids.push(tmpid);
            }
        });
        get_chart_data(params);
        function get_chart_data(params) {
            $.each(ids,function (index,item) {
                var idname = echarts.init(document.getElementById(item));
                idname.clear();
                idname.showLoading();
            });
            var result = {};
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name+'/StatisQuality/qualityAnalysis',
                data: params,
                dataType: "json",
                async: false,
                success: function (data) {
                    if (data.status == 1) {
                        result = data;
                    }
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                }
            });
            if(!$.isEmptyObject(result)){
                $.each(ids,function (index,item) {
                    $('.hidden-contant').hide();
                    var idname = echarts.init(document.getElementById(item));
                    idname.hideLoading();
                    if(params.pic_type == 'pie'){
                        idname.setOption({
                            title : {
                                text: result['data']['title'][item],
                                x:'center'
                            },
                            tooltip : {
                                trigger: 'item',
                                formatter: "{a} <br/>{b} : {c} ({d}%)"
                            },
                            series : [
                                {
                                    name: '占比',
                                    type: 'pie',
                                    radius : '60%',
                                    center: ['50%', '55%'],
                                    data:result['data'][item]
                                }
                            ]
                        });
                    }else{
                        idname.setOption({
                            tooltip : {
                                trigger: 'axis',
                                // axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                                //     type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                                // }
                            },
                            title : {
                                text: result['data']['title'][item],
                                x:'center'
                            },
                            color: result['data']['color'][item],
                            xAxis : [
                                {
                                    type : 'category',
                                    barMinHeight:30,
                                    data : result['data'][item]['xAxis_data'],
                                    axisTick: {
                                        alignWithLabel: true
                                    },
                                    axisLabel: {
                                        interval:0,//坐标轴刻度标签的显示间隔(在类目轴中有效哦)，默认会采用标签不重叠的方式显示标签（也就是默认会将部分文字显示不全）可以设置为0强制显示所有标签，如果设置为1，表示隔一个标签显示一个标签，如果为3，表示隔3个标签显示一个标签，以此类推
                                        rotate:20//标签倾斜的角度
                                    }
                                }
                            ],
                            yAxis : [
                                {
                                    type : 'value'
                                }
                            ],
                            series : [
                                {
                                    name:'直接访问',
                                    type:result['data'][item]['type'],
                                    barWidth: '40%',
                                    barMinHeight:'30%',
                                    data:result['data'][item]['series_data']
                                }
                            ]
                        });
                    }
                });
            }else{
                $.each(ids,function (index,item) {
                    var idname = echarts.init(document.getElementById(item));
                    idname.clear();
                    idname.hideLoading();
                    $('.hidden-contant').show();
                });
            }
        }
    });
    exports('statistics/statisQuality/qualityAnalysis', {});
});