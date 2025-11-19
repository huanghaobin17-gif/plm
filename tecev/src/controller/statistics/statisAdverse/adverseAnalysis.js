layui.define(function(exports){
    layui.use(['carousel', 'echarts', 'form', 'table', 'laydate', 'tablePlug'], function () {
        var $ = layui.$
            , form = layui.form
            , laydate = layui.laydate
            , carousel = layui.carousel
            , echarts = layui.echarts
            , table = layui.table, tablePlug = layui.tablePlug;

        laydate.render({
            elem: '#adverseS'
        });
        laydate.render({
            elem: '#adverseE'
        });
        form.render();
        var params = {};
        params.action = 'getChartData';
        params.pic_type = 'pie';
        params.startDate = $('input[name="startDate"]').val();
        params.endDate = $('input[name="endDate"]').val();
        params.pic_type = $('select[name="adverse_pic_type"]').val();
        table.render({
            elem: '#analysisLists'
            //,height: '600'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '不良事件报表分析列表'
            ,url: adverseAnalysis //数据接口
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
                    rowspan:"6",
                    align:'center',
                    unresize: true,
                    totalRowText: '合计',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'type', fixed: 'left', title: '设备所属', rowspan: "6", width: 90, align: 'center'},
                {
                    field: 'report_num',
                    fixed: 'left',
                    title: '报告数量',
                    totalRow: true,
                    totalNums:0,
                    rowspan: "6",
                    width: 90,
                    align: 'center'
                },
                {title: '报告来源', totalRow: true,totalNums:0, colspan: "4", align: 'center'}
                , {title: '患者性别', colspan: "3", align: 'center'},
                {title: '患者年龄', colspan: "5", align: 'center'},
                {title: '医疗器械使用场所', colspan: "4", align: 'center'},
                {title: '事件后果', colspan: "4", align: 'center'}
            ],
                [
                    {field: 'report_from_factory',align:'center', title: '生产企业',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'report_from_bussniess',align:'center', title: '经营企业',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'report_from_user',align:'center', title: '使用单位',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'report_from_none',align:'center', title: '未填写',totalRow: true,totalNums:0, width: 90,rowspan:5}
                    ,{field: 'male', title: '男',align:'center',totalRow: true,totalNums:0, width: 60,rowspan:4}
                    ,{field: 'female', title: '女',align:'center',totalRow: true,totalNums:0, width: 60,rowspan:4}
                    ,{field: 'none_sex', title: '未填写',align:'center',totalRow: true,totalNums:0, width: 80,rowspan:4}
                    ,{field: 'age1',align:'center',totalRow: true,totalNums:0, title: '0~20', width: 90,rowspan:4}
                    ,{field: 'age2',align:'center',totalRow: true,totalNums:0, title: '20~40', width: 90,rowspan:4}
                    ,{field: 'age3',align:'center',totalRow: true,totalNums:0, title: '40~60', width: 90,rowspan:4}
                    ,{field: 'age4',align:'center',totalRow: true,totalNums:0, title: '60以上', width: 90,rowspan:4}
                    ,{field: 'age5',align:'center',totalRow: true,totalNums:0, title: '未填写', width: 90,rowspan:4}
                    ,{field: 'place1',align:'center',totalRow: true,totalNums:0,title: '医疗机构', width: 90,rowspan:5}
                    ,{field: 'place2',align:'center',totalRow: true,totalNums:0, title: '家庭', width: 90,rowspan:5}
                    ,{field: 'place3',align:'center',totalRow: true,totalNums:0, title: '其他', width: 90,rowspan:5}
                    ,{field: 'place4',align:'center',totalRow: true,totalNums:0, title: '未填写', width: 90,rowspan:5}
                    ,{field: 'die',align:'center',totalRow: true,totalNums:0, title: '死亡', width: 90,rowspan:4}
                    ,{field: 'jeolife',align:'center',totalRow: true,totalNums:0, title: '危及生命', width: 90,rowspan:4}
                    ,{field: 'res3',align:'center',totalRow: true,totalNums:0, title: '机体功能结构永久性损伤', width: 190,rowspan:4}
                    ,{field: 'resother',align:'center',totalRow: true,totalNums:0, title: '其他后果', width: 90,rowspan:4}
                ]
            ]
        });
        //监听搜索
        form.on('submit(staticAdverseSearch)', function(data){
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
            table.reload('analysisLists', {
                url: adverseAnalysis
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            params.action = 'getChartData';
            params.pic_type = $('select[name="adverse_pic_type"]').val();
            params.startDate = gloabOptions.startDate;
            params.endDate = gloabOptions.endDate;
            get_chart_data(params);
            return false;
        });
        form.on('select(change_pic)',function (data) {
            params.action = 'getChartData';
            params.pic_type = data.value;
            params.startDate = $('input[name="startDate"]').val();
            params.endDate = $('input[name="endDate"]').val();
            get_chart_data(params);
        });
        var tdiv = $('#statics-contant').find('div');
        var ids = [];
        $.each(tdiv,function (index,item) {
            var tmpid = $(this).find('.static-chart-width').attr('id');
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
                url: admin_name+'/StatisAdverse/adverseAnalysis',
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
    exports('statistics/statisAdverse/adverseAnalysis', {});
});
